<?php

if (! defined('ABSPATH')) exit; // Exit if accessed directly

/* Load required classes */
foreach (glob(THEME_DIR . '-child' . '/includes/posthog_tracking/class-posthog-*.php') as $file_name) {
  require_once($file_name);
}

// Get posthog id from URL
add_action('wp_loaded', function () {
    if (isset($_GET['phid']) && WC()->session) {
        $phid = sanitize_text_field($_GET['phid']);
        WC()->session->set('posthog_distinct_id', $phid);
    }
});


/* Initialize the tracker */
new PostHog_Init();
new PostHog_Events();
