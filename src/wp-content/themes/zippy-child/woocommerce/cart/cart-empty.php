<?php
/**
 * Empty cart page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-empty.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see              https://docs.woocommerce.com/document/template-structure/
 * @package          WooCommerce\Templates
 * @version          7.0.1
 * @flatsome-version 3.16.2
 */

defined( 'ABSPATH' ) || exit; ?>
<div class="empty-cart-section text-center pt pb">
	<?php wc_get_template( 'cart/cart-breadcrumb.php' ); ?>

	<div class="empty-cart-content">
		<div class="empty-cart-message">
			<img src="/wp-content/uploads/2026/03/404.png" alt="Empty Cart">
			<?php do_action( 'woocommerce_cart_is_empty' ); ?>
		</div>
		<p class="return-to-shop">
			<a class="button wc-backward<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
				<?php echo esc_html( apply_filters( 'woocommerce_return_to_shop_text', __( 'View Products', 'woocommerce' ) ) ); ?>
			</a>
		</p>
	</div>
</div>
