<?php

if (! defined('ABSPATH')) exit; // Exit if accessed directly

class PostHog_Events
{
  public function __construct()
  {
    // add_action('wp_footer', array($this, 'inject_woocommerce_events'));
    add_action('wp_footer', array($this, 'inject_checkout_events'));
    // add_action('wp_footer', array($this, 'inject_order_received_identify'));
    // add_action('wp_footer', array($this, 'inject_promo_popup_events'));
    // add_action('wp_footer', array($this, 'inject_onboarding_events'));
    // add_action('wp_footer', array($this, 'inject_homepage_tab_events'));
    add_action('woocommerce_payment_complete', array($this, 'track_purchase'));
  }

  /**
   * Track purchase (server-side via PostHog Capture API).
   * Uses the billing email as distinct_id to correlate with client-side events.
   */
  public function track_purchase($order_id) {
    if (!$order_id) return;

    $order = wc_get_order($order_id);
    if (!$order) return;

    $email = $order->get_billing_email();
    $items = [];

    $coupon_codes = $order->get_coupon_codes();

    foreach ($order->get_items() as $item) {
      $product = $item->get_product();
      $items[] = [
        'product_id' => $product ? $product->get_id() : null,
        'name'       => $item->get_name(),
        'quantity'   => $item->get_quantity(),
        'price'      => $product ? (float) $product->get_price() : 0,
      ];
    }

    $this->posthog_capture($email, 'purchase', array(
      'order_id'        => $order_id,
      'order_value'     => (float) $order->get_total(),
      'currency'        => $order->get_currency(),
      'products'        => $items,
      'payment_method'  => $order->get_payment_method_title(),
      'coupon_codes'    => $coupon_codes,
      'referral_code'   => $order->get_meta('referral_code') ?: null,
    ));
  }

  /**
   * Track add_to_cart via the WooCommerce added_to_cart JS event.
   */
  public function inject_woocommerce_events()
  {
?>
    <script>
      jQuery(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button) {
        if (typeof posthog === 'undefined') return;
        posthog.capture('add_to_cart', {
          product_id: String($button.data('product_id') || ''),
          product_name: $button.attr('aria-label') || '',
          quantity: parseInt($button.data('quantity')) || 1,
          price: parseFloat($button.data('price')) || 0,
          currency: '<?php echo esc_js(get_woocommerce_currency()); ?>',
        });
      });
    </script>
<?php
  }

  /**
   * Track checkout interactions: coupon applied and place order clicked.
   */
  public function inject_checkout_events()
  {
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page()) {
      return;
    }
?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        if (typeof posthog === 'undefined') return;

        // Track coupon applied
        // jQuery(document.body).on('applied_coupon_in_checkout', function(e, coupon) {
        //   posthog.capture('coupon_applied', {
        //     coupon_code: coupon || '',
        //   });
        // });

        // Track place order button click
        // jQuery(document).on('click', '#place_order', function() {
        //   posthog.capture('place_order_clicked', {
        //     page: window.location.href,
        //   });
        // });

        // Identify guest users as soon as they enter their billing email
        var $billingEmail = jQuery('#billing_email');
        var identifiedByEmail = false;
        $billingEmail.on('blur', function() {
          var email = $billingEmail.val().trim();
          if (email && !identifiedByEmail) {
            posthog.identify(email, { email: email });
            identifiedByEmail = true;
          }
        });

        // Track begin_checkout
        // if (!sessionStorage.getItem('ph_begin_checkout_fired')) {
          console.log('begin_checkout');
          posthog.capture('begin_checkout', {
            currency: '<?php echo esc_js(get_woocommerce_currency()); ?>',
            value: <?php echo WC()->cart ? (float) WC()->cart->total : 0; ?>,
            items: <?php echo wp_json_encode(array_map(function($cart_item) {
              $product = $cart_item['data'];
              return [
                'product_id' => $product->get_id(),
                'name'       => $product->get_name(),
                'quantity'   => $cart_item['quantity'],
                'price'      => (float) $product->get_price(),
              ];
            }, WC()->cart ? WC()->cart->get_cart() : [])); ?>
          });
        //   sessionStorage.setItem('ph_begin_checkout_fired', '1');
        // }
      });
    </script>
<?php
  }

  /**
   * Identify guest users on the order received (thank-you) page.
   * This ensures the purchase server-side event distinct_id matches client-side.
   */
  public function inject_order_received_identify()
  {
    if (! function_exists('is_order_received_page') || ! is_order_received_page()) return;

    $order_id = absint(get_query_var('order-received'));
    if (! $order_id) return;

    $order = wc_get_order($order_id);
    if (! $order) return;

    $email = $order->get_billing_email();
    if (! $email) return;
?>
    <script>
      if (typeof posthog !== 'undefined') {
        posthog.identify('<?php echo esc_js($email); ?>', {
          email: '<?php echo esc_js($email); ?>',
        });
      }
    </script>
<?php
  }

  /**
   * Send an event to PostHog via the HTTP capture API.
   *
   * @param string $distinct_id User identifier (email or anonymous ID).
   * @param string $event       Event name.
   * @param array  $properties  Additional event properties.
   */
  private function posthog_capture($distinct_id, $event, $properties = array())
  {
    $api_key = get_field('posthog_api_key', 'option');
    $host    = get_field('posthog_host', 'option');

    if (! $api_key || ! $host) return;

    $payload = array(
      'api_key'     => $api_key,
      'event'       => $event,
      'distinct_id' => $distinct_id,
      'properties'  => array_merge(
        array('$lib' => 'posthog-php-wp'),
        $properties
      ),
      'timestamp'   => gmdate('c'),
    );

    wp_remote_post(trailingslashit($host) . 'capture/', array(
      'headers' => array('Content-Type' => 'application/json'),
      'body'    => wp_json_encode($payload),
      'timeout' => 10,
      'blocking' => false,
    ));
  }

  /**
   * Track order paid (status → processing) with order details.
   *
   * @param int      $order_id Order ID.
   * @param WC_Order $order    Order object.
   */
  public function track_order_paid($order_id, $order)
  {
    $email = $order->get_billing_email();

    $product_names = array();
    foreach ($order->get_items() as $item) {
      $product_names[] = $item->get_name();
    }

    $this->posthog_capture($email, 'order_paid', array(
      'order_id'      => $order_id,
      'order_value'   => (float) $order->get_total(),
      'currency'      => $order->get_currency(),
      'products'      => implode(', ', $product_names),
      'payment_method' => $order->get_payment_method_title(),
    ));
  }

  /**
   * Track every order status transition.
   *
   * @param int      $order_id   Order ID.
   * @param string   $old_status Previous status slug.
   * @param string   $new_status New status slug.
   * @param WC_Order $order      Order object.
   */
  public function track_order_status_changed($order_id, $old_status, $new_status, $order)
  {
    // Avoid duplicating the order_paid event already fired above.
    if ($old_status === 'pending' && $new_status === 'processing') return;

    $email = $order->get_billing_email();

    $this->posthog_capture($email, 'order_status_changed', array(
      'order_id'   => $order_id,
      'old_status' => $old_status,
      'new_status' => $new_status,
      'order_value' => (float) $order->get_total(),
      'currency'   => $order->get_currency(),
    ));
  }
}
