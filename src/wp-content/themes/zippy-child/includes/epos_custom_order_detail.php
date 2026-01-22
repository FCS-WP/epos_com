<?php

// Add UTM for order detail when place order
add_action('woocommerce_init', function() {
    if (!WC()->session) return;

    $keys = [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content'
    ];

    foreach ($keys as $key) {
        if (!empty($_GET[$key])) {
            WC()->session->set($key, sanitize_text_field($_GET[$key]));
        }
    }
});


add_action('woocommerce_checkout_create_order', function($order, $data) {
    // Handle Full name field
    if (empty($data['billing_full_name'])) return;

    $name  = trim(preg_replace('/\s+/', ' ', $data['billing_full_name']));
    $parts = explode(' ', $name, 2);
    $first = $parts[0];
    $last  = $parts[1] ?? '';

    $order->set_billing_first_name($first);
    $order->set_billing_last_name($last);

}, 99, 2);


