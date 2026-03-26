<?php
/**
 * Checkout Order Receipt Template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/order-receipt.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
?>

<div class="order-receipt">
  <div class="payment-method-details">
    <?php do_action( 'woocommerce_receipt_' . $order->get_payment_method(), $order->get_id() ); ?>
  </div>

  <div class="order-details">
    <h2 class="order-details-title">Order Summary</h2>
    <ul class="order-details-content">
      <li class="order">
        <p><?php esc_html_e( 'Order number:', 'woocommerce' ); ?></p>
        <strong><?php echo esc_html( $order->get_order_number() ); ?></strong>
      </li>
      <li class="date">
        <p><?php esc_html_e( 'Date:', 'woocommerce' ); ?></p>
        <strong><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></strong>
      </li>
      <?php if ( $order->get_payment_method_title() ) : ?>
        <li class="method">
          <p><?php esc_html_e( 'Payment method:', 'woocommerce' ); ?></p>
          <strong><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
        </li>
      <?php endif; ?>
      <li class="total">
        <p><?php esc_html_e( 'Total:', 'woocommerce' ); ?></p>
        <strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
      </li>
    </ul>
  </div>
</div>

<div class="clear"></div>