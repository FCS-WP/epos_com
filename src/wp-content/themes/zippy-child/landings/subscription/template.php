<?php
/*
 * Template Name: Ads — Subscription
 *
 * Bare landing page (no main header/footer, no theme chrome) for ad campaigns
 * and HubSpot form submissions. Reads content from content.json.
 *
 * NOTE: This template does NOT call wp_head() / wp_footer() — instead it uses
 * landing_head() and landing_footer() which emit only what the landing needs
 * (no Flatsome, WooCommerce, plugin chrome, or tracking). WP Rocket page
 * cache + minify continue to work because they hook template_redirect.
 *
 * If a campaign needs tracking (GTM, FB Pixel, GA), paste the snippet
 * directly below <head> or before </body> in this template.
 */

if (! defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<?php landing_head(); ?>
</head>
<body <?php body_class('landing landing--subscription'); ?>>
    <main class="landing__main">
        <?php landing_partial('hero'); ?>
        <?php landing_partial('features'); ?>
        <?php landing_partial('form'); ?>
        <?php landing_partial('footer-cta'); ?>
    </main>
<?php landing_footer(); ?>
</body>
</html>
