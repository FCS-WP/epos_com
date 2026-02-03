<?php

/**
 * Handle Meta Conversions API (Server-Side Tracking)
 */
class My_FB_CAPI
{
  private $access_token;
  private $pixel_id;
  private $api_version  = 'v19.0';

  public function __construct()
  {
    $this->access_token = get_my_fb_access_token();
    $this->pixel_id     = get_my_fb_pixel_id();

    // Hook into WooCommerce purchase
    if ($this->access_token && $this->pixel_id) {
      add_action('woocommerce_order_status_processing', array($this, 'send_purchase_to_capi'));
    }
  }

  /**
   * Send events to Meta
   */
  public function send_event_to_meta($event_name, $event_id, $custom_data, $user_data)
  {
    $url = "https://graph.facebook.com/{$this->api_version}/{$this->pixel_id}/events?access_token={$this->access_token}";

    $body = [
      'data' => [
        [
          'event_name' => $event_name,
          'event_time' => time(),
          'event_id'   => $event_id,
          'action_source' => 'website',
          'event_source_url' => home_url(add_query_arg([], $GLOBALS['wp']->request)),
          'user_data'  => $user_data,
          'custom_data' => $custom_data,
        ]
      ]
    ];

    $response = wp_remote_post($url, [
      'method'    => 'POST',
      'body'      => json_encode($body),
      'headers'   => ['Content-Type' => 'application/json'],
      'timeout'   => 15,
    ]);

    return $response;
  }

  /**
   * Track Purchase via CAPI
   */
  public function send_purchase_to_capi($order_id)
  {
    $order = wc_get_order($order_id);

    if (! $order) return;

    if ($order->get_meta('_fb_capi_sent') === 'yes') {
      return;
    }
    //Prepare Custom Data
    $custom_data = [
      'value'    => (float) $order->get_total(),
      'currency' => strtoupper($order->get_currency()),
    ];

    $user_data = [
      'client_ip_address' => $_SERVER['REMOTE_ADDR'],
      'client_user_agent' => $_SERVER['HTTP_USER_AGENT'],
      'em' => hash('sha256', strtolower(trim($order->get_billing_email()))),
      'ph' => hash('sha256', $order->get_billing_phone()),
    ];

    $response = $this->send_event_to_meta(
      'Purchase',
      'PURCHASE_' . $order->get_id(),
      $custom_data,
      $user_data
    );

    if (! is_wp_error($response)) {
      $response_code = wp_remote_retrieve_response_code($response);
      if ($response_code >= 200 && $response_code < 300) {

        /* Update meta and save order object */
        $order->update_meta_data('_fb_capi_sent', 'yes');
        $order->save();
      }
    }
  }
}
