<?php
class My_FB_WC_Events
{
  public function __construct()
  {
    // Track View Content
    // add_action('wp_footer', array($this, 'track_view_content'));
    // Track Purchase
    add_action('woocommerce_thankyou', array($this, 'track_purchase'));
  }

  public function track_view_content()
  {
    if (! is_page('bluetap')) return;

    global $product;
    $payload = [
      'content_name' => $product->get_name(),
      'content_ids'  => [(string) $product->get_id()],
      'content_type' => 'product',
      'value'        => $product->get_price(),
      'currency'     => get_woocommerce_currency(),
    ];

    printf(
      "<script>fbq('track', 'ViewContent', %s, { eventID: '%s' });</script>",
      json_encode($payload),
      My_FB_Init::get_event_id()
    );
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
