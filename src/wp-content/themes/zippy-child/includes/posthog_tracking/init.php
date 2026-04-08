<?php

if (! defined('ABSPATH')) exit; // Exit if accessed directly

/* Load required classes */
foreach (glob(THEME_DIR . '-child' . '/includes/posthog_tracking/class-posthog-*.php') as $file_name) {
  require_once($file_name);
}

/* Initialize the tracker */
new PostHog_Init();
new PostHog_Events();
