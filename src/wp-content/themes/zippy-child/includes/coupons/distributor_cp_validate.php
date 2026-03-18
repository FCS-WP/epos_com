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
    if ($coupon->get_meta('is_distributor_coupon') !== 'yes') {
        return $is_valid;
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
        $(document.body).on('change', '#billing_company', function() {
            let val = $(this).val().trim();
            let $apply_btn = $('button[name="apply_coupon"]');
            if (val === '') {
                $apply_btn.prop('disabled', true);
                $('#woocommerce-form-coupon-toggle').after('<p id="company-note" style="color:#d63638; display:none;">Please enter your company before applying a distributor coupon.</p>');
                $('#company-note').show();
            } else {
                $apply_btn.prop('disabled', false);
                $('#company-note').hide();
            }
            $('form.checkout').trigger('update_checkout');
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