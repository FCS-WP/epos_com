<?php
/**
 * Landings — HubSpot form submission REST API.
 *
 * Endpoint: POST /wp-json/landings/v1/hubspot/form-submit
 *
 * Body (JSON):
 *   {
 *     "landing_slug":   "subscription",      // required
 *     "fields":         { "email": "...", "firstname": "..." },  // required
 *     "honeypot":       ""                   // optional, must be empty
 *   }
 *
 * Headers:
 *   X-WP-Nonce: <wp_create_nonce('wp_rest')>   // required, injected by loader.php
 *
 * The portal/form ids are NOT trusted from the request — they are read
 * from the landing's content.json on the server. Each landing controls
 * its own credentials via its own JSON file.
 *
 * Defenses:
 *   - WP nonce check (CSRF)
 *   - Origin header must match site host
 *   - Honeypot field must be empty
 *   - Per-IP rate limit (transient-backed, 10 submissions / 10 minutes)
 */

if (! defined('ABSPATH')) exit;

if (! class_exists('Landings_HubSpot_Form_API')) :

class Landings_HubSpot_Form_API
{
    const NAMESPACE  = 'landings/v1';
    const ROUTE      = '/hubspot/form-submit';
    const RATE_LIMIT = 10;          // requests
    const RATE_WINDOW = 600;        // seconds (10 minutes)

    /**
     * @var Landings_HubSpot_Form_Client
     */
    protected $client;

    public function __construct(Landings_HubSpot_Form_Client $client = null)
    {
        $this->client = $client instanceof Landings_HubSpot_Form_Client
            ? $client
            : new Landings_HubSpot_Form_Client();

        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes()
    {
        register_rest_route(self::NAMESPACE, self::ROUTE, array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle'),
            'permission_callback' => array($this, 'permission_check'),
        ));
    }

    /**
     * Permission gate. Combines:
     *   1. wp_rest nonce (sent via X-WP-Nonce header by the bridge)
     *   2. Origin header host matches our own host
     *
     * Both fail-closed: missing nonce or wrong origin → reject.
     *
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public function permission_check($request)
    {
        // Origin check.
        $origin = $request->get_header('origin');
        if ($origin && ! $this->origin_is_local($origin)) {
            return new WP_Error(
                'landings_forbidden_origin',
                'Origin not allowed.',
                array('status' => 403)
            );
        }

        // Nonce check (WP REST cookie nonce).
        $nonce = $request->get_header('x-wp-nonce');
        if (! $nonce || ! wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error(
                'landings_invalid_nonce',
                'Invalid or missing nonce.',
                array('status' => 401)
            );
        }

        return true;
    }

    /**
     * Main handler.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle($request)
    {
        $params = $request->get_json_params();
        if (! is_array($params)) $params = array();

        // Honeypot — bots fill any input they see; real users don't see this one.
        if (! empty($params['honeypot'])) {
            // Pretend success so bots don't learn this is a trap.
            return $this->respond(array('success' => true, 'message' => null), 200);
        }

        // Rate limit by IP.
        $rate_check = $this->rate_limit_check($request);
        if (is_wp_error($rate_check)) {
            return $this->respond_error($rate_check);
        }

        $landing_slug = isset($params['landing_slug']) ? sanitize_key($params['landing_slug']) : '';
        $fields       = isset($params['fields']) && is_array($params['fields']) ? $params['fields'] : array();

        if (! $landing_slug) {
            return $this->respond(array(
                'success' => false,
                'error'   => 'Missing landing_slug.',
            ), 400);
        }

        if (empty($fields)) {
            return $this->respond(array(
                'success' => false,
                'error'   => 'No fields submitted.',
            ), 400);
        }

        // Look up portal/form ids from the landing's content.json — single
        // source of truth, not trusted from the client.
        $config = $this->load_landing_form_config($landing_slug);
        if (is_wp_error($config)) {
            return $this->respond_error($config);
        }

        // Sanitize fields. We don't enforce a schema (each landing chooses
        // its own fields); just trim and cast scalars.
        $clean_fields = array();
        foreach ($fields as $name => $value) {
            $name = sanitize_key($name);
            if ($name === '' || $name === 'honeypot') continue;
            if (is_array($value)) {
                $value = array_map(function ($v) {
                    return is_scalar($v) ? sanitize_text_field((string) $v) : '';
                }, $value);
            } elseif (is_scalar($value)) {
                // Email gets stricter handling.
                if ($name === 'email') {
                    $value = sanitize_email((string) $value);
                } else {
                    $value = sanitize_text_field((string) $value);
                }
            } else {
                continue;
            }
            $clean_fields[$name] = $value;
        }

        if (empty($clean_fields)) {
            return $this->respond(array(
                'success' => false,
                'error'   => 'No valid fields submitted.',
            ), 400);
        }

        $context = array(
            'hutk'      => $this->read_hutk_cookie(),
            'pageUri'   => isset($params['page_uri']) ? esc_url_raw($params['page_uri']) : '',
            'pageName'  => isset($params['page_name']) ? sanitize_text_field((string) $params['page_name']) : '',
            'ipAddress' => $this->client_ip(),
        );

        $result = $this->client->submit(
            $config['portal_id'],
            $config['form_id'],
            $clean_fields,
            $context
        );

        $status = $result['success'] ? 200 : (int) ($result['status'] ?: 502);
        return $this->respond($result, $status);
    }

    /**
     * Read portal_id + form_id from a landing's content.json.
     * Not trusted from request → not spoofable.
     *
     * @param string $slug
     * @return array|WP_Error  ['portal_id' => '...', 'form_id' => '...']
     */
    protected function load_landing_form_config($slug)
    {
        $path = LANDINGS_DIR . '/' . $slug . '/content.json';
        if (! file_exists($path)) {
            return new WP_Error(
                'landings_unknown_landing',
                'Unknown landing.',
                array('status' => 404)
            );
        }

        $raw = file_get_contents($path);
        $data = json_decode($raw, true);
        if (! is_array($data)) {
            return new WP_Error(
                'landings_invalid_content',
                'Landing content invalid.',
                array('status' => 500)
            );
        }

        $form = isset($data['form']) && is_array($data['form']) ? $data['form'] : array();
        $portal_id = isset($form['hubspot_portal_id']) ? trim((string) $form['hubspot_portal_id']) : '';
        $form_id   = isset($form['hubspot_form_id'])   ? trim((string) $form['hubspot_form_id'])   : '';

        if (! $portal_id || ! $form_id) {
            return new WP_Error(
                'landings_form_not_configured',
                'HubSpot form not configured for this landing.',
                array('status' => 500)
            );
        }

        return array('portal_id' => $portal_id, 'form_id' => $form_id);
    }

    /**
     * Per-IP rate limit using a transient counter.
     *
     * @param WP_REST_Request $request
     * @return true|WP_Error
     */
    protected function rate_limit_check($request)
    {
        $ip  = $this->client_ip();
        $key = 'landings_form_rl_' . md5($ip);
        $count = (int) get_transient($key);

        if ($count >= self::RATE_LIMIT) {
            return new WP_Error(
                'landings_rate_limited',
                'Too many submissions. Please try again later.',
                array('status' => 429)
            );
        }

        set_transient($key, $count + 1, self::RATE_WINDOW);
        return true;
    }

    /**
     * Best-effort client IP, respecting common proxy headers.
     *
     * @return string
     */
    protected function client_ip()
    {
        $candidates = array('HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');
        foreach ($candidates as $key) {
            if (empty($_SERVER[$key])) continue;
            $list = explode(',', (string) $_SERVER[$key]);
            $ip = trim($list[0]);
            if ($ip) return $ip;
        }
        return '';
    }

    /**
     * HubSpot tracking cookie (set by HubSpot's own scripts if loaded; absent
     * if the landing doesn't load HubSpot's tracker, which is fine).
     *
     * @return string
     */
    protected function read_hutk_cookie()
    {
        return isset($_COOKIE['hubspotutk']) ? sanitize_text_field((string) $_COOKIE['hubspotutk']) : '';
    }

    /**
     * Compare an Origin header value to the site's own host.
     *
     * @param string $origin
     * @return bool
     */
    protected function origin_is_local($origin)
    {
        $origin_host = wp_parse_url($origin, PHP_URL_HOST);
        $site_host   = wp_parse_url(home_url(), PHP_URL_HOST);
        if (! $origin_host || ! $site_host) return false;
        return strtolower($origin_host) === strtolower($site_host);
    }

    /**
     * @param array $payload
     * @param int   $status
     * @return WP_REST_Response
     */
    protected function respond(array $payload, $status = 200)
    {
        $response = new WP_REST_Response($payload, $status);
        // Form responses are per-submission and must never be cached.
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        return $response;
    }

    /**
     * @param WP_Error $err
     * @return WP_REST_Response
     */
    protected function respond_error(WP_Error $err)
    {
        $code   = $err->get_error_code();
        $data   = $err->get_error_data($code);
        $status = is_array($data) && isset($data['status']) ? (int) $data['status'] : 400;

        return $this->respond(array(
            'success' => false,
            'code'    => $code,
            'error'   => $err->get_error_message($code),
        ), $status);
    }
}

new Landings_HubSpot_Form_API();

endif;
