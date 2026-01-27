<?php

if (! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Get Pixel ID from ACF
 */
function get_my_fb_pixel_id()
{
  return get_field('facebook_pixel_id', 'option');
}

/**
 * Get CAPI Token from ACF
 */
function get_my_fb_access_token()
{
  return get_field('facebook_access_token', 'option');
}

/* Load required classes */
foreach (glob(THEME_DIR . '-child' . "/includes/fb_tracking/includes/*.php") as $file_name) {
  require_once($file_name);
}

/* Initialize the tracker */
new My_FB_Init();
new My_FB_WC_Events();

// Initialize CAPI
new My_FB_CAPI();
