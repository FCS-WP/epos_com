<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see              https://docs.woocommerce.com/document/template-structure/
 * @package          WooCommerce\Templates
 * @version          8.1.0
 * @flatsome-version 3.17.7
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="woocommerce-order woo-thankyou-wrapper">

	<?php if ( $order ) : ?>

		<?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<div class="woo-thankyou-failed">
				<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed">
					<?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?>
				</p>
				<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
					<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay">
						<?php esc_html_e( 'Pay', 'woocommerce' ); ?>
					</a>
					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button">
							<?php esc_html_e( 'My account', 'woocommerce' ); ?>
						</a>
					<?php endif; ?>
				</p>
			</div>

		<?php else : ?>

		<div class="woo-thankyou-layout">

			<div class="woo-thankyou-left">

				<div class="woo-thankyou-header">
					<h1 class="woo-thankyou-title">
						<?php esc_html_e( 'Order Confirmed', 'woocommerce' ); ?>
					</h1>
					<p class="woo-thankyou-subtitle">
						<?php esc_html_e( 'Your transaction was successful. We\'ve sent a detailed receipt to your email address.', 'woocommerce' ); ?>
					</p>
				</div>

				<div class="woo-thankyou-steps">
					<h2 class="woo-steps-title"><?php esc_html_e( 'What Happens Next', 'woocommerce' ); ?></h2>

					<div class="woo-steps-timeline">

						<div class="woo-step woo-step--done">
							<div class="woo-step__icon">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
									<polyline points="20 6 9 17 4 12"></polyline>
								</svg>
							</div>
							<div class="woo-step__connector"></div>
							<div class="woo-step__content">
								<h3 class="woo-step__title"><?php esc_html_e( 'Payment Received', 'woocommerce' ); ?></h3>
								<p class="woo-step__desc">
									<?php
									printf(
										esc_html__( 'We\'ve received your payment. Your order number is %s.', 'woocommerce' ),
										'<strong>#' . esc_html( $order->get_order_number() ) . '</strong>'
									);
									?>
								</p>
							</div>
						</div>

						<div class="woo-step woo-step--active">
							<div class="woo-step__icon">
								<img src="/wp-content/uploads/2026/03/activating-step.gif" alt="" />
							</div>
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

			<div class="woo-thankyou-right">

				<div class="woo-summary-card">
					<h2 class="woo-summary-card__title"><?php esc_html_e( 'Order Summary', 'woocommerce' ); ?></h2>

					<div class="woo-summary-items">
						<?php foreach ( $order->get_items() as $item_id => $item ) :
							$product  = $item->get_product();
							$qty      = $item->get_quantity();
							$subtotal = $order->get_formatted_line_subtotal( $item );
							$price    = $product ? wc_price( $product->get_price() ) : '';
						?>
						<div class="woo-summary-item">
							<div class="woo-summary-item__info">
								<span class="woo-summary-item__name"><?php echo esc_html( $item->get_name() ); ?></span>
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
							<span class="woo-summary-row__label"><?php echo esc_html_e( 'Shipping', 'woocommerce' ) . '<br><p>Ships in 3–5 business days</p>'; ?></span>
							<span class="woo-summary-row__value"><?php esc_html_e( 'Free', 'woocommerce' ); ?></span>
						</div>

						<?php foreach ( $order->get_tax_totals() as $code => $tax ) : 
							$rate = WC_Tax::_get_tax_rate( $tax->rate_id );
							$percent = $rate['tax_rate'] ?? '';
						?>
							
						<div class="woo-summary-row">
							<span class="woo-summary-row__label"><?php echo esc_html( $tax->label ) . ' (' . (int)$percent . '%)'; ?></span>
							<span class="woo-summary-row__value"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
						</div>
						<?php endforeach; ?>
					</div>

					<div class="woo-summary-grand-total">
						<span class="woo-summary-grand-total__label"><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
						<span class="woo-summary-grand-total__value"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
					</div>

				</div>

				<div class="woo-summary-card woo-delivery-card">
					<h2 class="woo-summary-card__title"><?php esc_html_e( 'Delivery Details', 'woocommerce' ); ?></h2>

					<div class="woo-delivery-row">
						<span class="woo-delivery-icon woo-delivery-icon--person">
							<img src="/wp-content/uploads/2026/03/User.png" alt="Person">
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
							<img src="/wp-content/uploads/2026/03/Location-Pin.png" alt="Location">
						</span>
						<div class="woo-delivery-info">
							<strong><?php echo esc_html( $order->get_meta('_billing_recipient') ); ?></strong>
							<?php if ( $order->get_shipping_company() ) : ?>
								<span><?php echo esc_html( $order->get_shipping_company() ); ?></span>
							<?php endif; ?>
							<?php if ( $order->get_shipping_address_1() ) : ?>
								<span><?php echo esc_html( $order->get_shipping_address_1() ); ?></span>
							<?php endif; ?>
							<?php if ( $order->get_shipping_address_2() ) : ?>
								<span><?php echo esc_html( $order->get_shipping_address_2() ); ?></span>
							<?php endif; ?>
							<?php if ( $order->get_shipping_postcode() ) : ?>
								<span><?php echo esc_html( $order->get_shipping_postcode() ); ?></span>
							<?php endif; ?>
							<?php if ( $order->get_shipping_city() ) : ?>
								<span><?php echo esc_html( $order->get_shipping_city() ); ?></span>
							<?php endif; ?>
						</div>
					</div>

				</div>

			</div>

		</div>

		<?php endif; ?>

	<?php else : ?>

		<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
			<?php echo esc_html( apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'woocommerce' ), null ) ); ?>
		</p>

	<?php endif; ?>

</div>