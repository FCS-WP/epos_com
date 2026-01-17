<?php

namespace ZIPPY_Pay\Src\Paynow;

use ZIPPY_Pay\Src\Logs\ZIPPY_Pay_Logger;

use WC_Order;

class ZIPPY_Paynow_Scheduler
{

  const HOOK_NAME = 'zippy_check_paynow_payment_task';
  const MAX_RETRIES = 40;
  const RETRY_INTERVAL = 10; // Retry every 10 seconds

  /**
   * Schedule an event to process an order with retries
   */
  public static function schedule_order_processing($order_id, $attempt = 1)
  {
    if (!wp_next_scheduled(self::HOOK_NAME, [$order_id])) {

      wp_schedule_event(time() + 15, 'zippy_paynow_every_minute', 'zippy_check_paynow_payment_task', [$order_id]);
    }
  }

  /**
   * Process the scheduled order, retrying if not found
   */
  public static function process_order($order_id)
  {

    // Retry if max retries not reached
    $attempts = (int) get_post_meta($order_id, '_zippy_paynow_attempts', true);

    if ($attempts >= self::MAX_RETRIES) {
      ZIPPY_Pay_Logger::log_checkout("Job max retries: $attempts time", $order_id);
      wp_clear_scheduled_hook(self::HOOK_NAME, [$order_id]);
    }

    $api = new ZIPPY_Paynow_Api($order_id);

    $merchant_id = get_option(PREFIX . '_merchant_id');

    $order = new WC_Order($order_id);

    $amount = $order->get_total();

    $response = $api->checkStatusOrder($merchant_id, $order_id, $amount);

    if (isset($response) && $response->result->status === "completed") {

     	delete_option('zippy_paynow_redirect_object_' .	$order_id);

			$order->add_order_note(sprintf(__('Payment was complete via ' . PAYMENT_PAYNOW_NAME, PREFIX . '_zippy_payment')));

			$order->payment_complete();

      // Stop further scheduling since payment is complete
      ZIPPY_Pay_Logger::log_checkout("Payment SUCCESS for order_id: $order_id. Stopping background job.", $response['data']);

      wp_clear_scheduled_hook(self::HOOK_NAME, [$order_id]); // Stop cron

    } else {

      $attempts++;

      update_post_meta($order_id, '_zippy_paynow_attempts', $attempts);

      // Retry if max retries not reached
      ZIPPY_Pay_Logger::log_checkout("Retrying payment check for order_id: $order_id. Attempt $attempts in 15 seconds", $order_id);
    }
  }
}
