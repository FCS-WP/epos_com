<?php

if (! defined('ABSPATH')) exit; // Exit if accessed directly


/* Load required classes */
foreach (glob(THEME_DIR . '-child' . "/includes/gtm_tracking/includes/*.php") as $file_name) {
  require_once($file_name);
}

/* Initialize the tracker */
new GMT_Events();
