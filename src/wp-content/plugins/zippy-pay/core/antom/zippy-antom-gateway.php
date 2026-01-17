<?php

namespace ZIPPY_Pay\Core\Antom;

use WC_Payment_Gateway;
use WC_Order;
use ZIPPY_Pay\Core\ZIPPY_Pay_Core;
use ZIPPY_Pay\Src\Antom\ZIPPY_Antom_Scheduler;
use ZIPPY_Pay\Src\Logs\ZIPPY_Pay_Logger;

defined('ABSPATH') || exit;

class ZIPPY_Antom_Gateway extends WC_Payment_Gateway
{
	const PAYMENT_STATUS_SUCCESS = 'SUCCESS';

	public function __construct()
	{
		$this->id           = PAYMENT_ANTOM_ID;
		$this->method_title = __(PAYMENT_ANTOM_NAME, PREFIX . '_zippy_payment');
		$this->icon  =  ZIPPY_PAY_DIR_URL . 'includes/assets/icons/antom.svg';

		$this->has_fields   = true;

		$this->init_form_fields();
		$this->init_settings();

		$this->title = PAYMENT_ANTOM_NAME;
		$this->enabled = $this->get_option('enabled');

		add_action('woocommerce_receipt_' . $this->id, [$this, 'receipt_page']);
		add_action('woocommerce_api_wc_zippy_antom_redirect', [$this, 'handle_redirect']);
	}

	public function init_form_fields()
	{
		$this->form_fields = [
			'enabled' => [
				'title'   => __('Enable ' . PAYMENT_ANTOM_NAME, PREFIX . '_zippy_payment'),
				'type'    => 'checkbox',
				'label'   => __('Enable ' . PAYMENT_ANTOM_NAME, PREFIX . '_zippy_payment'),
				'default' => 'no'
			],
		];
	}

	public function payment_fields()
	{
		echo ZIPPY_Pay_Core::get_template('payment-section.php', [
			'is_active' => $this->is_gateway_configured(),
		], dirname(__FILE__), '/templates');
	}

	public function process_payment($order_id)
	{
		$order = wc_get_order($order_id);

		if (!$order) {
			return $this->handle_payment_failed();
		}

		return $this->handle_do_payment($order);
	}

	private function handle_do_payment($order)
	{
		$order_id = $order->get_id();

		$transaction = get_post_meta($order_id, 'zippy_antom_transaction', true);

		if (!empty($transaction) && isset($transaction->paymentStatus)) {
			return $this->check_order_status($order_id);
		}

		return $this->handle_payment_redirect($order);
	}

	private function handle_payment_redirect($order)
	{
		return [
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url(true)
		];
	}

	public function receipt_page($order_id)
	{
		echo ZIPPY_Pay_Core::get_template('payment-page.php', [
			'order_id' => $order_id,
		], dirname(__FILE__), '/templates');

		// Ensure WooCommerce session is started

		if (!is_admin() && WC()->session && !WC()->session->has_session()) {
			WC()->session->set_customer_session_cookie(true);
		}

		// Store order ID in session
		WC()->session->set('antom_order_id', $order_id);

		$scheduler = new ZIPPY_Antom_Scheduler();
		$scheduler->schedule_order_processing($order_id);

		wp_safe_redirect(site_url('/antom-payment'));
		exit;
	}

	public function handle_redirect()
	{
		$order_id = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;

		if (!$order_id) {
			wp_send_json_error(['message' => 'Invalid order ID']);
		}

		$order = wc_get_order($order_id);

		if (!$order) {
			wp_send_json_error(['message' => 'Order not found']);
		}

		$transaction_status = get_post_meta($order_id, 'zippy_antom_transaction', true);

		$is_payment_successful = !empty($transaction_status) && isset($transaction_status->paymentStatus) && $transaction_status->paymentStatus === self::PAYMENT_STATUS_SUCCESS;

		if (!$is_payment_successful) {
			wp_send_json([
				'status'  => 'success',
				'message' => 'Redirecting to payment page',
				'data' => $transaction_status,
				'redirect_url' => $order->get_checkout_payment_url()
			]);
		}

		wp_send_json([
			'status'  => 'success',
			'message' => 'Redirecting to payment page',
			'data' => $transaction_status,
			'redirect_url' => $this->get_return_url($order)
		]);
	}

	private function check_order_status($order_id)
	{
		$order = wc_get_order($order_id);
		if (!$order) return;

		$status = $this->get_transaction_status($order_id);

		if ($status === self::PAYMENT_STATUS_SUCCESS) {
			$this->payment_complete($order);
			wp_safe_redirect($this->get_return_url($order));
			exit;
		}

		wp_safe_redirect($order->get_checkout_payment_url());
		exit;
	}

	private function get_transaction_status($order_id)
	{
		for ($i = 0; $i < 3; $i++) {
			if ($i > 0) {
				sleep(5);
			}

			$transaction = get_post_meta($order_id, 'zippy_antom_transaction', true);
			ZIPPY_Pay_Logger::log_checkout("Transaction log: $order_id", $transaction);

			if (!empty($transaction) && isset($transaction->paymentStatus) && $transaction->paymentStatus === self::PAYMENT_STATUS_SUCCESS) {
				return self::PAYMENT_STATUS_SUCCESS;
			}
		}

		return false;
	}

	private function handle_payment_failed()
	{
		$this->add_notice();
		return false;
	}

	private function payment_complete($order)
	{
		$order->add_order_note(sprintf(__('Payment completed via ' . PAYMENT_ANTOM_NAME, PREFIX . '_zippy_payment')));
		$order->payment_complete();
	}

	private function is_gateway_configured()
	{
		return true;
	}

	private function add_notice()
	{
		wc_add_notice(__('Something went wrong with the payment. Please try again using another payment method.', PREFIX . '_zippy_payment'), 'error');
	}
}
