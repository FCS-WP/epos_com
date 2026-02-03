<?php
function footer_shortcode()
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path_trimmed = trim($path, '/');

    if (strpos($path_trimmed, 'my') === 0) {
        return do_shortcode('[block id="footer-malaysia"]');
    } else {
        return do_shortcode('[block id="footer"]');
    }
}
add_shortcode('custom_footer', 'footer_shortcode');



/**
 * Inject GTM Add to Cart script into wp_footer
 */
function ad_gtm_add_to_cart_script()
{
    if (! is_woocommerce()) return;
?>
    <script>
        jQuery(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button) {
            var product_id = $button.data('product_id');
            console.log(product_id);
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': 'add_to_cart',
                'ecommerce': {
                    'currency': '<?php echo get_woocommerce_currency(); ?>',
                    'value': $button.data('price') || 0, // Ensure price data is available on button
                    'items': [{
                        'item_id': product_id,
                        'item_name': $button.attr('aria-label') || 'Product',
                        'quantity': $button.data('quantity') || 1
                    }]
                }
            });
        });
    </script>
<?php
}
add_action('wp_footer', 'ad_gtm_add_to_cart_script');
