<?php

add_filter('flatsome_viewport_meta', function () {
    return '<meta name="viewport" content="width=device-width, initial-scale=1">';
});

function custom_optimize_is_frontend()
{
    return !is_admin();
}

function custom_optimize_child_asset_url($path)
{
    return trailingslashit(get_stylesheet_directory_uri()) . ltrim($path, '/');
}

function custom_optimize_dequeue_handles($type, $handles)
{
    foreach ($handles as $handle) {
        if ($type === 'style') {
            wp_dequeue_style($handle);
            wp_deregister_style($handle);
            continue;
        }

        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }
}

function custom_optimize_asset_handles($group)
{
    $handles = array(
        'async_styles' => array(
            'flatsome-style',
        ),
        'cart_async_styles' => array(
            'epos-style-css',
            'flatsome-main',
            'flatsome-shop',
            'select2',
            'woocommerce-general',
            'woocommerce-layout',
            'woocommerce-smallscreen',
            'flatsome-googlefonts',
            'wc-block-style',
            'wc-blocks-style',
        ),
        'defer_scripts' => array(
            'jquery-migrate',
            'jquery-blockui',
            'wc-jquery-blockui',
            'js-cookie',
            'wc-js-cookie',
            'main-scripts-js',
        ),
        'cart_defer_scripts' => array(
            'epos-scripts-js',
            'flatsome-live-search',
            'sourcebuster-js',
            'wc-order-attribution',
            'hoverIntent',
            'flatsome-js',
            'flatsome-cart-refresh',
            'flatsome-theme-woocommerce-js',
            'jquery-migrate',
            'jquery-blockui',
            'wc-jquery-blockui',
            'js-cookie',
            'wc-js-cookie',
            'wc-add-to-cart',
            'woocommerce',
            'wc-country-select',
            'wc-address-i18n',
            'wc-cart',
            'wc-cart-fragments',
            'selectWoo',
        ),
        'noncritical_wc_styles' => array(
            'wc-block-style',
            'wc-blocks-style',
            'wc-blocks-vendors-style',
            'wc-blocks-packages-style',
            'wc-blocks-style-mini-cart',
            'wc-blocks-style-cart',
            'wc-blocks-style-checkout',
        ),
        'noncritical_wc_scripts' => array(
            'wc-blocks',
            'wc-blocks-middleware',
            'wc-blocks-vendors',
            'wc-blocks-registry',
        ),
    );

    return isset($handles[$group]) ? $handles[$group] : array();
}

function custom_optimize_get_local_image_path($src)
{
    if (empty($src) || strpos($src, 'data:') === 0) {
        return null;
    }

    $src_path = wp_parse_url($src, PHP_URL_PATH);

    if (!$src_path) {
        return null;
    }

    $normalized_path = wp_normalize_path($src_path);
    $normalized_root = wp_normalize_path(ABSPATH);
    $content_position = strpos($normalized_path, '/wp-content/');

    if ($content_position === false) {
        return null;
    }

    $relative_path = ltrim(substr($normalized_path, $content_position), '/');
    $absolute_path = wp_normalize_path(trailingslashit(ABSPATH) . $relative_path);

    if (strpos($absolute_path, $normalized_root) !== 0 || !file_exists($absolute_path)) {
        return null;
    }

    return $absolute_path;
}

function custom_optimize_get_image_dimensions($src)
{
    static $dimensions_cache = array();

    if (isset($dimensions_cache[$src])) {
        return $dimensions_cache[$src];
    }

    $dimensions_cache[$src] = null;

    if (empty($src)) {
        return null;
    }

    $attachment_id = attachment_url_to_postid($src);

    if ($attachment_id) {
        $metadata = wp_get_attachment_metadata($attachment_id);

        if (!empty($metadata['width']) && !empty($metadata['height'])) {
            $dimensions_cache[$src] = array(
                'width'  => (int) $metadata['width'],
                'height' => (int) $metadata['height'],
            );

            return $dimensions_cache[$src];
        }
    }

    $file_path = custom_optimize_get_local_image_path($src);

    if (!$file_path || strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) === 'svg') {
        return null;
    }

    $image_size = function_exists('wp_getimagesize') ? wp_getimagesize($file_path) : getimagesize($file_path);

    if (empty($image_size[0]) || empty($image_size[1])) {
        return null;
    }

    $dimensions_cache[$src] = array(
        'width'  => (int) $image_size[0],
        'height' => (int) $image_size[1],
    );

    return $dimensions_cache[$src];
}

function custom_optimize_element_has_ancestor_match($el, $tag_names = array(), $class_fragments = array(), $id_fragments = array())
{
    if (!($el instanceof DOMElement)) {
        return false;
    }

    $tag_names = array_map('strtolower', (array) $tag_names);

    for ($node = $el->parentNode; $node instanceof DOMElement; $node = $node->parentNode) {
        if ($tag_names && in_array(strtolower($node->tagName), $tag_names, true)) {
            return true;
        }

        $class = strtolower($node->getAttribute('class'));
        $id = strtolower($node->getAttribute('id'));

        foreach ((array) $class_fragments as $fragment) {
            if ($fragment !== '' && strpos($class, strtolower($fragment)) !== false) {
                return true;
            }
        }

        foreach ((array) $id_fragments as $fragment) {
            if ($fragment !== '' && strpos($id, strtolower($fragment)) !== false) {
                return true;
            }
        }
    }

    return false;
}

function custom_optimize_is_lcp_image_candidate($el, $src, $dimensions)
{
    if (!($el instanceof DOMElement) || empty($src) || strpos($src, 'data:') === 0) {
        return false;
    }

    $path = strtolower((string) wp_parse_url($src, PHP_URL_PATH));

    if (!$path || preg_match('/\.(svg|ico)(\?.*)?$/', $path)) {
        return false;
    }

    foreach (array('/assets/icons/', '/icons/', '/avatar', '/logo') as $path_fragment) {
        if (strpos($path, $path_fragment) !== false) {
            return false;
        }
    }

    $class = strtolower($el->getAttribute('class'));
    $alt = strtolower($el->getAttribute('alt'));

    foreach (array('logo', 'icon', 'avatar', 'emoji', 'payment', 'spinner') as $fragment) {
        if (strpos($class, $fragment) !== false || strpos($alt, $fragment) !== false) {
            return false;
        }
    }

    if (custom_optimize_element_has_ancestor_match(
        $el,
        array('header', 'footer', 'nav'),
        array('header', 'footer', 'nav', 'menu', 'mini-cart', 'breadcrumb', 'slider-nav'),
        array('header', 'footer', 'nav')
    )) {
        return false;
    }

    if (!$dimensions) {
        return true;
    }

    $width = !empty($dimensions['width']) ? (int) $dimensions['width'] : 0;
    $height = !empty($dimensions['height']) ? (int) $dimensions['height'] : 0;

    return !($width > 0 && $height > 0 && ($width < 180 || $height < 120));
}

function custom_optimize_mark_lcp_image($el, $decoding = 'async')
{
    if (!($el instanceof DOMElement)) {
        return;
    }

    $el->setAttribute('fetchpriority', 'high');
    $el->setAttribute('loading', 'eager');
    $el->setAttribute('decoding', $decoding);
}

function custom_optimize_get_img_dimensions_from_dom($el, $src)
{
    if ($el->hasAttribute('width') && $el->hasAttribute('height')) {
        return array(
            'width'  => (int) $el->getAttribute('width'),
            'height' => (int) $el->getAttribute('height'),
        );
    }

    return custom_optimize_get_image_dimensions($src);
}

function custom_optimize_add_missing_img_dimensions($el, $dimensions)
{
    if (!$dimensions) {
        return;
    }

    if (!$el->hasAttribute('width')) {
        $el->setAttribute('width', (string) $dimensions['width']);
    }

    if (!$el->hasAttribute('height')) {
        $el->setAttribute('height', (string) $dimensions['height']);
    }
}

function custom_optimize_html_output($html)
{
    if (!custom_optimize_is_frontend() || !class_exists('DOMDocument') || stripos($html, '<html') === false) {
        return $html;
    }

    libxml_use_internal_errors(true);

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadHTML($html);

    foreach ($dom->getElementsByTagName('a') as $el) {
        if (!$el->hasAttribute('name')) {
            $el->setAttribute('name', 'epos');
        }
    }

    foreach ($dom->getElementsByTagName('button') as $el) {
        if (!$el->hasAttribute('name')) {
            $el->setAttribute('name', 'epos');
        }
    }

    $lcp_image_marked = false;

    foreach ($dom->getElementsByTagName('img') as $el) {
        $src = $el->getAttribute('src');
        $dimensions = custom_optimize_get_img_dimensions_from_dom($el, $src);

        custom_optimize_add_missing_img_dimensions($el, $dimensions);

        if (strpos($src, '/assets/icons/empty-cart.png') !== false) {
            custom_optimize_mark_lcp_image($el, 'sync');
            $lcp_image_marked = true;
            continue;
        }

        if (!$lcp_image_marked && custom_optimize_is_lcp_image_candidate($el, $src, $dimensions)) {
            custom_optimize_mark_lcp_image($el);
            $lcp_image_marked = true;
        }
    }

    return $dom->saveHTML();
}

add_action('template_redirect', function () {
    ob_start('custom_optimize_html_output');
});

function custom_flatsome_icons_font_url($file)
{
    return get_template_directory_uri() . '/assets/css/icons/' . ltrim($file, '/');
}

function custom_flatsome_get_icons_font_face_css()
{
    return sprintf(
        '@font-face{font-family:"fl-icons";font-style:normal;font-weight:400;font-display:swap;src:url(%1$s);src:url(%2$s) format("embedded-opentype"),url(%3$s) format("woff2"),url(%4$s) format("woff"),url(%5$s) format("truetype"),url(%6$s) format("svg");}',
        custom_flatsome_icons_font_url('fl-icons.eot'),
        custom_flatsome_icons_font_url('fl-icons.eot?#iefix'),
        custom_flatsome_icons_font_url('fl-icons.woff2'),
        custom_flatsome_icons_font_url('fl-icons.woff'),
        custom_flatsome_icons_font_url('fl-icons.ttf'),
        custom_flatsome_icons_font_url('fl-icons.svg#fl-icons')
    );
}

function custom_flatsome_add_icons_css()
{
    global $wp_styles;

    if (!($wp_styles instanceof WP_Styles) || !isset($wp_styles->registered['flatsome-main'])) {
        return;
    }

    $existing_after = $wp_styles->registered['flatsome-main']->extra['after'] ?? array();
    $existing_after = is_array($existing_after) ? $existing_after : array($existing_after);

    $wp_styles->registered['flatsome-main']->extra['after'] = array_values(array_filter($existing_after, function ($css) {
        return strpos($css, 'font-family: "fl-icons"') === false;
    }));

    wp_add_inline_style('flatsome-main', custom_flatsome_get_icons_font_face_css());
}

function custom_flatsome_preload_icons_font()
{
    if (!custom_optimize_is_frontend()) {
        return;
    }

    printf(
        '<link rel="preload" href="%1$s" as="font" type="font/woff2" crossorigin>' . "\n",
        esc_url(custom_flatsome_icons_font_url('fl-icons.woff2'))
    );
}

function custom_optimize_preload_theme_fonts()
{
    if (!custom_optimize_is_frontend()) {
        return;
    }

    $font_paths = array(
        'assets/fonts/Inter/inter-latin.woff2',
        'assets/fonts/Montserrat/Montserrat-Regular.woff2',
        'assets/fonts/Montserrat/Montserrat-Medium.woff2',
        'assets/fonts/Montserrat/Montserrat-Bold.woff2',
        'assets/fonts/PlusJakartaSans/PlusJakartaSans-Regular.woff2',
    );

    foreach ($font_paths as $font_path) {
        printf(
            '<link rel="preload" href="%1$s" as="font" type="font/woff2" crossorigin>' . "\n",
            esc_url(custom_optimize_child_asset_url($font_path))
        );
    }
}

function custom_optimize_add_cls_critical_css()
{
    if (!custom_optimize_is_frontend()) {
        return;
    }

    ?>
<style id="custom-cls-critical-css">
.header-main{min-height:90px}.header-inner{min-height:90px}.header .flex-col{min-width:0}.header .logo{width:160px;flex:0 0 auto}.header-logo,.header-logo-dark{display:block;width:160px;height:auto;aspect-ratio:1020/228}.header-nav.header-nav-main{min-height:90px;align-items:center}.header .nav-top-link{display:inline-flex;align-items:center;gap:6px;line-height:1.2}.header .ux-menu-icon{display:inline-block;width:20px!important;height:20px!important;min-width:20px;object-fit:contain}.header .flex-col.hide-for-medium.flex-right{min-width:170px;min-height:50px;display:flex;justify-content:flex-end;align-items:center}.header-my .header-inner>.flex-col.hide-for-medium.flex-right{flex:0 0 1040px;max-width:calc(100% - 180px);min-height:90px}.header-my .header-inner>.flex-col.hide-for-medium.flex-right>.flex-row{display:flex;flex-wrap:nowrap;width:100%;justify-content:flex-end;align-items:center}.header-my .header-nav.header-nav-main{display:flex;flex-wrap:nowrap!important;align-items:center;justify-content:flex-end;white-space:nowrap;min-height:90px}.header-my .header-nav.header-nav-main>li{flex:0 0 auto}.header-my .header-nav.header-nav-main>li>a{font-family:Inter,Arial,sans-serif;font-size:16px;font-weight:700;line-height:90px;min-height:90px;padding-top:0;padding-bottom:0}#content .width-80,#main .width-80{max-width:100%}@media (min-width:992px){#content .width-80,#main .width-80{max-width:80%!important}}#content .section-custom-container{margin:20px!important;width:auto!important;overflow:hidden}#content .section-custom-container .section-bg{border-radius:30px!important}#content .section-custom-container>.section-content>.row:first-child{min-height:520px}@media (min-width:550px){#content .section-custom-container>.section-content>.row:first-child{min-height:680px}}@media (min-width:992px){.header-main,.header-inner,.header-nav.header-nav-main{min-height:90px}#content .section-custom-container{margin:50px!important}#content .section-custom-container>.section-content>.row:first-child{min-height:760px}}.img img,img.ux-menu-icon{height:auto}
</style>
    <?php
}

function custom_optimize_add_cart_critical_css()
{
    if (!custom_optimize_is_frontend() || !custom_optimize_is_cart_page()) {
        return;
    }

    ?>
<style id="custom-cart-critical-css">
.woocommerce-cart #main{background:#f6f8fe;min-height:720px}.woocommerce-cart .checkout-page-title{min-height:160px;display:flex;align-items:center}.woocommerce-cart .checkout-page-title .page-title-inner{min-height:90px}.woocommerce-cart .checkout-page-title__inner{display:flex;align-items:center;justify-content:space-between;gap:24px}.woocommerce-cart .checkout-breadcrumbs{display:flex;align-items:center;justify-content:flex-end;gap:16px;min-height:44px;white-space:nowrap}.woocommerce-cart .cart-container.page-wrapper.page-checkout{display:block;min-height:560px;padding-top:0;padding-bottom:0}.woocommerce-cart .cart-container.page-wrapper.page-checkout>.woocommerce{min-height:560px}.woocommerce-cart .woocommerce.row.cart-section{display:flex;align-items:flex-start;gap:30px;max-width:1250px;margin-left:auto;margin-right:auto}.woocommerce-cart .cart-section>.col{float:none}.woocommerce-cart .left-col{flex:1 1 auto;min-width:0}.woocommerce-cart .right-col{flex:0 0 360px}.woocommerce-cart .cart-title{display:flex;align-items:center;justify-content:space-between;gap:16px}.woocommerce-cart .cart-title h1{margin:0;line-height:1.2}.woocommerce-cart .cart-sidebar-card{background:#fff;border-radius:8px;padding:24px;box-shadow:0 10px 30px rgba(10,34,170,.08)}.woocommerce-cart .cart-checkout-btn,.woocommerce-cart .return-to-shop .button{display:inline-flex;align-items:center;justify-content:center;min-height:56px;border-radius:6px;background:#0a22aa;color:#fff;font-weight:700;text-align:center}.woocommerce-cart .return-to-shop .button{min-width:min(428px,100%)}.woocommerce-cart .empty-cart-section{min-height:560px;display:flex;align-items:center;justify-content:center;text-align:center}.woocommerce-cart .empty-cart-content{width:min(520px,100%);margin:auto}.woocommerce-cart .empty-cart-message img{display:block;width:200px;height:200px;object-fit:contain;margin:0 auto 24px}.woocommerce-cart .cart-secure-badge{display:flex;align-items:center;gap:8px}.woocommerce-cart .cart-secure-badge img{width:20px;height:20px;object-fit:contain}@media (max-width:849px){.woocommerce-cart .checkout-page-title{min-height:120px}.woocommerce-cart .checkout-page-title__inner{display:block}.woocommerce-cart .woocommerce.row.cart-section{display:block}.woocommerce-cart .right-col{width:100%;max-width:none}.woocommerce-cart .cart-container.page-wrapper.page-checkout,.woocommerce-cart .cart-container.page-wrapper.page-checkout>.woocommerce,.woocommerce-cart .empty-cart-section{min-height:440px}.woocommerce-cart .empty-cart-section{padding-left:20px;padding-right:20px}}
</style>
    <?php
}

function custom_optimize_remove_jquery_migrate($scripts)
{
    if (!custom_optimize_is_frontend() || !($scripts instanceof WP_Scripts) || empty($scripts->registered['jquery'])) {
        return;
    }

    $jquery = $scripts->registered['jquery'];

    if (!empty($jquery->deps)) {
        $jquery->deps = array_diff($jquery->deps, array('jquery-migrate'));
    }
}

function custom_optimize_request_path_matches($slugs)
{
    global $wp;

    $request_path = isset($wp->request) ? trim((string) $wp->request, '/') : '';

    if ($request_path === '') {
        return false;
    }

    foreach ((array) $slugs as $slug) {
        $slug = trim((string) $slug, '/');

        if ($slug !== '' && ($request_path === $slug || substr($request_path, -strlen('/' . $slug)) === '/' . $slug)) {
            return true;
        }
    }

    return false;
}

function custom_optimize_is_sensitive_woocommerce_page()
{
    if ((function_exists('is_cart') && is_cart())
        || (function_exists('is_checkout') && is_checkout())
        || (function_exists('is_account_page') && is_account_page())) {
        return true;
    }

    return custom_optimize_request_path_matches(array('cart', 'checkout', 'my-account'));
}

function custom_optimize_is_cart_page()
{
    if (function_exists('is_cart') && is_cart()) {
        return true;
    }

    return custom_optimize_request_path_matches('cart');
}

function custom_optimize_is_checkout_or_account_page()
{
    if ((function_exists('is_checkout') && is_checkout())
        || (function_exists('is_account_page') && is_account_page())) {
        return true;
    }

    return custom_optimize_request_path_matches(array('checkout', 'my-account'));
}

function custom_optimize_dequeue_noncritical_woocommerce_assets()
{
    if (!custom_optimize_is_frontend() || custom_optimize_is_sensitive_woocommerce_page()) {
        return;
    }

    custom_optimize_dequeue_handles('style', custom_optimize_asset_handles('noncritical_wc_styles'));
    custom_optimize_dequeue_handles('script', custom_optimize_asset_handles('noncritical_wc_scripts'));
}

function custom_optimize_disable_zippy_core_frontend_assets()
{
    if (!custom_optimize_is_frontend()) {
        return;
    }

    custom_optimize_dequeue_handles('style', array('core-web-styles'));
    custom_optimize_dequeue_handles('script', array('core-web-scripts'));
}

function custom_optimize_cart_preload_images()
{
    if (!custom_optimize_is_frontend() || !custom_optimize_is_cart_page()) {
        return;
    }

    $woocommerce = function_exists('WC') ? WC() : null;

    if ($woocommerce && isset($woocommerce->cart) && $woocommerce->cart && !$woocommerce->cart->is_empty()) {
        return;
    }

    printf(
        '<link rel="preload" href="%1$s" as="image" fetchpriority="high">' . "\n",
        esc_url(custom_optimize_child_asset_url('assets/icons/empty-cart.png'))
    );
}

function custom_optimize_cart_disable_extra_assets()
{
    if (!custom_optimize_is_frontend() || !custom_optimize_is_cart_page()) {
        return;
    }

    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');
}

function custom_optimize_style_loader_tag($html, $handle, $href, $media)
{
    $async_style_handles = custom_optimize_asset_handles('async_styles');

    if (custom_optimize_is_cart_page()) {
        $async_style_handles = array_merge($async_style_handles, custom_optimize_asset_handles('cart_async_styles'));
    }

    if (!custom_optimize_is_frontend() || !in_array($handle, $async_style_handles, true)) {
        return $html;
    }

    return sprintf(
        '<link rel="preload" href="%1$s" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" media="%2$s">' .
        '<noscript><link rel="stylesheet" href="%1$s" media="%2$s"></noscript>' . "\n",
        esc_url($href),
        esc_attr($media ?: 'all')
    );
}

function custom_optimize_script_loader_tag($tag, $handle, $src)
{
    $defer_script_handles = custom_optimize_asset_handles('defer_scripts');

    if (custom_optimize_is_cart_page()) {
        $defer_script_handles = array_merge($defer_script_handles, custom_optimize_asset_handles('cart_defer_scripts'));
    }

    if (!custom_optimize_is_frontend()
        || custom_optimize_is_checkout_or_account_page()
        || !in_array($handle, $defer_script_handles, true)
        || strpos($tag, ' defer') !== false
        || strpos($tag, ' async') !== false) {
        return $tag;
    }

    return str_replace(' src', ' defer src', $tag);
}

if (function_exists('flatsome_add_icons_css')) {
    remove_action('wp_enqueue_scripts', 'flatsome_add_icons_css', 150);
}

add_action('after_setup_theme', function () {
    remove_action('wp_enqueue_scripts', 'flatsome_add_icons_css', 150);
}, 20);

add_action('wp_enqueue_scripts', 'custom_flatsome_add_icons_css', 999);
add_action('wp_enqueue_scripts', 'custom_optimize_dequeue_noncritical_woocommerce_assets', 999);
add_action('wp_enqueue_scripts', 'custom_optimize_disable_zippy_core_frontend_assets', 1000);
add_action('wp_default_scripts', 'custom_optimize_remove_jquery_migrate', 20);
add_action('wp', 'custom_optimize_cart_disable_extra_assets');

add_action('wp_head', 'custom_optimize_cart_preload_images', 0);
add_action('wp_head', 'custom_flatsome_preload_icons_font', 1);
add_action('wp_head', 'custom_optimize_preload_theme_fonts', 1);
add_action('wp_head', 'custom_optimize_add_cls_critical_css', 2);
add_action('wp_head', 'custom_optimize_add_cart_critical_css', 3);
add_filter('style_loader_tag', 'custom_optimize_style_loader_tag', 20, 4);
add_filter('script_loader_tag', 'custom_optimize_script_loader_tag', 20, 3);
