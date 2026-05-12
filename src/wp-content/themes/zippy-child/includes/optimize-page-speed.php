<?php
//cusstom meta

add_filter('flatsome_viewport_meta', function () {
  return '<meta name="viewport" content="width=device-width, initial-scale=1">';
});

function custom_flatsome_get_icons_font_base()
{
  $theme   = wp_get_theme(get_template());
  $version = $theme->get('Version');

  return array(
    'version'   => $version,
    'fonts_url' => get_template_directory_uri() . '/assets/css/icons',
  );
}

function custom_flatsome_get_icons_font_face_css()
{
  $font_data = custom_flatsome_get_icons_font_base();

  return '@font-face {
		font-family: "fl-icons";
		font-style: normal;
		font-weight: 400;
		font-display: swap;
		src: url(' . $font_data['fonts_url'] . '/fl-icons.eot?v=' . $font_data['version'] . ');
		src:
			url(' . $font_data['fonts_url'] . '/fl-icons.eot?v=' . $font_data['version'] . '#iefix) format("embedded-opentype"),
			url(' . $font_data['fonts_url'] . '/fl-icons.woff2?v=' . $font_data['version'] . ') format("woff2"),
			url(' . $font_data['fonts_url'] . '/fl-icons.woff?v=' . $font_data['version'] . ') format("woff"),
			url(' . $font_data['fonts_url'] . '/fl-icons.ttf?v=' . $font_data['version'] . ') format("truetype"),
			url(' . $font_data['fonts_url'] . '/fl-icons.svg?v=' . $font_data['version'] . '#fl-icons) format("svg");
	}';
}

function custom_flatsome_add_icons_css()
{
  global $wp_styles;

  if (!($wp_styles instanceof WP_Styles) || !isset($wp_styles->registered['flatsome-main'])) {
    return;
  }

  $existing_after = $wp_styles->registered['flatsome-main']->extra['after'] ?? array();

  if (!is_array($existing_after)) {
    $existing_after = array($existing_after);
  }

  // Remove the parent theme's blocking fl-icons font-face before adding our optimized version.
  $filtered_after = array_filter($existing_after, function ($css) {
    return strpos($css, 'font-family: "fl-icons"') === false;
  });

  $wp_styles->registered['flatsome-main']->extra['after'] = array_values($filtered_after);

  wp_add_inline_style(
    'flatsome-main',
    custom_flatsome_get_icons_font_face_css()
  );
}

function custom_flatsome_preload_icons_font()
{
  if (is_admin()) {
    return;
  }

  $font_data = custom_flatsome_get_icons_font_base();
  $font_url  = $font_data['fonts_url'] . '/fl-icons.woff2?v=' . $font_data['version'];

  printf(
    '<link rel="preload" href="%1$s" as="font" type="font/woff2" crossorigin>' . "\n",
    esc_url($font_url)
  );

  // Preload LCP image cho mobile
  if (wp_is_mobile()) {
    echo '<link rel="preload" as="image" href="https://www.epos.com/wp-content/uploads/2026/05/Run-Your-Business-Not-Just-Payments-2-optimized.webp" fetchpriority="high">' . "\n";
  }
}

function custom_optimize_preload_theme_fonts()
{
  if (is_admin()) {
    return;
  }

  $font_base_url = trailingslashit(get_stylesheet_directory_uri()) . 'assets/fonts';
  $font_paths = array(
    '/Poppins/Poppins-Regular-400.woff2',
    '/Poppins/Poppins-Medium-500.woff2',
    '/Poppins/Poppins-SemiBold-600.woff2',
    '/Poppins/Poppins-Bold-700.woff2',
    '/Montserrat/Montserrat-Regular.woff2',
    '/Montserrat/Montserrat-Medium.woff2',
    '/Montserrat/Montserrat-Bold.woff2',
    '/PlusJakartaSans/PlusJakartaSans-Regular.woff2',
    '/Inter/inter-latin.woff2',
  );

  foreach ($font_paths as $font_path) {
    printf(
      '<link rel="preload" href="%1$s" as="font" type="font/woff2" crossorigin>' . "\n",
      esc_url($font_base_url . $font_path)
    );
  }
}

function custom_optimize_add_cls_critical_css()
{
  if (is_admin()) {
    return;
  }

?>
  <style id="custom-cls-critical-css">
    .header-main {
      min-height: 90px
    }

    .header-inner {
      min-height: 90px
    }

    .header .flex-col {
      min-width: 0
    }

    .header .logo {
      width: 160px;
      flex: 0 0 auto
    }

    .header-logo,
    .header-logo-dark {
      display: block;
      width: 160px;
      height: auto;
      aspect-ratio: 1020/228
    }

    .header-nav.header-nav-main {
      min-height: 90px;
      align-items: center
    }

    .header .nav-top-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      line-height: 1.2
    }

    .header .ux-menu-icon {
      display: inline-block;
      width: 20px !important;
      height: 20px !important;
      min-width: 20px;
      object-fit: contain
    }

    .header .flex-col.hide-for-medium.flex-right {
      min-width: 170px;
      min-height: 50px;
      display: flex;
      justify-content: flex-end;
      align-items: center
    }

    #content .width-80,
    #main .width-80 {
      max-width: 100%
    }

    @media (min-width:992px) {

      #content .width-80,
      #main .width-80 {
        max-width: 80% !important
      }
    }

    #content .section-custom-container {
      margin: 20px !important;
      width: auto !important;
      overflow: hidden
    }

    #content .section-custom-container .section-bg {
      border-radius: 30px !important
    }

    #content .section-custom-container>.section-content>.row:first-child {
      min-height: 520px
    }

    @media (min-width:550px) {
      #content .section-custom-container>.section-content>.row:first-child {
        min-height: 680px
      }
    }

    @media (min-width:992px) {

      .header-main,
      .header-inner,
      .header-nav.header-nav-main {
        min-height: 90px
      }

      #content .section-custom-container {
        margin: 50px !important
      }

      #content .section-custom-container>.section-content>.row:first-child {
        min-height: 760px
      }
    }

    .img img,
    img.ux-menu-icon {
      height: auto
    }
  </style>
<?php
}

function custom_optimize_remove_jquery_migrate($scripts)
{
  if (is_admin() || !($scripts instanceof WP_Scripts) || empty($scripts->registered['jquery'])) {
    return;
  }

  $jquery = $scripts->registered['jquery'];

  if (!empty($jquery->deps)) {
    $jquery->deps = array_diff($jquery->deps, array('jquery-migrate'));
  }
}

function custom_optimize_is_sensitive_woocommerce_page()
{
  if ((function_exists('is_cart') && is_cart())
    || (function_exists('is_checkout') && is_checkout())
    || (function_exists('is_account_page') && is_account_page())
  ) {
    return true;
  }

  $request_path = wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
  $request_path = trim((string) $request_path, '/');

  return in_array($request_path, array('cart', 'checkout', 'my-account'), true);
}

function custom_optimize_dequeue_noncritical_woocommerce_assets()
{
  if (is_admin() || custom_optimize_is_sensitive_woocommerce_page()) {
    return;
  }

  $style_handles = array(
    'wc-block-style',
    'wc-blocks-style',
    'wc-blocks-vendors-style',
    'wc-blocks-packages-style',
    'wc-blocks-style-mini-cart',
    'wc-blocks-style-cart',
    'wc-blocks-style-checkout',
  );

  foreach ($style_handles as $handle) {
    wp_dequeue_style($handle);
    wp_deregister_style($handle);
  }

  $script_handles = array(
    'wc-blocks',
    'wc-blocks-middleware',
    'wc-blocks-vendors',
    'wc-blocks-registry',
  );

  foreach ($script_handles as $handle) {
    wp_dequeue_script($handle);
    wp_deregister_script($handle);
  }
}

function custom_optimize_async_style_handles()
{
  return array(
    'flatsome-style',
  );
}

function custom_optimize_defer_script_handles()
{
  return array(
    // 'jquery',
    // 'jquery-core',
    'jquery-migrate',
    'jquery-blockui',
    'wc-jquery-blockui',
    'js-cookie',
    'wc-js-cookie',
    'main-scripts-js',
  );
}

function custom_optimize_style_loader_tag($html, $handle, $href, $media)
{
  if (is_admin() || !in_array($handle, custom_optimize_async_style_handles(), true)) {
    return $html;
  }

  $media = $media ?: 'all';

  return sprintf(
    '<link rel="preload" href="%1$s" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" media="%2$s">' .
      '<noscript><link rel="stylesheet" href="%1$s" media="%2$s"></noscript>' . "\n",
    esc_url($href),
    esc_attr($media)
  );
}

function custom_optimize_script_loader_tag($tag, $handle, $src)
{
  if (is_admin() || custom_optimize_is_sensitive_woocommerce_page() || !in_array($handle, custom_optimize_defer_script_handles(), true)) {
    return $tag;
  }

  if (strpos($tag, ' defer') !== false || strpos($tag, ' async') !== false) {
    return $tag;
  }

  return sprintf(
    '<script src="%1$s" id="%2$s-js" defer></script>' . "\n",
    esc_url($src),
    esc_attr($handle)
  );
}

if (function_exists('flatsome_add_icons_css')) {
  remove_action('wp_enqueue_scripts', 'flatsome_add_icons_css', 150);
}

add_action('after_setup_theme', function () {
  remove_action('wp_enqueue_scripts', 'flatsome_add_icons_css', 150);
}, 20);

add_action('wp_enqueue_scripts', 'custom_flatsome_add_icons_css', 999);
add_action('wp_enqueue_scripts', 'custom_optimize_dequeue_noncritical_woocommerce_assets', 999);
add_action('wp_default_scripts', 'custom_optimize_remove_jquery_migrate', 20);

add_action('wp_head', 'custom_flatsome_preload_icons_font', 1);
add_action('wp_head', 'custom_optimize_preload_theme_fonts', 1);
add_action('wp_head', 'custom_optimize_add_cls_critical_css', 2);
add_filter('style_loader_tag', 'custom_optimize_style_loader_tag', 20, 4);
add_filter('script_loader_tag', 'custom_optimize_script_loader_tag', 20, 3);
