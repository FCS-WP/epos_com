<?php
/*
 * Define Variables
 */
if (!defined('THEME_DIR'))
    define('THEME_DIR', get_template_directory());
if (!defined('THEME_URL'))
    define('THEME_URL', get_template_directory_uri());

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

/*
 * Include framework files
 */
foreach (glob(THEME_DIR . '-child' . "/includes/*.php") as $file_name) {
    require_once($file_name);
}


// Include workable files
foreach (glob(THEME_DIR . '-child' . "/includes/workable/*.php") as $file_name) {
    require_once($file_name);
}

// Include ant bot files
require_once(THEME_DIR . '-child' . "/includes/ant_bot/init.php");

require_once(THEME_DIR . '-child' . "/includes/fb_tracking/init.php");

// Include HubSpot Integration files
foreach (glob(THEME_DIR . '-child' . "/includes/hubspot_intergration/*.php") as $file_name) {
    require_once($file_name);
}
