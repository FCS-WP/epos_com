<?php
if (! defined('ABSPATH')) exit;
$sub_v2 = $data ?? array();

$sub_v2_grow_features = [
  'Fast onboarding through TNG eWallet app',
  'Integrate multiple food delivery platforms in one place',
  'Launch online store to expand your customer base',
  'Scan-to-order lifts revenue by speeding up service',
  'Drive repeat customers through loyalty & memberships',
  'AI-powered smart insights for growth',
  '24/7 customer support',
  'All-in-one payment hub with FREE soundbox',
];
?>

<!-- Section: Grow Plan -->
<section class="sub-v2-grow" id="sub-v2-grow">
  <div class="sub-shell">
    <div class="sub-v2-grow__head" data-animate-group="section-head">
      <p class="sub-v2-grow__eyebrow sub-v2-kicker">
        <img class="sub-v2-kicker__icon" src="<?php echo esc_url($sub_v2['section_kicker_icon']); ?>" alt="" aria-hidden="true" decoding="async">
        <span>EPOS360</span>
      </p>
      <h2 class="sub-v2-grow__title sub-v2-section-title">
        Run A Smarter Business
        <span>With The Grow Plan</span>
      </h2>
    </div>
  </div>

  <div class="sub-v2-grow__stage">
    <div class="sub-v2-grow__photo sub-v2-grow__photo--left" data-animate="fade-up">
      <img
        src="<?php echo esc_url($sub_v2['images']['grow']['merchant_left']); ?>"
        alt="Merchant using EPOS360"
        loading="lazy"
        decoding="async">
    </div>

    <article class="sub-v2-grow__card" data-animate="grow-card">
      <div class="sub-v2-grow__ribbon">Grow</div>

      <div class="sub-v2-grow__price-block">
        <p class="sub-v2-grow__price-from">FROM</p>
        <div class="sub-v2-grow__price-row">
          <span class="sub-v2-grow__price">RM39</span>
        </div>
      </div>

      <div class="sub-v2-grow__offer">
        <span class="sub-v2-grow__offer-icon" aria-hidden="true">
          <svg width="46" height="46" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 12V21H4V12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M22 7H2V12H22V7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M12 21V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M12 7H7.5C6.11929 7 5 5.88071 5 4.5C5 3.11929 6.11929 2 7.5 2C10 2 12 7 12 7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M12 7H16.5C17.8807 7 19 5.88071 19 4.5C19 3.11929 17.8807 2 16.5 2C14 2 12 7 12 7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </span>
        <p>Subscribe for 12 months,<br><span class="sub-v2-grow__offer-highlight">get 3 extra months free</span></p>
      </div>

      <div class="sub-v2-grow__actions">
        <a href="<?php echo esc_url($sub_v2['contact_sales_url']); ?>" class="sub-v2-grow__button sub-v2-grow__button--ghost">Contact Sales</a>
        <a href="#sub-v2-demo" class="sub-v2-grow__button sub-v2-grow__button--primary">Get a Demo</a>
      </div>

      <ul class="sub-v2-grow__features" data-animate-group="grow-features" data-animate="stagger">
        <?php foreach ($sub_v2_grow_features as $feature) : ?>
          <li data-animate="stagger-item" data-stagger-item><?php echo esc_html($feature); ?></li>
        <?php endforeach; ?>
      </ul>
    </article>

    <div class="sub-v2-grow__photo sub-v2-grow__photo--right" data-animate="fade-up">
      <img
        src="<?php echo esc_url($sub_v2['images']['grow']['merchant_right']); ?>"
        alt="Merchant holding payment device"
        loading="lazy"
        decoding="async">
    </div>
  </div>
</section>
