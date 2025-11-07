<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('get_workable_api_key')) {
    function get_workable_api_key() {
        return get_option(WORKABLE_CLIENT_KEY, '');
    }
}
