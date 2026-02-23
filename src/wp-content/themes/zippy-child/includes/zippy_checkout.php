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
    'label'       => ('Referral Code'),
    'placeholder' => ('Referral Code'),
    'required'    => false,
  ], $checkout->get_value('referral_code'));
});

// Show in order dashboard
add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
  $eg = $order->get_meta('referral_code');
  if ($eg) {
    echo '<p><strong>' . __('Referral Code', 'woocommerce') . ':</strong> ' . esc_html($eg) . '</p>';
  }
});


// Show in order detail
add_action('woocommerce_order_details_after_customer_details', function ($order) {
  $eg = $order->get_meta('referral_code');
  if ($eg) {
    echo '<p><strong>' . __('Referral Code', 'woocommerce') . ':</strong> ' . esc_html($eg) . '</p>';
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
}, 99, 2);
