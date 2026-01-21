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

    wp_enqueue_script(
        'checkout-phone',
        get_stylesheet_directory_uri() . '/assets/js/checkout-phone.js',
        ['intl-tel-input'],
        '1.0',
        true
    );
});

// Custom order MCC/UEN field for checkout
add_action('woocommerce_before_order_notes', function ($checkout) {
    woocommerce_form_field('order_tag', [
        'type'        => 'text',
        'class'       => ['form-row-wide'],
        'label'       => __('E.g.MCC, UEN'),
        'placeholder' => __('MCC/UEN'),
        'required'    => false,
    ], $checkout->get_value('order_tag'));
});
add_action('woocommerce_checkout_create_order', function ($order) {
    if (!empty($_POST['order_tag'])) {
        $order->update_meta_data(
            'Tag',
            sanitize_text_field($_POST['order_tag'])
        );
    }
});
// Show in order dashboard
add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
    $tag = $order->get_meta('Tag');
    if ($tag) {
        echo '<p><strong>Tag:</strong> ' . esc_html($tag) . '</p>';
    }
});
// Show in mail
add_filter('woocommerce_email_order_meta_fields', function ($fields, $sent_to_admin, $order) {
    $tag = $order->get_meta('Tag');
    if ($tag) {
        $fields['order_tag'] = [
            'label' => __('Tag'),
            'value' => $tag,
        ];
    }
    return $fields;
}, 10, 3);




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
