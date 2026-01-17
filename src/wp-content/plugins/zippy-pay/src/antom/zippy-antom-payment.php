<?php

namespace ZIPPY_Pay\Src\Antom;

use ZIPPY_Pay\Src\Antom\ZIPPY_Antom_Api;
use ZIPPY_Pay\Src\Logs\ZIPPY_Pay_Logger;
use WP_REST_Response;
use WP_Error;

defined('ABSPATH') || exit;

class ZIPPY_Antom_Payment
{
  /**
   * Build Payment Session
   *
   * @param ZIPPY_Antom_Payment $order_id
   */

  public static function createPaymentSession($request)
  {
    $order_id = $request->get_param('order_id');

    if (!is_numeric($order_id) || empty($order_id)) {
      return new WP_Error('invalid_order_id', 'Order ID must be a number', array('status' => 400));
    }

    $api = new ZIPPY_Antom_Api($order_id);

    $response = $api->createPaymentSessionApi();

    if (!($response['status'])) {
      return new WP_Error('invalid_order_id', 'Invalid Order', array('status' => 400));
    }

    //stored paymentRequestId
    if (!empty($response['data']->paymentRequestId)) {
      update_post_meta($order_id, 'paymentRequestId', $response['data']->paymentRequestId);
    }

    return new WP_REST_Response(['data' => $response], 200);
  }

  public static function pollPaymentTransaction($request)
  {
    $order_id = $request->get_param('order_id');


    $transaction_status = get_post_meta($order_id, 'zippy_antom_transaction', true);


    return new WP_REST_Response(['data' => $transaction_status], 200);
  }
}
