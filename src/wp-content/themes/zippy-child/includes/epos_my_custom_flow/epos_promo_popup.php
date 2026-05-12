<?php
// Old popup version
/*
add_action('wp_footer', function () {
    if (is_page('my/home')) {
        ?>
            <div id="BlueTap-Promo" class="bluetap-promo">
                <div class="bluetap-promo-overlay"></div>

                <div class="bluetap-promo-content">
                    <button class="bluetap-promo-close" aria-label="Close popup">×</button>

                    <?php echo do_shortcode('[block id="bluetap-promo"]'); ?>
                </div>
            </div>
        <?php
    }
});
*/

// New popup version
if (! defined('ABSPATH')) exit;

// ── Homepage promo popup (/my/home) ──────────────────────────────────────────
// Mirrors the GROW promo popup used on the subscription landing page.
// To update: change the three constants below.
define('HP_PROMO_WHATSAPP_URL', '/my/subscription');
define('HP_PROMO_BANNER_DESKTOP', 'https://www.epos.com/wp-content/uploads/2026/05/popup-banner-desktop.webp');
define('HP_PROMO_BANNER_MOBILE', 'https://www.epos.com/wp-content/uploads/2026/05/R5_KV-Website_mobile-copy-1-1.webp');

// Poppins is already loaded via local @font-face declarations in _fonts.scss —
// no Google Fonts API call needed.
add_action('wp_footer', function () {
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    if (! preg_match('#^/my/home/?(?:\?|$)#', $uri)) return;

    $whatsapp_url = esc_url(HP_PROMO_WHATSAPP_URL);
    $img_desktop  = esc_url(HP_PROMO_BANNER_DESKTOP);
    $img_mobile   = esc_url(HP_PROMO_BANNER_MOBILE);
    ?>
<div class="sub-v2-modal-promo"
     id="sub-v2-promo-modal"
     data-sub-v2-promo-modal
     hidden
     aria-hidden="true">

  <div class="sub-v2-modal-promo__backdrop" data-sub-v2-promo-modal-close></div>

  <div class="sub-v2-modal-promo__dialog"
       role="dialog"
       aria-modal="true"
       aria-label="Grow your business"
       tabindex="-1">

    <button type="button"
            class="sub-v2-modal-promo__close"
            data-sub-v2-promo-modal-close
            aria-label="Close">
      <span aria-hidden="true">&#x2715;</span>
    </button>

    <div class="sub-v2-modal-promo__content">

      <!-- Left: text -->
      <div class="sub-v2-modal-promo__text">
        <p class="sub-v2-modal-promo__title">GROW</p>
        <p class="sub-v2-modal-promo__subtitle">Your Business Digitally<br>With Confidence</p>
        <a href="<?php echo $whatsapp_url; ?>"
           class="sub-v2-modal-promo__cta sub-v2-modal-promo__cta--desktop" rel="noopener noreferrer"
           data-sub-v2-promo-learn-more>
          Learn More
        </a>
      </div>

      <!-- Right: visual (desktop only) -->
      <?php if ($img_desktop) : ?>
      <div class="sub-v2-modal-promo__visual sub-v2-modal-promo__visual--desktop">
        <img src="<?php echo $img_desktop; ?>"
             alt="EPOS360 — Grow your business digitally"
             loading="lazy"
             decoding="async">
      </div>
      <?php endif; ?>

      <!-- Mobile: image + CTA overlaid at bottom -->
      <?php if ($img_mobile) : ?>
      <div class="sub-v2-modal-promo__mobile-wrap">
        <img src="<?php echo $img_mobile; ?>"
             alt="EPOS360 — Grow your business digitally"
             loading="lazy"
             decoding="async">
        <a href="<?php echo $whatsapp_url; ?>"
           class="sub-v2-modal-promo__cta sub-v2-modal-promo__cta--mobile"
           rel="noopener noreferrer"
           data-sub-v2-promo-learn-more>
          Learn More
        </a>
      </div>
      <?php endif; ?>

    </div><!-- /.sub-v2-modal-promo__content -->
  </div><!-- /.sub-v2-modal-promo__dialog -->
</div><!-- /.sub-v2-modal-promo -->
    <?php
});