<?php

namespace ZIPPY_Pay\Src\Antom;

use ZIPPY_Pay\Src\Logs\ZIPPY_Pay_Logger;

class ZIPPY_Antom_Scheduler
{

  const HOOK_NAME = 'zippy_check_antom_payment_task';
  const MAX_RETRIES = 40;
  const RETRY_INTERVAL = 10; // Retry every 10 seconds

  /**
   * Schedule an event to process an order with retries
   */
  public static function schedule_order_processing($order_id, $attempt = 1)
  {
    if (!wp_next_scheduled(self::HOOK_NAME, [$order_id])) {

      wp_schedule_event(time(), 'zippy_antom_every_minute', 'zippy_check_antom_payment_task', [$order_id]);
    }
  }

  /**
   * Process the scheduled order, retrying if not found
   */
  public static function process_order($order_id)
  {

    // Retry if max retries not reached
    $attempts = (int) get_post_meta($order_id, '_zippy_attempts', true);

    if ($attempts >= self::MAX_RETRIES) {
      ZIPPY_Pay_Logger::log_checkout("Job max retries: $attempts time", $order_id);
      wp_clear_scheduled_hook(self::HOOK_NAME, [$order_id]);
    }

    $api = new ZIPPY_Antom_Api($order_id);

    $response = $api->checkPaymentTransactionApi();


    if (isset($response) && $response['data']->paymentStatus === 'SUCCESS') {

      update_post_meta($order_id, 'zippy_antom_transaction', $response['data']);

      $order = wc_get_order($order_id);
      $order->add_order_note(sprintf(__('Payment completed via ' . PAYMENT_ANTOM_NAME, PREFIX . '_zippy_payment')));
      $order->payment_complete();

      // Stop further scheduling since payment is complete
      ZIPPY_Pay_Logger::log_checkout("Payment SUCCESS for order_id: $order_id. Stopping background job.", $response['data']);

      wp_clear_scheduled_hook(self::HOOK_NAME, [$order_id]); // Stop cron

    } else {

      $attempts++;

      update_post_meta($order_id, '_zippy_attempts', $attempts);

      // Retry if max retries not reached
      ZIPPY_Pay_Logger::log_checkout("Retrying payment check for order_id: $order_id. Attempt $attempts in 15 seconds", $order_id);
    }
  }
}
