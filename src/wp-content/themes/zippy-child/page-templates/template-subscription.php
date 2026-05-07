<?php
/*
Template Name: Subscription V2
*/
require __DIR__ . '/subscription/template-data.php';

// Whatsapp widget
add_action('wp_enqueue_scripts', function () { 
  $wa_origin_file = get_stylesheet_directory() . '/assets/js/widgetWhatsappCustom.js';
  if (is_page('subscription')) {
    wp_enqueue_script('wa-scripts-js', THEME_URL . '-child' . '/assets/js/widgetWhatsappCustom.js', array('jquery'), file_exists($wa_origin_file) ? filemtime($wa_origin_file) : null, true);
  }
});
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?> class="<?php flatsome_html_classes(); ?>">

<head>
  <meta charset="<?php bloginfo('charset'); ?>" />
  <link rel="profile" href="http://gmpg.org/xfn/11" />
  <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

  <?php wp_head(); ?>
</head>

<body <?php body_class('page-template-subscription'); ?>>
  <?php do_action('flatsome_after_body_open'); ?>
  <?php wp_body_open(); ?>

  <?php do_action('flatsome_before_page'); ?>
  <?php do_action('flatsome_after_header'); ?>

  <div id="wrapper">
    <?php while (have_posts()) : the_post(); ?>
      <main id="main" class="sub-page sub-page--v2">
        <?php require __DIR__ . '/subscription/header.php'; ?>
        <?php require __DIR__ . '/subscription/section-hero.php'; ?>
        <?php require __DIR__ . '/subscription/section-partnership.php'; ?>
        <?php require __DIR__ . '/subscription/section-tools.php'; ?>
        <?php require __DIR__ . '/subscription/section-everything.php'; ?>
        <?php require __DIR__ . '/subscription/section-testimonials.php'; ?>
        <?php require __DIR__ . '/subscription/section-grow.php'; ?>
        <?php require __DIR__ . '/subscription/section-faq.php'; ?>

      </main>
    <?php endwhile; ?>
  </div>

  <?php do_action('flatsome_after_page'); ?>

  <?php wp_footer(); ?>

  <?php require __DIR__ . '/subscription/modal-demo.php'; ?>
</body>

</html>