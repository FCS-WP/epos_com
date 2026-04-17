<?php

if (! defined('ABSPATH')) {
  exit;
}

class Webflow_Cart_Service
{
  /**
   * Return cart_count + cart_url only.
   *
   * @return array|WP_Error
   */
  public function get_cart_count_payload()
  {
    $cart = $this->get_cart_instance();
    if (is_wp_error($cart)) {
      return $cart;
    }

    return array(
      'success'    => true,
      'cart_count' => $this->resolve_cart_count($cart),
      'cart_url'   => $this->get_cart_url(),
    );
  }

  /**
   * @return string
   */
  public function get_cart_url()
  {
    if (function_exists('wc_get_cart_url')) {
      return wc_get_cart_url();
    }

    return home_url('/cart/');
  }

  /**
   * @return WC_Cart|WP_Error
   */
  protected function get_cart_instance()
  {
    if (! class_exists('WooCommerce') || ! function_exists('WC')) {
      return new WP_Error(
        'bluetap_woocommerce_unavailable',
        'WooCommerce is not available.',
        array('status' => 503)
      );
    }

    $woocommerce = WC();
    if (! $woocommerce) {
      return new WP_Error(
        'bluetap_woocommerce_unavailable',
        'WooCommerce could not be initialized.',
        array('status' => 503)
      );
    }

    if (method_exists($woocommerce, 'initialize_session')) {
      $woocommerce->initialize_session();
    }

    if (method_exists($woocommerce, 'initialize_cart')) {
      $woocommerce->initialize_cart();
    }

    if (function_exists('wc_load_cart')) {
      wc_load_cart();
    }

    if (! isset($woocommerce->cart) || ! is_a($woocommerce->cart, 'WC_Cart')) {
      return new WP_Error(
        'bluetap_cart_unavailable',
        'WooCommerce cart is unavailable.',
        array('status' => 503)
      );
    }

    return $woocommerce->cart;
  }

  /**
   * Resolve cart count reliably from cart state.
   *
   * @param WC_Cart $cart
   * @return int
   */
  protected function resolve_cart_count($cart)
  {
    $count = (int) $cart->get_cart_contents_count();
    if ($count > 0) {
      return $count;
    }

    $items = $cart->get_cart();
    if (! is_array($items) || empty($items)) {
      return 0;
    }

    $fallback_count = 0;
    foreach ($items as $item) {
      $fallback_count += isset($item['quantity']) ? absint($item['quantity']) : 0;
    }

    return (int) $fallback_count;
  }
}
