<?php

namespace ZIPPY_Pay\Src\Antom;

use WC_Order;
use ZIPPY_Pay\Core\ZIPPY_Pay_Core;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

class ZIPPY_Antom_Api
{
  private $order;
  private string $base_uri = "https://rest.zippy.sg";
  private string $errorMessage = 'We cannot process the payment at the moment. Please try again later.';

  public function __construct(int $order_id)
  {


    if ($this->is_valid_woocommerce_order($order_id)) {
      $this->order = wc_get_order($order_id);
    } else {
      error_log("Invalid order ID: $order_id");
    }
  }

  public function createPaymentSessionApi()
  {
    if (!$this->order) {
      return $this->formatResponse(false, 'Invalid order. Cannot create payment session.');
    }

    $order_id = $this->order->get_id();

    $merchant_id = get_option(PREFIX . '_merchant_id');

    $path = "/v1/payment/antom/ecommerce/session";
    $headers = array(
      'Content-Type' => 'application/json',
      'domain' => ZIPPY_Pay_Core::get_domain_name(),
      'organization' => $merchant_id
    );

    $client = new Client([
      'base_uri' => $this->base_uri,
      'timeout'  => 30,
      'headers'  => $headers,
      'verify' => false,
    ]);

    $data = $this->buildSessionPayload($order_id);
    try {
      $response = $client->post($path, ['json' => $data]);
      return $this->formatResponse(true, 'Get Payment Session Successfully', json_decode($response->getBody()));
    } catch (ConnectException $e) {
      return $this->formatResponse(false, 'Connection timed out. Please try again later.');
    } catch (RequestException $e) {
      $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;
      $errorMessage = $e->getResponse() ? (string) $e->getResponse()->getBody() : 'Unknown error occurred.';
      return $this->formatResponse(false, "Request failed: $errorMessage", [], $statusCode);
    }
  }

  public function checkPaymentTransactionApi()
  {

    $order_id = $this->order->get_id();
    $merchant_id = get_option(PREFIX . '_merchant_id');

    $path = "/v1/payment/antom/ecommerce/validate";

    $headers = array(
      'Content-Type' => 'application/json',
      'domain' => ZIPPY_Pay_Core::get_domain_name(),
      'organization' => $merchant_id
    );
    $client = new Client([
      'base_uri' => $this->base_uri,
      'timeout'  => 30,
      'headers'  => $headers,
      'verify' => false,
    ]);

    $data = $this->buildValidateTransactionPayload($order_id);

    try {
      $response = $client->post($path, ['json' => $data]);
      return $this->formatResponse(true, 'Get Payment Transaction Successfully', json_decode($response->getBody()));
    } catch (ConnectException $e) {
      return $this->formatResponse(false, 'Connection timed out. Please try again later.');
    } catch (RequestException $e) {
      $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;
      $errorMessage = $e->getResponse() ? (string) $e->getResponse()->getBody() : 'Unknown error occurred.';
      return $this->formatResponse(false, "Request failed: $errorMessage", [], $statusCode);
    }
  }

  public function checkPaymentTransactionCallback($order_id)
  {


    $order_id = $this->order->get_id();

    $path =  "/wp-json/zippy-pay/v1/antom/checkPaymentTransaction";

    $client = new Client([
      'base_uri' =>  home_url(),
      'timeout'  => 30,
      'verify' => false,
      'headers'  => ['Content-Type' => 'application/json'],
    ]);

    try {
      $response = $client->post($path,   ['query' => ['order_id' => $order_id]]);
      return $this->formatResponse(true, 'Trigger Background Job', json_decode($response->getBody()));
    } catch (ConnectException $e) {
      return $this->formatResponse(false, 'Connection timed out. Please try again later.');
    } catch (RequestException $e) {
      $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;
      $errorMessage = $e->getResponse() ? (string) $e->getResponse()->getBody() : 'Unknown error occurred.';
      return $this->formatResponse(false, "Request failed: $errorMessage", [], $statusCode);
    }
  }

  private function buildSessionPayload($order_id)
  {
    require_once ZIPPY_PAY_DIR_PATH . '../woocommerce/includes/wc-order-functions.php';

    $data = array(
      'OrderId' => $order_id,
      'CustomerId' => ZIPPY_Pay_Core::get_domain_name(),
      'OrderAmount' => $this->order->get_total(),
      'RedirectUrl' => str_replace('https:', 'http:', add_query_arg(array(
        'wc-api'      => 'zippy_antom_redirect',
        'order_id'     => $order_id
      ), home_url('/'))),
      'Currency' => $this->order->get_currency()
    );
    return $data;
  }

  private function buildValidateTransactionPayload($order_id)
  {
    $payment_requestID = get_post_meta($order_id, 'paymentRequestId');
    $data = array(
      'OrderId' => $order_id,
      'PaymentRequestId' => isset($payment_requestID[0]) ? $payment_requestID[0] : $payment_requestID,
    );
    return $data;
  }

  /**
   * Helper function to format API responses
   */
  private function formatResponse($status, $message, $data = [], $statusCode = 200)
  {
    return [
      'status' => $status,
      'message' => $message,
      'data' => $data,
      'code' => $statusCode
    ];
  }

  private function is_valid_woocommerce_order($order_id)
  {
    require_once ZIPPY_PAY_DIR_PATH . '../woocommerce/includes/wc-order-functions.php';

    $order = wc_get_order($order_id);

    return $order && $order->get_type() === 'shop_order';
  }
}
