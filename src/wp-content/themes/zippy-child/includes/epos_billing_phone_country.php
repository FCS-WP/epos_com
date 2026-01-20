<?php
add_action('wp_enqueue_scripts', function () {
    if (!is_checkout()) return;

    $base = get_stylesheet_directory_uri() . '/assets/lib/intl-tel-input';

    wp_enqueue_style(
        'intl-tel-input',
        $base . '/css/intlTelInput.min.css',
        [],
        '18.5.2'
    );

    wp_enqueue_script(
        'intl-tel-input',
        $base . '/js/intlTelInput.min.js',
        [],
        '18.5.2',
        true
    );

    wp_enqueue_script(
        'intl-tel-input-utils',
        $base . '/js/utils.js',
        ['intl-tel-input'],
        '18.5.2',
        true
    );

    wp_enqueue_script(
        'checkout-phone',
        get_stylesheet_directory_uri() . '/assets/js/checkout-phone.js',
        ['intl-tel-input'],
        '1.0',
        true
    );
});

