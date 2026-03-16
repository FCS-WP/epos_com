<?php
// Hide default coupon error message for distributor coupon
add_filter('woocommerce_coupon_error', function ($msg, $code, $coupon) {
    if ($coupon && $coupon->get_meta('is_distributor_coupon') === 'yes') {
        return '';
    }
    return $msg;
}, 10, 3);

// Success message for distributor coupon
add_filter('woocommerce_coupon_message', function ($msg, $msg_code, $coupon) {

    if (!$coupon || $coupon->get_meta('is_distributor_coupon') !== 'yes') {
        return $msg;
    }

    // chỉ chạy khi thực sự success
    if ($msg_code !== WC_Coupon::WC_COUPON_SUCCESS) {
        return $msg;
    }

    // nếu coupon đã bị remove trong validation
    if (!WC()->cart || !in_array($coupon->get_code(), WC()->cart->get_applied_coupons())) {
        return '';
    }

    $billing_company = trim(WC()->customer->get_billing_company());

    return sprintf(
        __('Distributor coupon for %s applied successfully.', 'woocommerce'),
        $billing_company
    );

}, 20, 3);

// Functions
function get_checkout_billing_company() {

    // lấy data trực tiếp từ checkout object
    if (function_exists('WC') && WC()->checkout()) {

        $posted_data = WC()->checkout()->get_posted_data();

        if (!empty($posted_data['billing_company'])) {
            return strtolower(trim(wc_clean($posted_data['billing_company'])));
        }
    }

    // fallback session
    if (WC()->customer) {
        return strtolower(trim(WC()->customer->get_billing_company()));
    }

    return '';
}

add_action('woocommerce_checkout_update_order_review', function ($post_data) {
    parse_str($post_data, $data);
    if (!empty($data['billing_company'])) {
        WC()->customer->set_billing_company($data['billing_company']);
    } else {
        WC()->customer->set_billing_company('');
    }
}, 1);

// distributor coupon validation
add_filter('woocommerce_coupon_is_valid', function ($is_valid, $coupon) {
    if (!$is_valid) {
        return $is_valid;
    }

    if ($coupon->get_meta('is_distributor_coupon') !== 'yes') {
        return $is_valid;
    }

    if (is_cart()) {
        wc_add_notice(
            __('Please checkout order to apply this coupon.', 'woocommerce'),
            'error'
        );
        return false;
    }

    $distributor_name = strtolower(trim($coupon->get_meta('distributor_name')));
    $billing_company = get_checkout_billing_company();

    if (!$billing_company) {
        wc_add_notice(
            __('Please enter your company name at form below to apply this coupon.', 'woocommerce'),
            'error'
        );
        return false;
    }

    if ($billing_company !== $distributor_name) {
        wc_add_notice(
            sprintf(
                __('This coupon is not valid for distributor %s, please check company name again.', 'woocommerce'),
                $billing_company
            ),
            'error'
        );
        return false;
    }

    $required_bt_qty = (int) $coupon->get_meta('distributor_required_qty');
    $total_bt_qty = WC()->cart->get_cart_item_quantities()[BLUETAP_PRODUCT_ID] ?? 0;
    if ($total_bt_qty < $required_bt_qty) {
        wc_add_notice(
            sprintf(
                __('Minimum of %d Bluetap units required for distributors %s.', 'woocommerce'),
                $required_bt_qty,
                $billing_company
            ),
            'error'
        );
        return false;
    }
    return $is_valid;
}, 10, 2);

// revalidate
add_action('woocommerce_after_checkout_validation', function ($data, $errors) {
    if (!WC()->cart) {
        return;
    }    
    
    foreach (WC()->cart->get_applied_coupons() as $coupon_code) {
        $coupon = new WC_Coupon($coupon_code);
        if ($coupon->get_meta('is_distributor_coupon') !== 'yes') {
            continue;
        }
        
        $billing_company = get_checkout_billing_company();
        $distributor_name = strtolower(trim($coupon->get_meta('distributor_name')));

        if ($billing_company !== $distributor_name) {
            if (!wc_has_notice(
                sprintf(
                    __('This coupon is not valid for distributor %s, please check company name again.', 'woocommerce'),
                    $billing_company
                ),
                'error'
            )) {

                wc_add_notice(
                    sprintf(
                        __('This coupon is not valid for distributor %s, please check company name again.', 'woocommerce'),
                        $billing_company
                    ),
                    'error'
                );
            }
            WC()->cart->remove_coupon($coupon_code);
        }

        $required_bt_qty = (int) $coupon->get_meta('distributor_required_qty');
        $total_bt_qty = WC()->cart->get_cart_item_quantities()[BLUETAP_PRODUCT_ID] ?? 0;
        if ($total_bt_qty < $required_bt_qty) {
            wc_add_notice(
                sprintf(
                    __('Minimum of %d Bluetap units required for distributors %s.', 'woocommerce'),
                    $required_bt_qty,
                    $billing_company
                ),
                'error'
            );
            WC()->cart->remove_coupon($coupon_code);
        }
    }
}, 10, 2);


