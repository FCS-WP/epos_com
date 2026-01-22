<?php
// Action
// Enqueue scripts for billing phone country codes
add_action('wp_enqueue_scripts', function () {
    if (!is_checkout()) return;

    $base = get_stylesheet_directory_uri() . '/assets/lib/intl-tel-input';
    $ver  = '18.5.2';

    wp_enqueue_style('intl-tel-input', "$base/css/intlTelInput.min.css", [], $ver);
    wp_enqueue_script('intl-tel-input', "$base/js/intlTelInput.min.js", [], $ver, true);
    wp_enqueue_script('intl-tel-input-utils', "$base/js/utils.js", ['intl-tel-input'], $ver, true);
    wp_enqueue_script('checkout-phone', get_stylesheet_directory_uri() . '/assets/js/checkout-phone.js', ['intl-tel-input'], '1.0', true);
});


// Add UTMs in to woo session for mapping when create order
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


// Custom order MCC/UEN field for checkout
add_action('woocommerce_after_checkout_billing_form', function ($checkout) {
    woocommerce_form_field('order_eg', [
        'type'        => 'text',
        'class'       => ['form-row-wide'],
        'label'       => __('MCC/UEN'),
        'placeholder' => __('MCC/UEN'),
        'required'    => false,
    ], $checkout->get_value('order_eg'));
});

// Show in order dashboard
add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
    $eg = $order->get_meta('order_eg');
    if ($eg) {
        echo '<p><strong>' . __('MCC / UEN', 'woocommerce') . ':</strong> ' . esc_html($eg) . '</p>';
    }
});

// Show in order detail
add_action('woocommerce_order_details_after_customer_details', function ($order) {
    $eg = $order->get_meta('order_eg');
    if ($eg) {
        echo '<p><strong>' . __('MCC / UEN', 'woocommerce') . ':</strong> ' . esc_html($eg) . '</p>';
    }
});

// Show in mail
add_filter('woocommerce_email_order_meta_fields', function ($fields, $sent_to_admin, $order) {
    $eg = $order->get_meta('order_eg');
    if ($eg) {
        $fields['order_eg'] = [
            'label' => __('MCC / UEN', 'woocommerce'),
            'value' => $eg,
        ];
    }
    return $fields;
}, 10, 3);



add_action('woocommerce_checkout_create_order', function($order, $data) {
    // Handle UTM mapping to order from woo session
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


    // Handle Full name field
    if (empty($data['billing_full_name'])) return;

    $name  = trim(preg_replace('/\s+/', ' ', $data['billing_full_name']));
    $parts = explode(' ', $name, 2);
    $first = $parts[0];
    $last  = $parts[1] ?? '';

    $order->set_billing_first_name($first);
    $order->set_billing_last_name($last);


    // Handle custom order eg field
    if (!empty($_POST['order_eg'])) {
        $order->update_meta_data(
            'order_eg',
            sanitize_text_field($_POST['order_eg'])
        );
    }
}, 99, 2);





// Filter
// Company name input is required
add_filter('woocommerce_checkout_fields', function($fields) {
    $fields['billing']['billing_company']['required'] = true;
    $fields['billing']['billing_company']['label'] = 'Company name';
    return $fields;
});
add_filter('woocommerce_checkout_required_field_notice', function ($message, $field_label) {
    if ($field_label === 'Billing Company name') {
        return '<strong>Company name</strong> is required.';
    }
    return $message;
}, 10, 2);

// Name field
add_filter('woocommerce_default_address_fields', function ($fields) {
    unset($fields['first_name'], $fields['last_name']);
    return $fields;
}, 20);

add_filter('woocommerce_checkout_fields', function ($fields) {
    $fields['billing']['billing_full_name'] = [
        'type'        => 'text',
        'label'       => 'Full name',
        'required'    => true,
        'priority'    => 10,
        'class'       => ['form-row-wide'],
        'autocomplete'=> 'name',
    ];
    return $fields;
});

add_filter('woocommerce_add_error', function ($message) {
    return str_replace('Billing Full name', 'Full name', $message);
});
