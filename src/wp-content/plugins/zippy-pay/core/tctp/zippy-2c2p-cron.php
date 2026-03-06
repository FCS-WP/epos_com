<?php

namespace ZIPPY_Pay\Core\TcTp;

use ZIPPY_Pay\Src\Logs\ZIPPY_Pay_Logger;

defined('ABSPATH') || exit;

class ZIPPY_2c2p_Cron
{
	private static $_instance = null;

	public static function get_instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct()
	{
		add_action('zippy_2c2p_retry_payment_check', [$this, 'retry_payment_check'], 10, 2);
	}

	/**
	 * Schedule a WP Cron retry to re-check payment status
	 * Retry delays: attempt 1 = 1 min, attempt 2 = 5 min, attempt 3 = 15 min
	 */
	public function schedule_retry_check($order_id, $attempt)
	{
		$max_retries = 3;
		if ($attempt > $max_retries) return;

		$delays = [1 => 60, 2 => 300, 3 => 900];
		$delay = $delays[$attempt] ?? 60;

		wp_clear_scheduled_hook('zippy_2c2p_retry_payment_check', [$order_id, $attempt]);
		wp_schedule_single_event(time() + $delay, 'zippy_2c2p_retry_payment_check', [$order_id, $attempt]);

		ZIPPY_Pay_Logger::log_checkout("2C2P: Scheduled retry #$attempt for order $order_id in {$delay}s.", '');
	}

	/**
	 * WP Cron callback: re-check payment via paymentInquiry
	 */
	public function retry_payment_check($order_id, $attempt)
	{
		$order = wc_get_order($order_id);
		if (!$order) return;

		if ($order->is_paid()) {
			ZIPPY_Pay_Logger::log_checkout("2C2P Retry #$attempt: Order $order_id already paid. Skipping.", '');
			return;
		}

		$gateway = $this->get_gateway();
		if (!$gateway) {
			ZIPPY_Pay_Logger::log_checkout("2C2P Retry #$attempt: Gateway not available. Skipping.", '');
			return;
		}

		$resp_code = $gateway->get_payment_status($order_id);
		ZIPPY_Pay_Logger::log_checkout("2C2P Retry #$attempt: Order $order_id paymentInquiry returned $resp_code.", '');

		if ($resp_code === '0000') {
			$gateway->payment_complete($order);
			ZIPPY_Pay_Logger::log_checkout("2C2P Retry #$attempt: Order $order_id marked as paid.", '');
			return;
		}

		$max_retries = 3;
		if ($attempt < $max_retries) {
			$this->schedule_retry_check($order_id, $attempt + 1);
		} else {
			if (!$order->has_status('failed')) {
				$order->update_status('failed', __('2C2P: Payment not confirmed after ' . $max_retries . ' retries. Last code: ', 'zippy') . $resp_code);
			}
			ZIPPY_Pay_Logger::log_checkout("2C2P Retry: All retries exhausted for order $order_id. Marked as failed.", '');
		}
	}

	/**
	 * Get the 2C2P gateway instance from WooCommerce
	 */
	private function get_gateway()
	{
		$gateways = WC()->payment_gateways()->get_available_payment_gateways();
		return isset($gateways[PAYMENT_2C2P_ID]) ? $gateways[PAYMENT_2C2P_ID] : null;
	}
}
