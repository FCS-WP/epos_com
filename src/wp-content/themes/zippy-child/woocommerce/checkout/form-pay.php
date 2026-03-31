<?php
/**
 * Pay for order form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-pay.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.2.0
 */

defined( 'ABSPATH' ) || exit;

$totals = $order->get_order_item_totals(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
?>

<div class="order-receipt-page-title page-title">
	<div class="page-title-inner flex-row medium-flex-wrap">
	  <div class="checkout-page-title__inner flex-col flex-grow medium-text-center">
      <a class="back-to-checkout" href="<?php echo esc_url( home_url( '/my/checkout' ) ); ?>">
        <span><?php _e('Back to Checkout', 'flatsome'); ?></span>
      </a>
    </div>
  </div>
</div>

<div class="pre-order-details">
	<!-- left column -->
	<div class="pre-order-left">
		<div class="woo-thankyou-header">
			<h1 class="woo-thankyou-title">
				<?php esc_html_e( 'Thank you for your order!', 'woocommerce' ); ?>
			</h1>
			<p class="woo-thankyou-subtitle">
				<?php esc_html_e( 'We\'ve received your order and are processing it now.', 'woocommerce' ); ?>
			</p>
		</div>

		<div class="woo-thankyou-steps">
			<h2 class="woo-steps-title"><?php esc_html_e( 'What Happens Next', 'woocommerce' ); ?></h2>
			<div class="woo-steps-timeline">
				<div class="woo-step woo-step--active">
					<div class="woo-step__icon">
						<img src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/assets/icons/activating-step.gif" alt="Activating" />
					</div>
					<div class="woo-step__connector"></div>
					<div class="woo-step__content">
						<h3 class="woo-step__title"><?php esc_html_e( 'Pending Payment', 'woocommerce' ); ?></h3>
						<p class="woo-step__desc">
							<?php
							printf(
								esc_html__( 'Please pay to confirm your order. Your order number is %s.', 'woocommerce' ),
								'<strong>#' . esc_html( $order->get_order_number() ) . '</strong>'
							);
							?>
						</p>
					</div>
				</div>

				<div class="woo-step woo-step--pending">
					<div class="woo-step__icon"></div>
					<div class="woo-step__connector"></div>
					<div class="woo-step__content">
						<h3 class="woo-step__title"><?php esc_html_e( 'Device Activation', 'woocommerce' ); ?></h3>
						<p class="woo-step__desc">
							<?php esc_html_e( 'Our team will contact you ', 'woocommerce' ); ?>
							<strong><?php esc_html_e( 'within 2 working days', 'woocommerce' ); ?></strong>
							<?php esc_html_e( ' to guide you through activating your BlueTap device, so you\'re ready to accept payments as soon as it arrives.', 'woocommerce' ); ?>
						</p>
					</div>
				</div>

				<div class="woo-step woo-step--pending">
					<div class="woo-step__icon"></div>
					<div class="woo-step__connector"></div>
					<div class="woo-step__content">
						<h3 class="woo-step__title"><?php esc_html_e( 'Out for Delivery', 'woocommerce' ); ?></h3>
						<p class="woo-step__desc">
							<?php esc_html_e( 'Your device is out for delivery. It will be delivered to your address today.', 'woocommerce' ); ?>
						</p>
					</div>
				</div>

				<div class="woo-step woo-step--pending woo-step--last">
					<div class="woo-step__icon"></div>
					<div class="woo-step__content">
						<h3 class="woo-step__title"><?php esc_html_e( 'Delivered', 'woocommerce' ); ?></h3>
						<p class="woo-step__desc">
							<?php esc_html_e( 'Your device has been delivered. You\'re ready to start accepting payments.', 'woocommerce' ); ?>
						</p>
					</div>
				</div>

			</div>
		</div>
	</div>

	<!-- right column -->
	<div class="pre-order-right">
		<form id="order_review" method="post">
	
			<div class="woo-summary-card">
				<h2 class="woo-summary-card__title"><?php esc_html_e( 'Order Summary', 'woocommerce' ); ?></h2>
	
				<div class="woo-summary-items">
					<?php foreach ( $order->get_items() as $item_id => $item ) :
						if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) continue;
						$product  = $item->get_product();
						$qty      = $item->get_quantity();
						$subtotal = $order->get_formatted_line_subtotal( $item );
						$price    = $product ? wc_price( $product->get_price() ) : '';
					?>
					<div class="woo-summary-item">
						<div class="woo-summary-item__info">
							<span class="woo-summary-item__name">
								<?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) ); ?>
							</span>
							<span class="woo-summary-item__meta">
								<?php echo wp_kses_post( $price ); ?> &times; <?php echo esc_html( $qty ); ?>
							</span>
						</div>
						<span class="woo-summary-item__total"><?php echo wp_kses_post( $subtotal ); ?></span>
					</div>
					<?php endforeach; ?>
				</div>
	
				<div class="woo-summary-totals">
					<?php foreach ( $order->get_shipping_methods() as $shipping ) : ?>
					<div class="woo-summary-row">
						<span class="woo-summary-row__label"><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></span>
						<span class="woo-summary-row__value woo-summary-row__value--free">
							<?php
							$shipping_total = (float) $order->get_shipping_total();
							echo $shipping_total > 0
								? wp_kses_post( wc_price( $shipping_total ) )
								: esc_html__( 'Free', 'woocommerce' );
							?>
						</span>
					</div>
					<?php if ( $shipping->get_method_title() ) : ?>
					<div class="woo-summary-row woo-summary-row--sub">
						<span class="woo-summary-row__label"><?php echo esc_html( $shipping->get_method_title() ); ?></span>
					</div>
					<?php endif; ?>
					<?php endforeach; ?>
	
					<div class="woo-summary-row">
						<span class="woo-summary-row__label">
							<?php esc_html_e( 'Shipping', 'woocommerce' ); ?>
							<p class="woo-summary-row__sub"><?php esc_html_e( 'Ships in 3-5 business days', 'woocommerce' ); ?></p>
						</span>
						<span class="woo-summary-row__value woo-summary-row__value--free"><?php esc_html_e( 'Free', 'woocommerce' ); ?></span>
					</div>

					<?php foreach ( $order->get_coupons() as $coupons ) : ?>
						<div class="woo-summary-row cart-discount coupon-<?php echo esc_attr( sanitize_title( $coupons->get_code() ) ); ?>">
							<span class="woo-summary-row__label"><?php echo esc_html__( 'Coupon:', 'woocommerce' ) . ' ' . esc_html( strtolower( $coupons->get_code() ) ); ?></span>
							<span class="woo-summary-row__value">-<?php echo wp_kses_post( wc_price( $coupons->get_discount() ) ); ?></span>
						</div>
					<?php endforeach; ?>
	
					<?php foreach ( $order->get_tax_totals() as $code => $tax ) :
						$rate    = WC_Tax::_get_tax_rate( $tax->rate_id );
						$percent = $rate['tax_rate'] ?? '';
					?>
					<div class="woo-summary-row">
						<span class="woo-summary-row__label"><?php echo esc_html( $tax->label ) . ( $percent ? ' (' . (int) $percent . '%)' : '' ); ?></span>
						<span class="woo-summary-row__value"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
					</div>
					<?php endforeach; ?>
				</div>
	
				<div class="woo-summary-grand-total">
					<span class="woo-summary-grand-total__label"><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
					<span class="woo-summary-grand-total__value"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
				</div>

				<?php do_action( 'woocommerce_pay_order_before_payment' ); ?>
	
				<div id="payment">
					<?php if ( $order->needs_payment() ) : ?>
						<ul class="wc_payment_methods payment_methods methods <?php echo count( $available_gateways ) > 1 ? '' : 'hidden'; ?>">
							<?php
							if ( ! empty( $available_gateways ) ) {
								foreach ( $available_gateways as $gateway ) {
									wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
								}
							} else {
								echo '<li>';
								wc_print_notice( apply_filters( 'woocommerce_no_available_payment_methods_message', esc_html__( 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) ), 'notice' );
								echo '</li>';
							}
							?>
						</ul>
					<?php endif; ?>
		
					<div class="form-row">
						<input type="hidden" name="woocommerce_pay" value="1" />
						<?php wc_get_template( 'checkout/terms.php' ); ?>
						<?php do_action( 'woocommerce_pay_order_before_submit' ); ?>
						<?php echo apply_filters( 'woocommerce_pay_order_button_html', '<button type="submit" class="button alt' . esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ) . '" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); ?>
						<?php do_action( 'woocommerce_pay_order_after_submit' ); ?>
						<?php wp_nonce_field( 'woocommerce-pay', 'woocommerce-pay-nonce' ); ?>
					</div>
				</div>
			</div>

			<div class="woo-summary-card woo-delivery-card">
				<h2 class="woo-summary-card__title"><?php esc_html_e( 'Delivery Details', 'woocommerce' ); ?></h2>

				<div class="woo-delivery-row">
					<span class="woo-delivery-icon woo-delivery-icon--person">
						<img src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/assets/icons/User.png" alt="Person">
					</span>
					<div class="woo-delivery-info">
						<strong><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?></strong>
						<?php if ( $order->get_billing_email() ) : ?>
							<span><?php echo esc_html( $order->get_billing_email() ); ?></span>
						<?php endif; ?>
						<?php if ( $order->get_billing_phone() ) : ?>
							<span><?php echo esc_html( $order->get_billing_phone() ); ?></span>
						<?php endif; ?>
						<?php if ( $order->get_billing_country() ) : ?>
							<span><?php echo esc_html( WC()->countries->countries[ $order->get_billing_country() ] ?? $order->get_billing_country() ); ?></span>
						<?php endif; ?>
					</div>
				</div>

				<div class="woo-delivery-row">
					<span class="woo-delivery-icon woo-delivery-icon--location">
						<img src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/assets/icons/Location-Pin.png" alt="Location">
					</span>
					<div class="woo-delivery-info">
						<strong><?php echo esc_html( $order->get_meta('_billing_recipient') ); ?></strong>
						<?php if ( $order->get_billing_company() ) : ?>
							<span><?php echo esc_html( $order->get_billing_company() ); ?></span>
						<?php endif; ?>
						<?php if ( $order->get_billing_address_1() ) : ?>
							<span><?php echo esc_html( $order->get_billing_address_1() ); ?></span>
						<?php endif; ?>
						<?php if ( $order->get_billing_address_2() ) : ?>
							<span><?php echo esc_html( $order->get_billing_address_2() ); ?></span>
						<?php endif; ?>
						<?php if ( $order->get_billing_postcode() ) : ?>
							<span><?php echo esc_html( $order->get_billing_postcode() ); ?></span>
						<?php endif; ?>
						<?php if ( $order->get_billing_city() ) : ?>
							<span><?php echo esc_html( $order->get_billing_city() ); ?></span>
						<?php endif; ?>
						<?php if ( $order->get_meta('referral_code') ) : ?>
							<span><?php echo esc_html( $order->get_meta('referral_code') ); ?></span>
						<?php endif; ?>
					</div>
				</div>

			</div>
	
		</form>
	</div>
</div>
