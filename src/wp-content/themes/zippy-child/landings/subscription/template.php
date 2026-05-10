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
    <div id="sub-v2-smoother-wrapper">
        <div id="sub-v2-smoother-content">
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
        </div>
    </div>
    <!-- modal-demo is rendered outside <main> (sibling of it) so it isn't a descendant of the .sub-page on <main>, but we still want the .sub-page .sub-v2-form rules to apply to the form in the modal, so we add .sub-page to the modal's root element (see partial below). -->
    <?php landing_partial('modal-demo', $sub_v2); ?>
    <?php // Floating WhatsApp button — outside smoother so it stays clickable. ?>
    <?php landing_partial('whatsapp-button', $sub_v2); ?>
    <?php landing_footer(); ?>
</body>

</html>
