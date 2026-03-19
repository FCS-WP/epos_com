<?php
class GMT_Events
{
  public function __construct()
  {
    add_action('wp_footer', array($this, 'gtm_add_to_cart_event'));
  }

  public function gtm_add_to_cart_event()
  {
?>
    <script>
      jQuery(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button) {
        var product_id = $button.data('product_id');
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
          ecommerce: null
        });
        window.dataLayer.push({
          'event': 'add_to_cart',
          'eventCallback': function() {
            window.location.href = '/cart';
          },
          'eventTimeout': 2000,
          'ecommerce': {
            'currency': '<?php echo esc_js(get_woocommerce_currency()); ?>',
            'value': parseFloat($button.data('price')) || 0,
            'items': [{
              'item_id': String(product_id),
              'item_name': $button.attr('aria-label') || 'Product',
              'quantity': parseInt($button.data('quantity')) || 1
            }]
          }
        });
      });
    </script>
<?php
  }
}
