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


add_action('woocommerce_checkout_create_order', function($order) {
    $map = [
        'utm_source'   => 'source',
        'utm_medium'   => 'medium',
        'utm_campaign' => 'campaign',
        'utm_term'     => 'term',
        'utm_content'  => 'content',
    ];

    foreach ($map as $utm => $field) {
        $value = WC()->session->get($utm);
        if ($value) {
            $order->update_meta_data($field, $value);
            $order->update_meta_data('_' . $utm, $value);
        }
    }

    $order->update_meta_data('source_type', 'utm');
}, 99);


// Checkout form - Company name input is required
add_filter('woocommerce_checkout_fields', function($fields) {
    $fields['billing']['billing_company']['required'] = true;
    $fields['billing']['billing_company']['label'] = 'Company name';
    return $fields;
});
