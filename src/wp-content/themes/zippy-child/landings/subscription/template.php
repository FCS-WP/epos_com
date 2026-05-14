<?php
/*
 * Template Name: Ads — Subscription
 *
 * Subscription V2 landing — bare page (no theme chrome). HubSpot form is
 * submitted via our REST bridge (see _shared/integrations/hubspot/), NOT
 * via HubSpot's iframe embed.
 *
 * Section order matches the v2 design:
 *   header → hero → partnership → tools → everything → testimonials
 *   → grow → demo (form) → faq → modal-demo (popup form)
 */

if (! defined('ABSPATH')) exit;

$sub_v2 = landing_content();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <?php landing_head(); ?>

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-P4PQK8KM');</script>
    <!-- End Google Tag Manager -->
</head>

<body <?php body_class('landing landing--subscription page-template-subscription-v2'); ?>>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P4PQK8KM"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
     
    <main class="sub-page sub-page--v2 subscription-v2" data-subscription-v2>
        <?php landing_partial('header',              $sub_v2); ?>
        <?php landing_partial('section-hero',        $sub_v2); ?>
        <?php landing_partial('section-partnership', $sub_v2); ?>
        <?php landing_partial('section-tools',       $sub_v2); ?>
        <?php landing_partial('section-everything',  $sub_v2); ?>
        <?php landing_partial('section-testimonials', $sub_v2); ?>
        <?php landing_partial('section-grow',        $sub_v2); ?>
        <?php landing_partial('section-demo',        $sub_v2); ?>
        <?php landing_partial('section-faq',         $sub_v2); ?>
    </main>
    <!-- modal-demo is rendered outside <main> so it isn't disabled by scroll-lock on <body>. We add .sub-page to the modal root so form styles still apply (see partial). -->
    <?php landing_partial('modal-demo',  $sub_v2); ?>
    <?php landing_partial('modal-promo', $sub_v2); ?>
    <?php landing_partial('whatsapp-button', $sub_v2); ?>
    <?php landing_footer(); ?>
</body>

</html>
