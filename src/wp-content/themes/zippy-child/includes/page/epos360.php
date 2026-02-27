<?php
add_filter('body_class', function($classes) {
    if (is_page('epos360') && strpos($_SERVER['REQUEST_URI'], '/my/') !== false) {
        $classes[] = 'epos360';
    }
    return $classes;
});