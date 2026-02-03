<?php
// Functions
// Check exist domain using php DNS resolver
function email_domain_is_exist($email) {
    if (!is_email($email)) {
        return false;
    }
    $domain = substr(strrchr($email, "@"), 1);
    return checkdnsrr($domain, 'MX');
}


// Actions
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


// Check email domain
add_action('woocommerce_after_checkout_validation', function ($data, $errors) {
    if (empty($data['billing_email'])) {
        return;
    }

    if (!email_domain_is_exist($data['billing_email'])) {
        $errors->add(
            'billing_email',
            __('<strong>Email domain</strong> does not exist. Please use a valid email address.', 'woocommerce')
        );
    }
}, 10, 2);



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



// Filters
// Company name input is required
add_filter('woocommerce_checkout_fields', function($fields) {
    $fields['billing']['billing_company']['required'] = true;
    $fields['billing']['billing_company']['label'] = 'Company name';
    return $fields;
});
add_filter('woocommerce_checkout_required_field_notice', function ($message, $field_label) {
    if ($field_label === 'Billing Email address') {
        return '<strong>Email address</strong> is required.';
    }
    return $message;
}, 10, 2);


// Email address input is required
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
