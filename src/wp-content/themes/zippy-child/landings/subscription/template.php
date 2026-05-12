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
</head>

<body <?php body_class('landing landing--subscription page-template-subscription-v2'); ?>>
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
