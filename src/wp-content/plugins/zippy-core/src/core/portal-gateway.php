<?php

namespace Zippy_Core\Src\Core;

use WP_Error;

/**
 * Class Portal_Gatewaye
 * 
 */
class Portal_Gateway
{


  const API_NAMESPACE = 'portal/v1';

  public function __construct()
  {
    add_action('rest_api_init', [$this, 'register_routes']);
  }

  /**
   * Note: Define the endpoints and their requirements.
   */
  public function register_routes()
  {
    register_rest_route(self::get_namespace(), '/auth/sso', [
      'methods'  => 'GET',
      'callback' => [$this, 'handle_sso'],
      'permission_callback' => [$this, 'validate_signature'],
    ]);

    // Note: Endpoint to fetch site data (Plugins, Themes, Versions)
    register_rest_route(self::get_namespace(), '/site-info', [
      'methods'  => 'GET',
      'callback' => [$this, 'get_site_info'],
      'permission_callback' => [$this, 'validate_signature'],
    ]);

    register_rest_route(self::get_namespace(), '/health-check', [
      'methods'  => 'GET',
      'callback' => [$this, 'health_check'],
      'permission_callback' => [$this, 'validate_signature'],
    ]);

    register_rest_route(self::get_namespace(), '/update-plugin', [
      'methods'  => 'POST',
      'callback' => [$this, 'update_plugin'],
      'permission_callback' => [$this, 'validate_signature'],
    ]);
  }

  /**
   * Note: Security Layer - Validates the HMAC signature for every request.
   */

  public function validate_signature($request)
  {
    $app_key   = $request->get_param('portal_key');
    $timestamp = (int) $request->get_param('portal_ts');
    $signature = $request->get_param('portal_sig');

    if (!$app_key || !$timestamp || !$signature) {
      return new WP_Error('missing_auth', 'Missing authentication parameters', ['status' => 401]);
    }

    if (trim($app_key) !== trim(get_option('wp_portal_app_key'))) {
      return new WP_Error('invalid_key', 'Invalid App Key', ['status' => 401]);
    }

    if (abs(current_time('timestamp', true) - $timestamp) > 300) {
      return new WP_Error('expired', 'Request expired', ['status' => 403]);
    }

    $body = $request->get_body();

    if (empty($body) && $request->get_param('token')) {
      $body = json_encode(['token' => $request->get_param('token')]);
    } elseif (empty($body)) {
      $body = json_encode([]);
    }

    $payload = $timestamp . '.' . $body;
    $expected_signature = hash_hmac('sha256', $payload, trim(get_option('wp_portal_app_secret')));

    if (!hash_equals($expected_signature, (string)$signature)) {
      return new WP_Error('invalid_sig', 'Signature mismatch', ['status' => 403]);
    }

    return true;
  }

  /**
   * Note: Logic for SSO Login
   */
  public function handle_sso($request)
  {
    $token   = $request->get_param('token');
    $app_key = trim(get_option('wp_portal_app_key'));
    $user_ip = $_SERVER['REMOTE_ADDR'];

    $portal_url = defined('PORTAL_INTERNAL_URL') ? PORTAL_INTERNAL_URL : 'http://localhost:8000';

    $response = wp_remote_post($portal_url . '/api/v1/verify-sso-token', [
      'headers' => [
        'Accept' => 'application/json',
      ],
      'body' => [
        'token'   => $token,
        'app_key' => $app_key,
        'user_ip' => $user_ip,
      ],
      'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
      wp_die('Portal Connection Error: ' . $response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response));

    // var_dump($body);
    if (isset($body->valid) && $body->valid === true) {
      $users = get_users(['role' => 'administrator', 'number' => 1]);
      $admin = !empty($users) ? $users[0] : null;

      if ($admin) {
        wp_logout();

        wp_set_current_user($admin->ID);
        wp_set_auth_cookie($admin->ID, true); // Note: Set 'Remember Me' to true

        wp_safe_redirect(admin_url());
        exit;
      }
    }

    wp_die('SSO Authentication Failed: Invalid or Expired Token.', 'Unauthorized', ['response' => 403]);
  }

  /**
   * Note: Collects all relevant site data for the Portal dashboard.
   */
  public function get_site_info($request)
  {
    wp_update_plugins();

    $all_plugins = get_plugins();
    $updates = get_site_transient('update_plugins');
    $plugin_needing_update = [];
    $plugin_data = [];
    foreach ($all_plugins as $path => $info) {
      if (isset($updates->response[$path])) {
        $plugin_needing_update[] = $path;
      }
      $plugin_data[] = [
        'path'    => $path,
        'name'    => $info['Name'],
        'version' => $info['Version'],
        'active'  => is_plugin_active($path),
        'update'  => isset($updates->response[$path]),
        'new_version' => isset($updates->response[$path]) ? $updates->response[$path]->new_version : null,
      ];
    }

    $data = [
      'wp_version'  => get_bloginfo('version'),
      'php_version' => phpversion(),
      'site_name'   => get_bloginfo('name'),
      'plugins'     => [
        'total'   => count($all_plugins),
        'updates' => count($plugin_needing_update ?? []),
      ],
      'plugins_list' => $plugin_data,
      'themes'      => [
        'current' => wp_get_theme()->get('Name'),
        'updates' => count(get_site_transient('update_themes')->updates ?? []),
      ],
      'disk_usage'  => $this->get_disk_info(),
    ];

    return rest_ensure_response([
      'success' => true,
      'data'    => $data
    ]);
  }

  /**
   * Note: Health Check API Responses
   */

  public function health_check()
  {
    return array(
      'status'    => 'ok',
      'timestamp' => current_time('mysql'),
    );
  }

  public function update_plugin($request)
  {


    //Get & validate plugin path
    $plugin_path = sanitize_text_field($request->get_param('plugin_path'));

    if (empty($plugin_path)) {
      return new WP_Error(
        'missing_param',
        'plugin_path is required.',
        array('status' => 400)
      );
    }

    if (! file_exists(WP_PLUGIN_DIR . '/' . $plugin_path)) {
      return new WP_Error(
        'invalid_plugin',
        'Plugin does not exist.',
        array('status' => 404)
      );
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    require_once ABSPATH . 'wp-admin/includes/plugin-install.php';


    //  Initialize filesystem
    WP_Filesystem();

    $was_active = is_plugin_active($plugin_path);

    $skin     = new \Automatic_Upgrader_Skin();
    $upgrader = new \Plugin_Upgrader($skin);
    $result   = $upgrader->upgrade($plugin_path);

    if (is_wp_error($result)) {
      return new WP_Error(
        'upgrade_failed',
        $result->get_error_message(),
        array('status' => 500)
      );
    }

    if ($result === false) {
      return new WP_Error(
        'upgrade_failed',
        'Plugin upgrade failed for unknown reasons.',
        array('status' => 500)
      );
    }

    if ($was_active) {
      $activate = activate_plugin($plugin_path);

      if (is_wp_error($activate)) {
        return new WP_Error(
          'activation_failed',
          $activate->get_error_message(),
          array('status' => 500)
        );
      }
    }

    return rest_ensure_response(array(
      'success' => true,
      'plugin'  => $plugin_path,
    ));
  }


  private function get_disk_info()
  {
    $disk_total = disk_total_space(ABSPATH);
    $disk_free  = disk_free_space(ABSPATH);
    $disk_used  = $disk_total - $disk_free;

    return [
      'total_bytes' => $disk_total,
      'used_bytes'  => $disk_used,
      'free_bytes'  => $disk_free,
      'used_percentage' => round(($disk_used / $disk_total) * 100, 2),
    ];
  }

  public static function get_namespace()
  {
    return defined('PORTAL_CUSTOM_NAMESPACE') ? PORTAL_CUSTOM_NAMESPACE : self::API_NAMESPACE;
  }
}
