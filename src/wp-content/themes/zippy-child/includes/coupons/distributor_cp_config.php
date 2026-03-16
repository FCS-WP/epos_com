<?php 
add_action('woocommerce_coupon_options', function ($coupon_id) {
    $is_distributor = get_post_meta($coupon_id, 'is_distributor_coupon', true);
    // Checkbox Distributor Coupon
    woocommerce_wp_checkbox([
        'id'          => 'is_distributor_coupon',
        'label'       => __('Distributor coupon', 'woocommerce'),
        'description' => __('Mark this coupon as a distributor coupon (update this coupon when choose this option). ', 'woocommerce'),
    ]);

    if ($is_distributor === 'yes') {
        // Distributor Name fieldc
        woocommerce_wp_text_input([
            'id'          => 'distributor_name',
            'label'       => __('Distributor name', 'woocommerce'),
            'placeholder' => 'Enter distributor name',
            'desc_tip'    => true,
            'description' => 'Name of the distributor for this coupon',
        ]);

        // Distributor blue tap required quantity field
        woocommerce_wp_text_input([
            'id' => 'distributor_required_qty',
            'label' => __('Minimum quantity', 'woocommerce'),
            'type' => 'number',
            'desc_tip' => true,
            'description' => 'Minimum Bluetap quantity required to apply this coupon.',
        ]);
    }
});

add_action('woocommerce_coupon_options_save', function ($coupon_id) {
    $value = isset($_POST['is_distributor_coupon']) ? 'yes' : 'no';
    update_post_meta($coupon_id, 'is_distributor_coupon', $value);

    if (isset($_POST['distributor_name'])) {
        update_post_meta(
            $coupon_id,
            'distributor_name',
            sanitize_text_field($_POST['distributor_name'])
        );
    }
    if (isset($_POST['distributor_required_qty'])) {
        update_post_meta(
            $coupon_id,
            'distributor_required_qty',
            intval($_POST['distributor_required_qty'])
        );
    }
});


// Display in coupon list
add_filter('manage_edit-shop_coupon_columns', function ($columns) {
    $new_columns = [];
    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;
        if ($key === 'coupon_code') {
            $new_columns['distributor_name'] = 'Distributor Name';
        }
    }
    return $new_columns;
});
add_action('manage_shop_coupon_posts_custom_column', function ($column, $post_id) {
    if ($column === 'distributor_name') {
        $name = get_post_meta($post_id, 'distributor_name', true);
        if ($name) {
            echo esc_html($name);
        } else {
            echo '—';
        }
    }
}, 10, 2);