<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="<?php echo get_stylesheet_uri(); ?>">

  <title>Payment | <?php echo get_bloginfo(); ?></title>
</head>

<body>
  <style>
    #zippy_antom {
      width: 100%;
      min-height: 500px;
      height: calc(-160px + 100svh);
      margin-top: 80px;
    }

    #zippy_antom_loader {
      position: absolute;
      width: 100%;
      height: 100%;
      z-index: 10;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      display: flex;
      align-items: center;
      justify-content: center;
      transition: opacity 195mscubic-bezier 0.4, 0, 0.2, 1;
      visibility: hidden;
      opacity: 0;
    }

    #zippy_antom_loader.show-loading {
      opacity: 1;
      visibility: visible;
    }

    #zippy_antom_loader::before {
      content: "";
      position: fixed;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.2);
      opacity: 0.2;
    }

    #zippy_antom_loader .spinner {
      border: 2px solid rgba(0, 0, 0, 0.1);
      border-left-color: #000;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    #antom_error {
      display: none;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      margin-top: 15%;
      position: absolute;
      left: 50%;
      top: 30%;
      transform: translate(-50%, -50%);
    }

    #antom_error.show-error {
      display: flex;
    }

    #antom_error .message_wrapper {
      flex-direction: column;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    #antom_error .message_wrapper .message {
      font-size: 16px;
      font-weight: bold;
      color: #666;
      text-align: center;
    }

    #antom_error .back_to_checkout {
      margin-top: 25px;
      outline: none;
      background-color: #04aa6d;
      width: auto;
      min-width: 250px;
      padding: 8px 12px;
      text-align: center;
      border-radius: 8px;
      border: none;
      text-align: center;
    }

    #antom_error .back_to_checkout:hover {
      background: #059862;
    }

    #antom_error .back_to_checkout a {
      color: #fff;
      font-size: 18px;
      text-decoration: none;
    }

    #zippy_antom_notification {
      display: none;
      position: fixed;
      top: calc(-88px + 100svh);
      width: 100%;
      height: auto;
    }

    body {
      padding: 0;
      margin: 0;
    }

    #antom_header_payment_page,
    #antom_footer_payment_page {
      background-color: #fff;
      position: fixed;
      width: 100%;
      z-index: 999;
      padding: 25px 0px;
      display: flex;
      justify-content: center;
    }

    #antom_header_payment_page {
      top: 0px;
      box-shadow: rgba(3, 13, 32, 0.12) 0px 2px 4px 0px;
    }

    #antom_header_payment_page span {
      font-size: 22px;
      font-weight: bold;
    }

    #antom_footer_payment_page {
      bottom: 0px;
      box-shadow: rgba(3, 13, 32, 0.12) 0px -2px 4px 0px;
      display: flex;
      justify-items: center;
      flex-direction: column;
    }

    #antom_footer_payment_page.pending-page span {
      font-size: 16px;
      color: #555;
      line-height: 18px;
    }

    #antom_footer_payment_page span {
      font-size: 14px;
      text-align: center;
      color: #7c89a3;
    }

    #antom_footer_payment_page .bottom {
      text-align: center;
    }

    #antom_footer_payment_page .bottom a {
      color: #df2e30;
      text-decoration: underline;
    }
  </style>
  <header id="antom_header_payment_page">
    <span>Payment | <?php echo get_bloginfo(); ?></span>
  </header>
  <div id="zippy_antom_loader">
    <div class="spinner"></div>
  </div>
  <div id="antom_error">
    <div class="message_wrapper">
      <span class="message">We cannot process the payment at the moment.</span>

      <span class="message"> Please try again later.</span>
    </div>

    <button class="back_to_checkout">
      <a href="/checkout">Back to checkout</a>
    </button>
  </div>
  <div id="zippy_antom"></div>
  <?php
  // Make sure WooCommerce is initialized
  if (!is_admin() && WC()->session && !WC()->session->has_session()) {
    WC()->session->set_customer_session_cookie(true);
  }

  // Retrieve the stored order ID
  $order_id = WC()->session->get('antom_order_id');

  if (function_exists('WC') && empty($order_id)) {
    $customer_orders = wc_get_orders([
      'limit'   => 1,
      'orderby' => 'date',
      'order'   => 'DESC',
      'customer_id' => get_current_user_id()
    ]);

    if (!empty($customer_orders)) {
      $order_id = $customer_orders[0]->get_id();
    }
  }
  ?>

  <div>
    <input id="antom_order_id_pending" hidden type="text" value="<?php echo $order_id; ?>">
  </div>
  <footer id="antom_footer_payment_page" class="pending-page">
    <span class="">The payment has been received and is being processed</span>
    <div class="bottom">
      <span style="line-height: 1;">Please do not refresh or close your tab browser</span>
    </div>
  </footer>
  <script src="/wp-content/plugins/zippy-pay/includes/assets/dist/js/web.min.js?ver=<?php echo time(); ?>"></script>
</body>

</html>
