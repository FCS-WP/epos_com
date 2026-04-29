<?php

if (! defined('ABSPATH')) {
  exit;
}

class Webflow_Cart_API extends WP_REST_Controller
{
  /**
   * @var string
   */
  protected $namespace = 'bluetap/v1';

  /**
   * @var Webflow_Cart_Service
   */
  protected $service;

  /**
   * @param Webflow_Cart_Service|null $service
   */
  public function __construct($service = null)
  {
    $this->service = $service instanceof Webflow_Cart_Service ? $service : new Webflow_Cart_Service();

    add_action('rest_api_init', array($this, 'register_routes'));
  }

  /**
   * Register Webflow bridge endpoints.
   *
   * @return void
   */
  public function register_routes()
  {
    register_rest_route(
      $this->namespace,
      '/cart/count',
      array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => array($this, 'get_cart_count'),
        'permission_callback' => array($this, 'get_public_permissions_check'),
      )
    );
  }

  /**
   * Public endpoint permission callback.
   *
   * @return bool
   */
  public function get_public_permissions_check()
  {
    return true;
  }

  /**
   * GET /cart/count
   *
   * @param WP_REST_Request $request
   * @return WP_REST_Response
   */
  public function get_cart_count($request)
  {
    // Fast path: when WC has no session cookie AND the cart-items cookie
    // is absent or zero, we know the cart is empty without booting WC/session.
    // Booting WC session + cart costs noticeably on every Webflow page view.
    if ($this->is_cart_definitely_empty()) {
      $result = array(
        'success'    => true,
        'cart_count' => 0,
        'cart_url'   => $this->service->get_cart_url(),
      );
      return $this->to_response($result);
    }

    $result = $this->service->get_cart_count_payload();
    return $this->to_response($result);
  }

  /**
   * Decide whether we can skip booting WC because the cart is obviously empty.
   *
   * WC sets `woocommerce_items_in_cart` to the item count on the cookie whenever
   * the cart is non-empty. It also sets a `wp_woocommerce_session_*` cookie once
   * a session exists. If neither is present we can safely return 0.
   *
   * @return bool
   */
  protected function is_cart_definitely_empty()
  {
    $items_cookie = isset($_COOKIE['woocommerce_items_in_cart']) ? (int) $_COOKIE['woocommerce_items_in_cart'] : 0;
    if ($items_cookie > 0) {
      return false;
    }

    foreach ($_COOKIE as $name => $value) {
      if (strpos($name, 'wp_woocommerce_session_') === 0) {
        return false;
      }
    }

    return true;
  }

  /**
   * Convert array|WP_Error to WP_REST_Response with proper status.
   *
   * @param mixed $result
   * @return WP_REST_Response
   */
  protected function to_response($result)
  {
    $response = null;

    if (is_wp_error($result)) {
      $codes        = $result->get_error_codes();
      $primary_code = ! empty($codes) ? reset($codes) : 'bluetap_error';
      $error_data   = $result->get_error_data($primary_code);
      $status       = is_array($error_data) && isset($error_data['status']) ? (int) $error_data['status'] : 400;

      $response = new WP_REST_Response(
        array(
          'success' => false,
          'code'    => $primary_code,
          'message' => $result->get_error_message($primary_code),
        ),
        $status
      );
    } else {
      $response = new WP_REST_Response($result, 200);
    }

    // Cart state is session-driven and should not be cached by browsers/CDNs.
    $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    $response->header('Pragma', 'no-cache');
    $response->header('Expires', 'Wed, 11 Jan 1984 05:00:00 GMT');

    return $response;
  }
}
