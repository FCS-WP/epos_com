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
    $result = $this->service->get_cart_count_payload();
    return $this->to_response($result);
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
