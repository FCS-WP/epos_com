<?php

function my_register_extra_menu()
{
    register_nav_menus(array(
        'my_menu' => __('Main menu MY', 'Epos.com'),
    ));
}
add_action('after_setup_theme', 'my_register_extra_menu');

add_action('template_redirect', function() {
    if (untrailingslashit($_SERVER['REQUEST_URI']) === '/my') {
        wp_redirect(home_url('/my/home'), 301);
        exit;
    }
});
