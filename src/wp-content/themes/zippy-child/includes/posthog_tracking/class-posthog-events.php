<?php

if (! defined('ABSPATH')) exit; // Exit if accessed directly

class PostHog_Events
{
  public function __construct()
  {
    add_action('wp_footer', array($this, 'inject_checkout_events'));
    // Save phid to order meta
    add_action('woocommerce_checkout_order_created', array($this, 'save_phid_to_order'), 10, 1);
    add_action('woocommerce_order_status_processing', array($this, 'track_purchase'));
    add_action('wp_footer', array($this, 'clear_signatures'));
  }

  /**
   * Get PostHog session ID from WooCommerce session if available.
   *
   * @return string|null The PostHog session ID or null if not set.
   */
  private function get_session_phid()
  {
    if (WC()->session) {
      return WC()->session->get('posthog_distinct_id') ?: null;
    }
    return null;
  }

  /**
   * Save PostHog session ID to order meta when order is created.
   *
   * @param WC_Order $order The order object.
   */
  public function save_phid_to_order($order)
  {
    $phid = $this->get_session_phid();
    if ($phid && $order) {
      $order->update_meta_data('posthog_distinct_id', $phid);
      $order->save();
    }
  }

  public function clear_signatures() {
    if (!function_exists('is_order_received_page') || !is_order_received_page()) return;
?>
    <script>
      sessionStorage.removeItem('ph_begin_checkout_cart_signature');
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

    $cart_items = [];

    foreach (WC()->cart->get_cart() as $cart_item) {
      $product = $cart_item['data'];
      $cart_items[] = [
        'product_id'   => $product->get_id(),
        'variation_id' => !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : 0,
        'quantity'     => $cart_item['quantity'],
        'price'        => (float) $product->get_price(),
        'name'         => $product->get_name(),
      ];
    }

    $cart_signature = md5(wp_json_encode(array_map(function($item) {
      return [
        'product_id'   => $item['product_id'],
        'variation_id' => $item['variation_id'],
        'quantity'     => $item['quantity'],
      ];
    }, $cart_items)));
?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        if (typeof posthog === 'undefined') return;

        const phid = '<?php echo esc_js($this->get_session_phid() ?: ''); ?>';
        var $billingEmail = jQuery('#billing_email');
        var identifiedByPhid = false;

        if (phid && !identifiedByPhid) {
          posthog.identify(phid);
          identifiedByPhid = true;
        }

        $billingEmail.on('blur', function() {
          var email = $billingEmail.val().trim();
          if (email && phid) {
            posthog.setPersonProperties({ email: email });
          } else if (email && !phid) {
            // Fallback: if no phid, identify by email
            posthog.identify(email, { email: email });
          }
        });

        // Track begin_checkout
        const currentCartSignature = <?php echo wp_json_encode($cart_signature); ?>;
        const previousCartSignature = sessionStorage.getItem('ph_begin_checkout_cart_signature');

        if (previousCartSignature !== currentCartSignature) {
          const checkoutProps = {
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
          };
          posthog.capture('begin_checkout', checkoutProps);

          sessionStorage.setItem('ph_begin_checkout_cart_signature', currentCartSignature);
        }
      });
    </script>
<?php
  }

  /**
   * Track purchase (server-side via PostHog Capture API).
   * Uses phid from order meta or email as distinct_id.
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

    $purchase_props = array(
      'order_id'        => $order_id,
      'order_value'     => (float) $order->get_total(),
      'currency'        => $order->get_currency(),
      'products'        => $items,
      'payment_method'  => $order->get_payment_method_title(),
      'coupon_codes'    => $coupon_codes,
      'referral_code'   => $order->get_meta('referral_code') ?: null,
    );

    // Get phid from order meta (saved when order was created)
    $phid = $order->get_meta('posthog_distinct_id');
    if ($email) {
      $purchase_props['email'] = $email;
    }
    $distinct_id = $phid ?: $email;
    $this->posthog_capture($distinct_id, 'purchase', $purchase_props);
  }

  /**
   * Send an event to PostHog via the HTTP capture API.
   *
   * @param string $distinct_id User identifier (phid, email, or anonymous ID).
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

    $paid_props = array(
      'order_id'      => $order_id,
      'order_value'   => (float) $order->get_total(),
      'currency'      => $order->get_currency(),
      'products'      => implode(', ', $product_names),
      'payment_method' => $order->get_payment_method_title(),
    );

    $phid = $order->get_meta('posthog_distinct_id');
    if ($email) {
      $paid_props['email'] = $email;
    }
    $distinct_id = $phid ?: $email;
    $this->posthog_capture($distinct_id, 'order_paid', $paid_props);
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

    $status_props = array(
      'order_id'   => $order_id,
      'old_status' => $old_status,
      'new_status' => $new_status,
      'order_value' => (float) $order->get_total(),
      'currency'   => $order->get_currency(),
    );

    $phid = $order->get_meta('posthog_distinct_id');
    if ($email) {
      $status_props['email'] = $email;
    }
    $distinct_id = $phid ?: $email;
    $this->posthog_capture($distinct_id, 'order_status_changed', $status_props);
  }
}
