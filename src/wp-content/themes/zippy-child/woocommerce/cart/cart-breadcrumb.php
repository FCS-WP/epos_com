<?php defined( 'ABSPATH' ) || exit; ?>

<div class="cart-breadcrumb">
    <a href="<?php echo esc_url( '/my/bluetap' ); ?>" class="cart-back-link">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        <?php esc_html_e( 'Back to Product', 'woocommerce' ); ?>
    </a>

    <nav class="cart-stepper hide-for-medium">
        <span class="cart-step is-active">
            <span class="cart-step__bubble">1</span>
            <span class="cart-step__label"><?php esc_html_e( 'My Cart', 'woocommerce' ); ?></span>
        </span>
        <span class="cart-step__connector"></span>
        <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="cart-step is-inactive">
            <span class="cart-step__bubble">2</span>
            <span class="cart-step__label"><?php esc_html_e( 'Checkout', 'woocommerce' ); ?></span>
        </a>
        <span class="cart-step__connector"></span>
        <span class="cart-step is-inactive">
            <span class="cart-step__bubble">3</span>
            <span class="cart-step__label"><?php esc_html_e( 'Payment', 'woocommerce' ); ?></span>
        </span>
        <span class="cart-step__connector"></span>
        <span class="cart-step is-inactive">
            <span class="cart-step__bubble">4</span>
            <span class="cart-step__label"><?php esc_html_e( 'Order Confirmation', 'woocommerce' ); ?></span>
        </span>
    </nav>
</div>