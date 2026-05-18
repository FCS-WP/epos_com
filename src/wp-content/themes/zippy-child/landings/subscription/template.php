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

    <?php
    // Meta Pixel — base code is normally injected by My_FB_Init via the
    $fb_pixel_id = function_exists('get_my_fb_pixel_id') ? get_my_fb_pixel_id() : '';
    if ($fb_pixel_id) :
    ?>
    <!-- Meta Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
    document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '<?php echo esc_js($fb_pixel_id); ?>');
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=<?php echo esc_attr($fb_pixel_id); ?>&ev=PageView&noscript=1" /></noscript>
    <!-- End Meta Pixel Code -->
    <?php endif; ?>
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
