<?php
add_action('wp_enqueue_scripts', 'shin_scripts');

function shin_scripts()
{
  $css_path = THEME_DIR . '-child/assets/dist/css/epos.min.css';
  $js_path  = THEME_DIR . '-child/assets/dist/js/epos.min.js';
  $css_ver  = file_exists($css_path) ? filemtime($css_path) : null;
  $js_ver   = file_exists($js_path)  ? filemtime($js_path)  : null;

  wp_enqueue_style('epos-style-css', THEME_URL . '-child/assets/dist/css/epos.min.css', array(), $css_ver, 'all');

  wp_enqueue_script('epos-scripts-js', THEME_URL . '-child/assets/dist/js/epos.min.js', array('jquery'), $js_ver, true);

  // Route-scoped CSS bundles (split out of epos.min.css to reduce site-wide payload)
  $request_uri = isset($_SERVER['REQUEST_URI']) ? trim($_SERVER['REQUEST_URI'], '/') : '';

  // Epos 360 — match header.php URI pattern
  $is_epos360 = ($request_uri === 'my/epos360' || strpos($request_uri, 'my/epos360/') === 0);
  if ($is_epos360) {
    $epos360_css = THEME_DIR . '-child/assets/dist/css/epos-360.min.css';
    $epos360_ver = file_exists($epos360_css) ? filemtime($epos360_css) : null;
    wp_enqueue_style('epos-360-css', THEME_URL . '-child/assets/dist/css/epos-360.min.css', array('epos-style-css'), $epos360_ver, 'all');
  }

  // WooCommerce flow — cart, checkout, order-received
  $is_wc_flow = function_exists('is_cart') && (
    is_cart()
    || (function_exists('is_checkout') && is_checkout())
    || (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received'))
  );
  if ($is_wc_flow) {
    $wc_css = THEME_DIR . '-child/assets/dist/css/woocommerce-flow.min.css';
    $wc_ver = file_exists($wc_css) ? filemtime($wc_css) : null;
    wp_enqueue_style('woocommerce-flow-css', THEME_URL . '-child/assets/dist/css/woocommerce-flow.min.css', array('epos-style-css'), $wc_ver, 'all');
  }
}
//Add gallery video for product
function add_product_video_url_meta_box()
{
  add_meta_box(
    'product_video_url',
    'Video Product',
    'display_product_video_url_meta_box',
    'product',
    'normal',
    'high'
  );
}
// add_action('add_meta_boxes', 'add_product_video_url_meta_box');

function display_product_video_url_meta_box($post)
{
  $video_url = get_post_meta($post->ID, '_product_video_url', true);
?>
  <label for="product_video_url">Link Video Product:</label>
  <input type="text" id="product_video_url" name="product_video_url" value="<?php echo esc_url($video_url); ?>" style="width: 100%;" />
<?php
}

function save_product_video_url_meta_box($post_id)
{
  if (isset($_POST['product_video_url'])) {
    update_post_meta($post_id, '_product_video_url', esc_url($_POST['product_video_url']));
  }
}
// add_action('save_post_product', 'save_product_video_url_meta_box');


function display_product_video_on_single_product()
{
  global $post;

  $video_url = get_post_meta($post->ID, '_product_video_url', true);
?>

  <div data-thumb="<?php echo $video_url; ?>" data-thumb-alt="" class="woocommerce-product-gallery__image product-video" style="width: 496px; margin-right: 0px; float: left; display: block;">
    <?php if (!empty($video_url)) : ?>
      <a data-elementor-open-lightbox="no">
        <video width="100%" height="100%" controls="false" loop="true" autoplay="true">
          <source src="<?php echo $video_url; ?>" type="video/mp4">
        </video>
      </a>
    <?php endif; ?>
    <?php ?>
  </div>

<?php
}
// add_action('woocommerce_product_thumbnails', 'display_product_video_on_single_product', 0, 0);

foreach (glob(THEME_DIR . '-child' . "/includes/workable/*.php") as $file_name) {
  require_once($file_name);
}

