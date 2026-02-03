<?php
class My_FB_WC_Events
{
  public function __construct()
  {
    // Track View Content
    add_action('wp_footer', array($this, 'fb_js_initiate_checkout'));
    // Track Purchase
    add_action('woocommerce_thankyou', array($this, 'track_purchase'));
  }

  public function fb_js_initiate_checkout()
  {
    if (! is_checkout() || is_wc_endpoint_url('order-received')) {
      return;
    }

    $cart = WC()->cart;
    $payload = [
      'content_ids'  => array_values(array_map(function ($item) {
        return (string) $item['product_id'];
      }, $cart->get_cart())),
      'content_type' => 'product',
      'value'        => (float) $cart->get_total('edit'),
      'currency'     => get_woocommerce_currency(),
      'num_items'    => $cart->get_cart_contents_count(),
    ];
?>
    <script type="text/javascript">
      jQuery(function($) {
        /* Listen for the checkout form submission */
        $('form.checkout').on('checkout_place_order', function() {
          fbq('track', 'InitiateCheckout', <?php echo json_encode($payload); ?>, {
            eventID: '<?php echo My_FB_Init::get_event_id(); ?>'
          });
        });
      });
    </script>
<?php
  }

  public function track_purchase($order_id)
  {
    $order = wc_get_order($order_id);
    $product_ids = [];

    foreach ($order->get_items() as $item) {
      $product_ids[] = (string) $item->get_product_id();
    }

    $payload = [
      'content_ids'  => $product_ids,
      'content_type' => 'product',
      'value'        => $order->get_total(),
      'currency'     => $order->get_currency(),
    ];

    printf(
      "<script>fbq('track', 'Purchase', %s, { eventID: 'PURCHASE_%s' });</script>",
      json_encode($payload),
      $order_id
    );
  }
}
