<?php

if (!defined('ABSPATH')) exit;

// Define constants
if (!defined('WORKABLE_CLIENT_KEY')) {
    define('WORKABLE_CLIENT_KEY', 'workable_api_key');
}
if (!defined('WORKABLE_SUBDOMAIN')) {
    define('WORKABLE_SUBDOMAIN', 'epos');
}
foreach (glob(THEME_DIR . '-child' . "/includes/workable/*/*.php") as $file_name) {
    require_once($file_name);
}

require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/admin/settings.php';
require_once __DIR__ . '/includes/helpers.php';

require_once __DIR__ . '/vendor/autoload.php';
