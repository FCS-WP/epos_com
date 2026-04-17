<?php

if (! defined('ABSPATH')) {
  exit;
}

class Webflow_CORS
{
  const ALLOWED_ORIGINS_FILTER = 'bluetap_webflow_allowed_origins';
  const COUNT_ROUTE = '/bluetap/v1/cart/count';
  const DEFAULT_ALLOWED_ORIGINS = array(
    'https://epos-staging.webflow.io',
  );

  public function __construct()
  {
    add_action('init', array($this, 'maybe_handle_preflight'), 0);
    add_filter('rest_pre_serve_request', array($this, 'add_rest_cors_headers'), 10, 4);
    add_filter('rest_request_before_callbacks', array($this, 'enforce_origin_policy'), 10, 3);
    add_filter('woocommerce_set_cookie_options', array($this, 'set_woocommerce_cookie_options_for_webflow'), 10, 3);
  }

  /**
   * Ensure WooCommerce cart/session cookies are cross-site compatible for Webflow requests.
   *
   * @param array $options
   * @param string $name
   * @param string $value
   * @return array
   */
  public function set_woocommerce_cookie_options_for_webflow($options, $name, $value)
  {
    if (! is_ssl()) {
      return $options;
    }

    $cookie_name = (string) $name;
    $is_wc_cookie = (
      0 === strpos($cookie_name, 'wp_woocommerce_session_') ||
      0 === strpos($cookie_name, 'woocommerce_')
    );

    if (! $is_wc_cookie) {
      return $options;
    }

    $options['secure'] = true;
    $options['samesite'] = 'None';

    return $options;
  }

  /**
   * Handle preflight requests for bluetap REST routes.
   *
   * @return void
   */
  public function maybe_handle_preflight()
  {
    $request_method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper((string) $_SERVER['REQUEST_METHOD']) : '';
    if ('OPTIONS' !== $request_method) {
      return;
    }

    if (! $this->is_bluetap_rest_request_uri()) {
      return;
    }

    $origin = get_http_origin();
    if (! $origin) {
      wp_send_json(
        array(
          'success' => false,
          'code'    => 'bluetap_cors_missing_origin',
          'message' => 'Origin header is required.',
        ),
        403
      );
    }

    if (! $this->is_origin_allowed($origin)) {
      wp_send_json(
        array(
          'success' => false,
          'code'    => 'bluetap_cors_forbidden',
          'message' => 'Origin is not allowed.',
        ),
        403
      );
    }

    $this->send_cors_headers($origin);
    status_header(204);
    exit;
  }

  /**
   * Add CORS headers for bluetap REST responses.
   *
   * @param bool $served
   * @param mixed $result
   * @param WP_REST_Request $request
   * @param WP_REST_Server $server
   * @return bool
   */
  public function add_rest_cors_headers($served, $result, $request, $server)
  {
    if (! $request instanceof WP_REST_Request) {
      return $served;
    }

    if (! $this->is_bluetap_route($request->get_route())) {
      return $served;
    }

    $origin = get_http_origin();
    if (! $origin || ! $this->is_origin_allowed($origin)) {
      return $served;
    }

    $this->send_cors_headers($origin);

    return $served;
  }

  /**
   * Reject non-approved origins for bluetap routes.
   *
   * @param mixed $response
   * @param array $handler
   * @param WP_REST_Request $request
   * @return mixed
   */
  public function enforce_origin_policy($response, $handler, $request)
  {
    if (! $request instanceof WP_REST_Request) {
      return $response;
    }

    if (! $this->is_bluetap_route($request->get_route())) {
      return $response;
    }

    $origin = get_http_origin();
    if (! $origin) {
      return new WP_Error(
        'bluetap_cors_missing_origin',
        'Origin header is required.',
        array('status' => 403)
      );
    }

    if ($this->is_origin_allowed($origin)) {
      return $response;
    }

    return new WP_Error(
      'bluetap_cors_forbidden',
      'Origin is not allowed.',
      array('status' => 403)
    );
  }

  /**
   * @return array
   */
  protected function get_allowed_origins()
  {
    $origins = self::DEFAULT_ALLOWED_ORIGINS;

    if (defined('BLUETAP_WEBFLOW_ALLOWED_ORIGINS')) {
      $configured = constant('BLUETAP_WEBFLOW_ALLOWED_ORIGINS');

      if (is_string($configured)) {
        $origins = array_merge($origins, explode(',', $configured));
      } elseif (is_array($configured)) {
        $origins = array_merge($origins, $configured);
      }
    }

    $origins = apply_filters(self::ALLOWED_ORIGINS_FILTER, $origins);
    if (! is_array($origins)) {
      return array();
    }

    $normalized = array();

    foreach ($origins as $origin) {
      if (! is_string($origin)) {
        continue;
      }

      $clean_origin = $this->normalize_origin($origin);
      if ('' !== $clean_origin) {
        $normalized[] = $clean_origin;
      }
    }

    return array_values(array_unique($normalized));
  }

  /**
   * @param string $origin
   * @return bool
   */
  protected function is_origin_allowed($origin)
  {
    if (! is_string($origin) || '' === trim($origin)) {
      return false;
    }

    $normalized_origin = $this->normalize_origin($origin);
    return in_array($normalized_origin, $this->get_allowed_origins(), true);
  }

  /**
   * @param string $origin
   * @return string
   */
  protected function normalize_origin($origin)
  {
    $origin = trim((string) $origin);
    if ('' === $origin) {
      return '';
    }

    $parts = wp_parse_url($origin);
    if (! is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
      return '';
    }

    $normalized = strtolower($parts['scheme']) . '://' . strtolower($parts['host']);

    if (! empty($parts['port'])) {
      $normalized .= ':' . (int) $parts['port'];
    }

    return $normalized;
  }

  /**
   * @param string $route
   * @return bool
   */
  protected function is_bluetap_route($route)
  {
    return is_string($route) && self::COUNT_ROUTE === $route;
  }

  /**
   * @return bool
   */
  protected function is_bluetap_rest_request_uri()
  {
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';

    if ('' === $uri) {
      return false;
    }

    if (false !== strpos($uri, '/wp-json' . self::COUNT_ROUTE)) {
      return true;
    }

    return false !== strpos($uri, 'rest_route=' . self::COUNT_ROUTE);
  }

  /**
   * @param string $origin
   * @return void
   */
  protected function send_cors_headers($origin)
  {
    $normalized_origin = $this->normalize_origin($origin);
    if ('' === $normalized_origin) {
      return;
    }

    header('Access-Control-Allow-Origin: ' . $normalized_origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-WP-Nonce');
    header('Access-Control-Max-Age: 600');
    header('Vary: Origin', false);
  }
}
