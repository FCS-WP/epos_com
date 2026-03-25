<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see              https://docs.woocommerce.com/document/template-structure/
 * @package          WooCommerce\Templates
 * @version          7.9.0
 * @flatsome-version 3.19.4
 */

defined( 'ABSPATH' ) || exit;

$row_classes     = array();
$main_classes    = array();
$sidebar_classes = array();
$auto_refresh    = get_theme_mod( 'cart_auto_refresh' );
$row_classes[]   = 'row-large';
$row_classes[]   = 'row-divided';

if ( $auto_refresh ) {
	$main_classes[] = 'cart-auto-refresh';
}

$row_classes     = implode( ' ', $row_classes );
$main_classes    = implode( ' ', $main_classes );
$sidebar_classes = implode( ' ', $sidebar_classes );
?>

<div class="woocommerce row cart-section <?php echo $row_classes; ?>">
	<!-- Left column -->
	<div class="left-col col large-8 pb-0 <?php echo $main_classes; ?>">
		<?php wc_print_notices(); ?>
		<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
		<div class="cart-wrapper sm-touch-scroll">

			<?php do_action( 'woocommerce_before_cart_table' ); ?>

			<div class="cart-title">
				<h1>Your Cart</h1>
				<span><?php echo WC()->cart->get_cart_contents_count(); ?> item(s)</span>
			</div>
			<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
				<tbody>
					<?php do_action( 'woocommerce_before_cart_contents' ); ?>

					<?php
					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
						$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
						$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
						$product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );

						if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
							$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
							?>
							<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?> ">
								<td class="product-thumbnail">
									<?php
										$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

										if ( ! $product_permalink ) {
											echo $thumbnail; // PHPCS: XSS ok.
										} else {
											printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
										}
									?>
								</td>

								<td class="product-info" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
									<div class="product-name">
										<?php
											if ( ! $product_permalink ) {
												echo wp_kses_post( $product_name . '&nbsp;' );
											} else {
												echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
											}

											do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
											echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.

											if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
												echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
											}
										?>

										<div class="mobile-product-price">
											<?php
												echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
											?>
											<span class="mobile-product-price__qty"> x <?php echo $cart_item['quantity']; ?> </span>
										</div>
									</div>

									<div class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
										<?php
											if ( $_product->is_sold_individually() ) {
												$min_quantity = 1;
												$max_quantity = 1;
											} else {
												$min_quantity = 0;
												$max_quantity = $_product->get_max_purchase_quantity();
											}

											$product_quantity = woocommerce_quantity_input(
												array(
													'input_name'   => "cart[{$cart_item_key}][qty]",
													'input_value'  => $cart_item['quantity'],
													'max_value'    => $max_quantity,
													'min_value'    => $min_quantity,
													'product_name' => $product_name,
												),
												$_product,
												false
											);
											echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
										?>
									</div>
								</td>

								<td class="product-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>">
									<?php
										echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
									?>
								</td>
							</tr>
							<?php
						}
					}
					?>
					
					<tr>
						<td colspan="6" class="actions clear hidden">
							<?php do_action( 'woocommerce_cart_actions' ); ?>
							<button type="submit" class="button primary mt-0 pull-left small<?php if ( fl_woocommerce_version_check( '7.0.1' ) ) { echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); } ?>" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>
							<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
						</td>
					</tr>

					<?php do_action( 'woocommerce_cart_contents' ); ?>
					<?php do_action( 'woocommerce_after_cart_contents' ); ?>
				</tbody>
			</table>
			<?php do_action( 'woocommerce_after_cart_table' ); ?>
		</div>
		</form>
	</div>

	<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

	<!-- Right column -->
	<div class="right-col cart-collaterals large-4 col pb-0">
		<?php flatsome_sticky_column_open( 'cart_sticky_sidebar' ); ?>

		<div class="cart-sidebar-card">
			<h3 class="cart-sidebar-card__title">Cart Summary</h3>
			<?php do_action( 'woocommerce_cart_collaterals' ); ?>

			<?php if ( wc_coupons_enabled() ) : ?>
			<form class="cart-sidebar-card__coupon" method="post">
				<input type="text" name="coupon_code" id="coupon_code" placeholder="<?php esc_attr_e( 'Have a promo code?', 'woocommerce' ); ?>">
				<button type="submit" name="apply_coupon"><?php esc_html_e( 'Apply', 'woocommerce' ); ?></button>
				<?php do_action( 'woocommerce_cart_coupon' ); ?>
			</form>
			<?php endif; ?>

			<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="cart-checkout-btn">
				<?php esc_html_e( 'Continue To Checkout', 'woocommerce' ); ?>
			</a>

			<div class="cart-secure-badge">
				<img src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/assets/icons/safe.svg" alt="Secure checkout">
				<?php esc_html_e( 'Secure checkout powered by Antom', 'woocommerce' ); ?>
			</div>

		</div>

		<?php flatsome_sticky_column_close( 'cart_sticky_sidebar' ); ?>
	</div>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>