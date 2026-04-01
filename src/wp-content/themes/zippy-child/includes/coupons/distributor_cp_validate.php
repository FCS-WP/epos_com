<?php
// Hide default coupon error message for distributor coupon
add_filter('woocommerce_coupon_error', function ($msg, $code, $coupon) {
    if ($coupon && is_distributor_coupon($coupon)) {
        return '';
    }
    return $msg;
}, 10, 3);

// Success message for distributor coupon
add_filter('woocommerce_coupon_message', function ($msg, $msg_code, $coupon) {
    if (!$coupon || !is_distributor_coupon($coupon)) {
        return $msg;
    }
    if ($msg_code !== WC_Coupon::WC_COUPON_SUCCESS) {
        return $msg;
    }
    if (!WC()->cart || !in_array($coupon->get_code(), WC()->cart->get_applied_coupons())) {
        return '';
    }
    $billing_company = get_checkout_billing_company();
    return sprintf(
        __('Distributor coupon for %s applied successfully.', 'woocommerce'),
        $billing_company
    );
}, 20, 3);

// Functions
// check valid distributor coupon
function is_distributor_coupon(WC_Coupon $coupon): bool {
    return $coupon->get_meta('is_distributor_coupon') === 'yes';
}

// get billing company name
function get_checkout_billing_company() {
    if (function_exists('WC') && WC()->checkout()) {
        $posted_data = WC()->checkout()->get_posted_data();
        if (!empty($posted_data['billing_company'])) {
            return strtolower(trim(wc_clean($posted_data['billing_company'])));
        }
    }
    if (WC()->customer) {
        $company = WC()->customer->get_billing_company();
        if ($company) {
            return strtolower(trim($company));
        }
    }
    return '';
}

// distributor coupon validation
add_filter('woocommerce_coupon_is_valid', function ($is_valid, $coupon) {
    if (!is_distributor_coupon($coupon)) {
        return $is_valid;
    }

    // Only 1 distributor coupon can be applied at a time
    $applied = WC()->cart ? WC()->cart->get_applied_coupons() : [];
    foreach ($applied as $applied_code) {
        if ($applied_code === $coupon->get_code()) {
            continue;
        }
        $applied_coupon = new WC_Coupon($applied_code);
        if (is_distributor_coupon($applied_coupon)) {
            wc_add_notice(
                __( 'Only one distributor coupon can be applied at a time.', 'woocommerce' ),
                'error'
            );
            return false;
        }
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
        WC()->cart->remove_coupon($coupon->get_code());
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


// Hide coupons apply on cart page
add_filter('woocommerce_coupons_enabled', function ($enabled) {
    if (is_cart()) {
        return false;
    }
    return $enabled;
});

// Update billing company 
add_action('wp_footer', function () {
    if (is_checkout()) {
?>
    <script>
        jQuery(function($) {
            $(document.body).on('input', '#billing_company', function() {
                let val = $(this).val().trim();
                let $apply_btn = $('button[name="apply_coupon"]');
                let timer;
                
                clearTimeout(timer);
                timer = setTimeout(() => {
                    $('form.checkout').trigger('update_checkout');
                }, 750);
            });
        });
    </script>
<?php
    }
});
add_action('woocommerce_checkout_update_order_review', function ($post_data) {
    parse_str($post_data, $data);
    if (!empty($data['billing_company'])) {
        WC()->customer->set_billing_company($data['billing_company']);
    } else {
        WC()->customer->set_billing_company('');
    }
}, 1);