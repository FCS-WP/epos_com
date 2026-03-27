<?php
// Check cart item has Bluetap360
function cart_has_product_bluetap360()
{
  if (!WC()->cart || WC()->cart->is_empty()) {
    return false;
  }

  $target_id = (int) BLUETAP_PRODUCT_ID;

  foreach (WC()->cart->get_cart() as $cart_item) {
    if (
      $target_id === (int) $cart_item['product_id'] ||
      $target_id === (int) $cart_item['variation_id']
    ) {
      return true;
    }
  }

  return false;
}

// Custom order Referral code field for checkout
add_action('woocommerce_after_checkout_billing_form', function ($checkout) {
  if (!cart_has_product_bluetap360()) {
    return;
  }
  woocommerce_form_field('referral_code', [
    'type'        => 'text',
    'class'       => ['form-row-wide'],
    'label'       => Lang::translate('Referral Code'),
    'placeholder' => Lang::translate('Referral Code'),
    'required'    => false,
  ], $checkout->get_value('referral_code'));
});

// Show in order dashboard
add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
  $eg = $order->get_meta('referral_code');
  $coupon = $order->get_meta('_distributor_coupon');
  $distributor = $order->get_meta('_distributor_name');
  if ($eg) {
    echo '<p><strong>' . __('Referral Code', 'woocommerce') . ':</strong> ' . esc_html($eg) . '</p>';
  }
  if ($coupon) {
    echo '<p><strong>' . __('Distributor Coupon', 'woocommerce') . ':</strong> ' . esc_html($coupon) . '<br>';
    echo '<strong>' . __('Distributor Name', 'woocommerce') . ':</strong> ' . esc_html($distributor) . '</p>';
  }
});


// Show in order detail
add_action('woocommerce_order_details_after_customer_details', function ($order) {
  $coupon = $order->get_meta('_distributor_coupon');
  $distributor = $order->get_meta('_distributor_name');
  $eg = $order->get_meta('referral_code');
  if ($eg) {
    echo '<p><strong>' . __('Referral Code', 'woocommerce') . ':</strong> ' . esc_html($eg) . '</p>';
  }
  if ($coupon) {
    echo '<section class="woocommerce-distributor-info">';
    echo '<h2>' . __('Distributor Information', 'woocommerce') . '</h2>';
    echo '<p><strong>' . __('Coupon', 'woocommerce') . ':</strong> ' . esc_html($coupon) . '<br>';
    echo '<strong>' . __('Distributor', 'woocommerce') . ':</strong> ' . esc_html($distributor) . '</p>';
    echo '</section>';
  }
});


// Show in mail
add_filter('woocommerce_email_order_meta_fields', function ($fields, $sent_to_admin, $order) {
  $eg = $order->get_meta('referral_code');
  if ($eg) {
    $fields['referral_code'] = [
      'label' => __('Referral Code', 'woocommerce'),
      'value' => $eg,
    ];
  }

  return $fields;
}, 10, 3);



add_action('woocommerce_checkout_create_order', function ($order, $data) {
  // Handle custom referral_code field
  if (!empty($_POST['referral_code'])) {
    $order->update_meta_data(
      'referral_code',
      sanitize_text_field($_POST['referral_code'])
    );
  }

  foreach (WC()->cart->get_applied_coupons() as $coupon_code) {
    $coupon = new WC_Coupon($coupon_code);

    if ($coupon->get_meta('is_distributor_coupon') !== 'yes') {
        continue;
    }

    $distributor_name = $coupon->get_meta('distributor_name');

    $order->update_meta_data('_distributor_coupon', $coupon_code);
    $order->update_meta_data('_distributor_name', $distributor_name);
  }
}, 99, 2);

/**
 * Update some texts
 */
add_filter( 'gettext', function( $translated_text, $text, $domain ) {
  if ( is_checkout() && 'woocommerce' === $domain ) {
    switch($text) {
      case 'Place order':
        return __( 'Continue To Payment', 'flatsome' );
        break;
      case 'Phone':
        return __( 'Phone number', 'flatsome' );
        break;
      case 'Country / Region':
        return __( 'Country', 'flatsome' );
        break;
      case 'State / County':
        return __( 'State / Region', 'flatsome' );
        break;
      case 'Your order':
        return __( 'Order summary', 'flatsome' );
        break;
      case 'Apply coupon':
        return __( 'Apply', 'flatsome' );
        break;
      case 'Coupon code':
        return __( 'Have a promo code?', 'flatsome' );
        break;
      case 'Apartment, suite, unit, etc.':
        return __( 'Apartment / Unit', 'flatsome' );
        break;
    }
  }
  return $translated_text;
}, 10, 3 );

/**
 * Adjust coupon form placement
 */
add_action( 'woocommerce_checkout_order_review', function() {
  if ( wc_coupons_enabled() ) {
    echo '
    <div class="coupon js-coupon">
      <div class="flex-row">
        <div class="flex-col flex-grow">
          <input type="text" name="epos_coupon" class="input-text coupon-btn js-coupon-input" placeholder="Have a promo code?" value="">
        </div>
        <div class="flex-col">
          <button class="button expand js-coupon-submit" name="apply_coupon" value="Apply">Apply</button>
        </div>
      </div>
    </div>';
  }
}, 10 );

/**
 * Add Checkout page title
 */
add_action( 'woocommerce_checkout_before_customer_details', function() {
  echo '<h1 class="checkout-title">'._('Checkout Details', 'flatsome').'</h1>';
}, 10 );

/**
 * Add recipient
 */
add_filter('woocommerce_checkout_fields', function ($fields) {
  $fields['billing']['billing_recipient'] = [
    'type'        => 'text',
    'label'       => 'Recipient',
    'required'    => true,
    'priority'    => 4,
    'class'       => ['form-row-first'],
    'autocomplete'=> 'name',
  ];
  return $fields;
});

/**
 * Display recipient in order received page
 */
add_action('woocommerce_order_details_after_customer_address', function($group, $order) {
  echo '
  <p>
    <strong>Recipient:</strong>
    '.esc_html($order->get_meta( '_billing_recipient' )).'
  </p>';
}, 10, 2);

/**
 * Display recipient in admin order page
 */
add_action('woocommerce_admin_order_data_after_billing_address', function($order) {
  echo '
  <p>
    <strong>Recipient:</strong>
    '.esc_html($order->get_meta('_billing_recipient')).'
  </p>';
});

/**
 * Reposition checkout fields
 */
add_filter('woocommerce_checkout_fields', function($fields) {
  $fields['billing']['billing_full_name']['priority'] = 0; // Full name
  $fields['billing']['billing_full_name']['class'] = 'form-row-first';
  $fields['billing']['billing_email']['priority'] = 1; // Email address
  $fields['billing']['billing_email']['class'] = 'form-row-last';
  $fields['billing']['billing_phone']['priority'] = 2; // Phone number
  $fields['billing']['billing_phone']['class'] = 'form-row-first';
  $fields['billing']['billing_country']['priority'] = 3; // Country
  $fields['billing']['billing_country']['class'] = 'form-row-last';
  $fields['billing']['billing_country']['custom_attributes']['readonly'] = 'readonly';

  // 4. Recipient
  $fields['billing']['billing_company']['priority'] = 5; // Company name
  $fields['billing']['billing_company']['class'] = 'form-row-last';

  return $fields;
}, 99, 1);

add_filter('woocommerce_default_address_fields', function($fields) {
  $fields['state']['priority'] = 6; // State / Region
  $fields['address_1']['priority'] = 7; // Street address
  $fields['address_2']['priority'] = 8; // Apartment / Unit
  $fields['postcode']['priority'] = 9; // Postcode / ZIP
  $fields['city']['priority'] = 10; // Town / City
  $fields['postcode']['class'] = 'form-row-first';
  $fields['city']['class'] = 'form-row-last';

  return $fields;
}, 10, 1);

add_filter('woocommerce_form_field', function($field, $key, $args, $value) {
  $field = start_wrapper($key) . $field . end_wrapper($key);
  return $field;
}, 10, 4);

/**
 * Add wrapper
 */
function start_wrapper($key) {
  switch($key) {
    case 'billing_full_name':
      return '
      <div class="epos-checkout__block js-checkout-block">
        <div class="epos-checkout__header js-checkout-header">
          <span>1. '.__('Contact Information', 'flatsome').'</span>
        </div>
        <div class="epos-checkout__content js-checkout-content">
          <div class="epos-checkout__content-inner js-checkout-inner">
      ';
      break;
    case 'billing_recipient':
      return '
          </div>
        </div>
      </div>
      <div class="epos-checkout__block js-checkout-block">
        <div class="epos-checkout__header js-checkout-header">
          <span>2. '.__('Delivery Information', 'flatsome').'</span>
        </div>
        <div class="epos-checkout__content js-checkout-content">
          <div class="epos-checkout__content-inner js-checkout-inner">
      ';
      break;
    default:
      return '';
      break;
  }
}

/**
 * Add end of wrapper
 */
function end_wrapper($key) {
  switch($key) {
    case 'referral_code':
      return '</div></div></div>';
      break;
    default:
      return '';
      break;
  }
}

/**
 * Add individual item price
 */
add_filter( 'woocommerce_checkout_cart_item_quantity', function($html, $cart_item, $cart_item_key) {
  $product = $cart_item['data'];
  $single_price = wc_price( wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
  
  return '
  <strong class="product-quantity">'
    .sprintf( '%s&nbsp;&times;&nbsp;%s', $single_price, $cart_item['quantity'] ).
  '</strong>';
}, 10, 3 );

/**
 * Add secure checkout label
 */
add_action( 'woocommerce_checkout_order_review', function() {
  echo '
  <div class="order-secure-checkout">
    <span>'.esc_html( 'Secure checkout powered by Antom', 'woocommerce' ).'</span>
  </div>';
}, 30 );

/**
 * Override order-receipt.php
 */
add_filter('woocommerce_locate_template', function($template, $template_name, $template_path, $default_path) {
  // Target the specific template
  if ($template_name === 'checkout/order-receipt.php') {
      
    // Build the full file path to your child theme's override
    $child_theme_template = get_stylesheet_directory() . '/woocommerce/' . $template_name;
    
    // Check if the file actually exists in your child theme
    if (file_exists($child_theme_template)) {
      return $child_theme_template;
    }
  }
  
  // If not found or not the right template, return the original $template
  return $template;
}, 99, 4);
