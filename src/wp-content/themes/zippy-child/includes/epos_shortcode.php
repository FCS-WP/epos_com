<?php

/**
 * Shortcode: [ajax_add_to_cart id="123" qty="1" text="Add to Cart"]
 */
function flatsome_ajax_add_to_cart_shortcode($atts)
{

  if (!class_exists('WooCommerce')) return '';

  $atts = shortcode_atts(array(
    'id'   => '2174',
    'qty'  => 1,
    'text' => 'Add to Cart'
  ), $atts);

  if (empty($atts['id'])) return '';

  $product = wc_get_product($atts['id']);
  if (!$product || !$product->is_purchasable()) return '';

  return sprintf(
    '<a href="%s" 
            data-quantity="%s" 
            class="button primary product_type_%s add_to_cart_button ajax_add_to_cart bluetap-add-to-cart-button rounded-45 fw-400"
            data-product_id="%s"
            data-product_sku="%s"
            aria-label="%s"
            rel="nofollow">
            %s
        </a>',
    esc_url($product->add_to_cart_url()),
    esc_attr($atts['qty']),
    esc_attr($product->get_type()),
    esc_attr($product->get_id()),
    esc_attr($product->get_sku()),
    esc_attr($product->add_to_cart_description()),
    esc_html($atts['text'])
  );
}
add_shortcode('bluetab_addtocart_button', 'flatsome_ajax_add_to_cart_shortcode');
