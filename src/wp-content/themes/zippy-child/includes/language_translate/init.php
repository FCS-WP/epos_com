<?php

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/includes/lang_switch.php';
require_once __DIR__ . '/includes/admin_display.php';


add_action('wp', function() {
    if (is_page() && get_field('multi_languages_page')) {
        $GLOBALS['is_multi_lang_page'] = true;
    }
});

//  Redirect for landing page
add_action('template_redirect', function () {
    if (is_cart() || is_checkout()) return;
    if (empty($GLOBALS['is_multi_lang_page'])) return;
    $lang = Lang::get();
    if (!$lang) return;

    $supported_Lang    = Lang::supported();
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $segments = explode('/', $path);
    $last = end($segments);

    if (in_array($last, $supported_Lang, true)) {
        array_pop($segments);
    }
    if ($lang !== 'en') {
        $segments[] = $lang;
    }
    $new_url = home_url('/' . implode('/', $segments) . '/');
    if (trailingslashit($new_url) === home_url(trailingslashit($path))) {
        return;
    }
    wp_safe_redirect($new_url);
    exit;
});