<?php

namespace ZIPPY_Pay\Core\TcTp;

use WC_Payment_Gateway;
use WC_Order;
use ZIPPY_Pay\Src\Logs\ZIPPY_Pay_Logger;
use ZIPPY_Pay\Core\ZIPPY_Pay_Core;


defined('ABSPATH') || exit;

class ZIPPY_2c2p_Gateway extends WC_Payment_Gateway
{

	private $merchant_id = '';
	private $secret_key = '';
	/**
	 * ZIPPY_2c2p_Gateway constructor.
	 */
	public function __construct()
	{

		$this->id           =  PAYMENT_2C2P_ID;
		$this->method_title = _(PAYMENT_2C2P_NAME, PREFIX . '_zippy_payment');
		$this->icon  =  ZIPPY_PAY_DIR_URL . 'includes/assets/icons/2c2p.svg';
		$this->has_fields   = true;

		$this->init_form_fields();
		$this->init_settings();

		$this->title = PAYMENT_2C2P_NAME;
		$this->method_description = __('Allow Payment via 2C2P', PREFIX . '_zippy_payment');
		$this->enabled         = $this->get_option('enabled');

		$this->merchant_id    = get_option(PAYMENT_2C2P_MERCHANT_ID, '');
		$this->secret_key    = get_option(PAYMENT_2C2P_SECRECT_KEY, '');

		add_action('woocommerce_receipt_' . $this->id, [$this, 'receipt_page']);
		add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
		add_action('woocommerce_api_zippy_2c2p_transaction', [$this, 'handle_callback']);
		add_action('woocommerce_api_zippy_2c2p_redirect', [$this, 'handle_redirect_page']);

		//Handle automatic payment status check
		add_action('wp_ajax_zippy_check_payment_status', [$this, 'ajax_check_payment_status']);
		add_action('wp_ajax_nopriv_zippy_check_payment_status', [$this, 'ajax_check_payment_status']);
	}

	/**
	 * Setup key form fields
	 *
	 */
	public function init_form_fields()
	{

		$this->form_fields = [
			'enabled'         => [
				'title'   => __('Enable ' . PAYMENT_2C2P_NAME, PREFIX . '_zippy_payment'),
				'type'    => 'checkbox',
				'label'   => __('Enable ' . PAYMENT_2C2P_NAME, PREFIX . '_zippy_payment'),
				'default' => 'no'
			],
		];
	}

	public function payment_fields()
	{
		echo ZIPPY_Pay_Core::get_template('message-fields.php', [
			'is_active' => 	[]
		], dirname(__FILE__), '/templates');
	}

	/**
	 * Woocomerce process payment
	 *
	 */
	public function process_payment($order_id)
	{

		$order              = new WC_Order($order_id);
		// Failed Payment
		if (empty($order)) $this->handle_payment_failed();

		// Request payment token from 2C2P
		$raw_response_jwt = $this->request_payment_token($order);

		if (!$raw_response_jwt) {
			wc_add_notice('Unable to initialize payment with 2C2P. Please try again.', 'error');
			return;
		}

		$decoded_response = $this->base64UrlDecode($raw_response_jwt);

		if (!$decoded_response || !isset($decoded_response['paymentToken'])) {
			wc_add_notice('Invalid response from 2C2P.', 'error');
			return;
		}

		$order->update_meta_data('_2c2p_payment_token', $raw_response_jwt);
		$order->save();

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url(true)
		);
	}

	public function receipt_page($order_id)
	{

		$order = wc_get_order($order_id);

		$response_data = $order->get_meta('_2c2p_payment_token');

		if (!$response_data) {
			echo "Payment session expired. Please return to checkout.";
			return;
		}

		// // Try to inquire payment status first
		// $status_code = $this->get_transaction_status($order_id);

		// if ($status_code == 2000) {
		// 	$this->payment_complete($order);
		// 	wp_safe_redirect($this->get_return_url($order));
		// 	exit;
		// }

		$decoded_response = $this->base64UrlDecode($response_data);

		$base_ui_url = PAYMENT_2C2P_BASE_UI;
		$payment_url = $base_ui_url . "/#/token/" . urlencode($decoded_response['paymentToken']);

		$version = time();
		wp_enqueue_script('2c2p-pgw-sdk', 'https://pgw-ui.2c2p.com/sdk/js/pgw-sdk-4.2.1.js', array(), '4.2.1', true);
		wp_enqueue_script('2c2p-dropin-init', ZIPPY_PAY_DIR_URL . 'includes/assets/js/dropin-init.js', array('2c2p-pgw-sdk'), $version, true);
		wp_enqueue_style('dropin-init', 'https://pgw-ui.2c2p.com/sdk/css/pgw-sdk-style-4.2.1.css', [], '4.2.1');

		wp_localize_script('2c2p-dropin-init', 'Zippy2C2P', array(
			'paymentUrl' => $payment_url,
			'returnUrl'  => $this->get_return_url($order),
			'ajaxUrl'   => admin_url('admin-ajax.php'),
			'orderId'   => $order_id
		));

		echo '<div id="pgw-ui-container" style="height: 600px; width: 100%;"></div>';
	}

	public function request_payment_token($order)
	{
		$payload = $this->get_2c2p_payload($order);

		ZIPPY_Pay_Logger::log_checkout("2C2P payload.", $payload);
		$invoice_no = (string) $order->get_id() . '_' . time();
		$order->update_meta_data('_2c2p_invoice_no', $invoice_no);
		$order->save();

		$jwt = $this->generate_jwt($payload);

		$response = wp_remote_post(PAYMENT_2C2P_ENDPOINT . '/paymentToken', array(
			'headers' => array('Content-Type' => 'application/json'),
			'body'    => json_encode(array('payload' => $jwt)),
			'timeout' => 45
		));

		if (is_wp_error($response)) return false;

		$body = json_decode(wp_remote_retrieve_body($response), true);
		return isset($body['payload']) ? $body['payload'] : false;
	}

	public function get_2c2p_payload($order)
	{
		return array(
			"merchantID"        => $this->merchant_id,
			"invoiceNo"         => (string) $order->get_id() . '_' . time(),
			"description"       => "Order #" . $order->get_id(),
			"amount"            => number_format($order->get_total(), 2, '.', ''),
			"currencyCode"      => $order->get_currency(),
			"frontendReturnUrl" => WC()->api_request_url('zippy_2c2p_redirect') . '?order_id=' . $order->get_id(),
			"backendReturnUrl"  => WC()->api_request_url('zippy_2c2p_transaction'),
			"nonceStr"          => wp_generate_password(12, false),
			"uiParams" => array(
				"userInfo" => array(
					"email" => $order->get_billing_email(),
					"name"  => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				)
			),
			"paymentChannel" => array("CC",    "TNG"),
		);
	}

	public function generate_jwt($payload)
	{
		$header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);

		$base64UrlHeader = $this->base64UrlEncode($header);
		$base64UrlPayload = $this->base64UrlEncode(json_encode($payload));

		$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret_key, true);
		$base64UrlSignature = $this->base64UrlEncode($signature);

		return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
	}

	public function base64UrlEncode($data)
	{
		return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
	}

	public function base64UrlDecode($jwt)
	{
		$token_parts = explode('.', $jwt);
		if (count($token_parts) !== 3) return false;

		$header = $token_parts[0];
		$payload = $token_parts[1];
		$signature_provided = $token_parts[2];

		// 1. Re-generate signature to verify
		$base64_url_signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(
			hash_hmac('sha256', "$header.$payload", $this->secret_key, true)
		));

		// 2. Check if signature matches
		if ($base64_url_signature !== $signature_provided) {
			error_log("2C2P Error: Invalid Signature detected!");
			return false;
		}

		// 3. Decode payload
		return json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);
	}
	/**
	 * Handle 2C2P IPN (Backend Callback)
	 * @return void
	 */
	public function handle_callback()
	{

		$jwt_payload = isset($_POST['payload']) ? $_POST['payload'] : '';


		if (empty($jwt_payload)) {
			$raw_body = file_get_contents('php://input');
			$decoded_body = json_decode($raw_body, true);
			$jwt_payload = isset($decoded_body['payload']) ? $decoded_body['payload'] : '';
		}

		if (empty($jwt_payload)) {
			ZIPPY_Pay_Logger::log_checkout("2C2P Callback Error: No payload found in POST or Raw Body.", '');
			exit;
		}

		$data = $this->base64UrlDecode($jwt_payload);


		$invoice_no = $data['invoiceNo'];
		$order_id_parts = explode('_', $invoice_no);
		$order_id = intval($order_id_parts[0]);

		$order = wc_get_order($order_id);
		if (!$order) {
			wp_send_json_error(['message' => 'Order not found']);
		}

		// // 2000: Success
		$resp_code = $data['respCode'];

		if ($resp_code == '0000') {
			if (!$order->is_paid()) {
				$this->payment_complete($order);
				ZIPPY_Pay_Logger::log_checkout("2C2P Success: Order $order_id marked as paid.", $data);
			}
		} elseif ($resp_code === '0001') {
			$order->update_status('on-hold', __('2C2P: Payment is pending/waiting.', 'zippy'));
		} else {
			$order->update_status('failed', __('2C2P: Payment failed. Code: ', 'zippy') . $resp_code);
		}

		// Respond to 2C2P
		status_header(200);
		echo "OK";
		exit;
	}

	public function payment_complete($order)
	{
		$order->payment_complete();
		$order->add_order_note(__('2C2P payment completed.', PREFIX . '_zippy_payment'));
	}

	/**
	 * Inquiry Transaction Status
	 */
	public function get_transaction_status($order_id)
	{
		$order = wc_get_order($order_id);
		if (!$order) return false;

		$payment_token = $order->get_meta('_2c2p_payment_token');

		$decoded_token = $this->base64UrlDecode($payment_token);

		$response = wp_remote_post(PAYMENT_2C2P_ENDPOINT . '/transactionStatus', array(
			'headers' => array('Content-Type' => 'application/json'),
			'body'    => json_encode(array('paymentToken' => $decoded_token['paymentToken'])),
			'timeout' => 30
		));

		if (is_wp_error($response)) return false;

		$body = json_decode(wp_remote_retrieve_body($response), true);
		ZIPPY_Pay_Logger::log_checkout("2C2P Redirect.", $body);

		if (isset($body['invoiceNo'])) {

			return isset($body['respCode']) ? $body['respCode'] : '9999';
		}

		return '9999';
	}

	public function get_payment_status($order_id)
	{
		$order = wc_get_order($order_id);
		if (!$order) return false;

		$payment_token = $order->get_meta('_2c2p_payment_token');

		$decoded_token = $this->base64UrlDecode($payment_token);

		$response = wp_remote_post(PAYMENT_2C2P_ENDPOINT . '/paymentInquiry', array(
			'headers' => array('Content-Type' => 'application/json'),
			'body'    => json_encode(array('paymentToken' => $decoded_token['paymentToken'])),
			'timeout' => 30
		));

		if (is_wp_error($response)) return false;

		$body = json_decode(wp_remote_retrieve_body($response), true);
		ZIPPY_Pay_Logger::log_checkout("2C2P Payment Inquiry.", $body);

		if (isset($body['invoiceNo'])) {

			return isset($body['respCode']) ? $body['respCode'] : '9999';
		}

		return '9999';
	}
	public function check_order_status($order_id)
	{
		$order = wc_get_order($order_id);
		if (!$order) return;

		$status_code = $this->get_transaction_status($order_id);

		if ($status_code == 2000) {
			//Payment Inquiry 

			$payed_status = $this->get_payment_status($order_id);

			if ($payed_status == '0000') { {
					if (!$order->is_paid()) {
						$this->payment_complete($order);
						wp_safe_redirect($this->get_return_url($order));
						exit;
					} else {
						wp_safe_redirect($this->get_return_url($order));
						exit;
					}
				}
			}
		} else {
			// Log the failure for debugging
			ZIPPY_Pay_Logger::log_checkout("Payment inquiry returned status:", $status_code);
			wp_safe_redirect($order->get_checkout_payment_url(true));
			exit;
		}
	}

	public function handle_redirect_page()
	{
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
		$order = wc_get_order($order_id);

		if (!$order_id) {
			wc_add_notice(__('Invalid Order.', 'zippy'), 'error');
			wp_safe_redirect(wc_get_checkout_url());
			exit;
		}

		if ($order && $order->has_status('pending')) {

			$resp_code = $this->get_transaction_status($order_id);

			if ($resp_code == 2000) {

				$payed_status = $this->get_payment_status($order_id);

				if ($payed_status == '0000') { {
						if (!$order->is_paid()) {
							$this->payment_complete($order);
							wp_safe_redirect($this->get_return_url($order));
							exit;
						} else {
							wp_safe_redirect($this->get_return_url($order));
							exit;
						}
					}
				}
			} else {
				$order->add_order_note(__('Inquiry on Redirect: Payment not completed yet. Code: ', 'zippy') . $resp_code);
				wp_safe_redirect($order->get_checkout_payment_url(true));
				exit;
			}
		}

		if ($order->is_paid()) {
			wp_safe_redirect($this->get_return_url($order));
			exit;
		}
	}

	public function ajax_check_payment_status()
	{
		$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
		$order = wc_get_order($order_id);

		if (!$order) {
			wp_send_json_error(['message' => 'Order not found']);
		}

		if ($order->is_paid()) {
			wp_send_json_success(['status' => 'paid', 'redirect' => $this->get_return_url($order)]);
		}

		$resp_code = $this->get_payment_status($order_id);

		if ($resp_code === '0000') {
			$this->payment_complete($order);
			wp_send_json_success(['status' => 'paid', 'redirect' => $this->get_return_url($order)]);
		}

		wp_send_json_success(['status' => 'pending']);
	}


	/**
	 * Handle do payment failed
	 *
	 * @return mixed
	 */

	private function handle_payment_failed()
	{

		$this->add_notice();
		return false;
	}



	/**
	 * Add notice when payment failed
	 *
	 * @return mixed
	 */

	private function add_notice()
	{
		return	wc_add_notice(__('Something went wrong with the payment. Please try again using another Credit / Debit Card.', PREFIX . '_zippy_payment'), 'error');
	}
}
