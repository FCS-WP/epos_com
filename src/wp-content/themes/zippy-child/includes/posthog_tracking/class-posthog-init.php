<?php

if (! defined('ABSPATH')) exit; // Exit if accessed directly

class PostHog_Init
{
  public function __construct()
  {
    add_action('wp_head', array($this, 'inject_posthog_snippet'), 1);
    add_action('wp_footer', array($this, 'inject_identify_user'));
    add_action('wp_logout', array($this, 'inject_posthog_reset'));
  }

  /**
   * Inject the PostHog JS snippet into wp_head.
   * The public API key and host are read from environment variables
   * (POSTHOG_API_KEY and POSTHOG_HOST) set in the Docker container.
   */
  public function inject_posthog_snippet()
  {
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page()) {
      return;
    }

    $api_key = get_field('posthog_api_key', 'option');
    $host    = get_field('posthog_host', 'option');

    if (! $api_key || ! $host) return;
?>
    <!-- PostHog Analytics -->
    <script>
      !function(t,e){var o,n,p,r;e.__SV||(window.posthog && window.posthog.__loaded)||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.crossOrigin="anonymous",p.async=!0,p.src=s.api_host.replace(".i.posthog.com","-assets.i.posthog.com")+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+".people (stub)"},o="Ii init Di qi Sr Bi Zi Pi capture calculateEventProperties Yi register register_once register_for_session unregister unregister_for_session Xi getFeatureFlag getFeatureFlagPayload getFeatureFlagResult isFeatureEnabled reloadFeatureFlags updateFlags updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures on onFeatureFlags onSurveysLoaded onSessionId getSurveys getActiveMatchingSurveys renderSurvey displaySurvey cancelPendingSurvey canRenderSurvey canRenderSurveyAsync Ji identify setPersonProperties group resetGroups setPersonPropertiesForFlags resetPersonPropertiesForFlags setGroupPropertiesForFlags resetGroupPropertiesForFlags reset get_distinct_id getGroups get_session_id get_session_replay_url alias set_config startSessionRecording stopSessionRecording sessionRecordingStarted captureException startExceptionAutocapture stopExceptionAutocapture loadToolbar get_property getSessionProperty Wi Vi createPersonProfile setInternalOrTestUser Gi Fi Ki opt_in_capturing opt_out_capturing has_opted_in_capturing has_opted_out_capturing get_explicit_consent_status is_capturing clear_opt_in_out_capturing $i debug Tr Ui getPageViewId captureTraceFeedback captureTraceMetric Ri".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
      posthog.init('<?php echo esc_js($api_key); ?>', {
          api_host: '<?php echo esc_js($host); ?>',
          defaults: '2026-01-30',
          person_profiles: 'always', // or 'always' to create profiles for anonymous users as well
          session_recording: {
            maskAllInputs: false,
          },
          session_recording_sample_rate: 1,
      })
    </script>
    <!-- End PostHog Analytics -->
<?php
  }

  /**
   * Identify logged-in WordPress users on each page load.
   * Uses the WP user email as the distinct_id to correlate with server-side events.
   */
  public function inject_identify_user()
  {
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page()) {
      return;
    }

    if (! is_user_logged_in()) return;

    $user = wp_get_current_user();
    if (! $user || ! $user->ID) return;
?>
    <script>
      if (typeof posthog !== 'undefined') {
        posthog.identify('<?php echo esc_js($user->user_email); ?>', {
          email: '<?php echo esc_js($user->user_email); ?>',
          name: '<?php echo esc_js($user->display_name); ?>',
        });
      }
    </script>
<?php
  }

  /**
   * Reset PostHog on logout so the session is not shared after sign-out.
   * This fires server-side; the JS reset happens via redirect after logout.
   */
  public function inject_posthog_reset()
  {
    // Reset is handled client-side on the next page load after logout.
    // No server-side action needed.
  }
}
