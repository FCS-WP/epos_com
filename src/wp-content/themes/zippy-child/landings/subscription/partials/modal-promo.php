<?php
if (! defined('ABSPATH')) exit;
$sub_v2 = $data ?? array();

$img_desktop = esc_url($sub_v2['images']['hero']['visual']        ?? '');
$img_mobile  = esc_url($sub_v2['images']['hero']['visual_mobile'] ?? '');
?>
<?php // `.sub-page` is added so font/reset rules resolve inside the modal
      // (rendered outside <main> — not a descendant of the .sub-page on <main>). ?>
<div class="sub-page sub-v2-modal-promo"
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

      <!-- Left: text (desktop CTA lives here) -->
      <div class="sub-v2-modal-promo__text">
        <p class="sub-v2-modal-promo__title">GROW</p>
        <p class="sub-v2-modal-promo__subtitle">Your Business Digitally<br>With Confidence</p>
        <a href="https://api.whatsapp.com/send/?phone=60124655571&text=Hi%21+I%27d+like+to+find+out+how+EPOS360+can+grow+my+business.+Can+you+tell+me+more%3F&type=phone_number&app_absent=0"
           class="sub-v2-modal-promo__cta sub-v2-modal-promo__cta--desktop"
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

      <!-- Mobile: image + CTA button overlaid at bottom -->
      <?php if ($img_mobile) : ?>
      <div class="sub-v2-modal-promo__mobile-wrap">
        <img src="<?php echo $img_mobile; ?>"
             alt="EPOS360 — Grow your business digitally"
             loading="lazy"
             decoding="async">
        <a href="https://api.whatsapp.com/send/?phone=60124655571&text=Hi%21+I%27d+like+to+find+out+how+EPOS360+can+grow+my+business.+Can+you+tell+me+more%3F&type=phone_number&app_absent=0"
           class="sub-v2-modal-promo__cta sub-v2-modal-promo__cta--mobile"
           data-sub-v2-promo-learn-more>
          Learn More
        </a>
      </div>
      <?php endif; ?>

    </div><!-- /.sub-v2-modal-promo__content -->
  </div><!-- /.sub-v2-modal-promo__dialog -->
</div><!-- /.sub-v2-modal-promo -->
