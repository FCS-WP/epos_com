<?php
if (! defined('ABSPATH')) exit;
$sub_v2 = $data ?? array();
?>
<!-- Section: Partnership (blue brand banner) -->
<section class="sub-v2-partnership" id="sub-v2-partnership">
  <div class="sub-v2-partnership__shell">
    <div class="sub-v2-partnership__panel" data-animate="fade-up">
      <div class="sub-v2-partnership__brand-row" aria-label="Partner brands">
        <div class="sub-v2-partnership__brand">
          <img
            class="sub-v2-partnership__brand-logo sub-v2-partnership__brand-logo--epos"
            src="<?php echo esc_url($sub_v2['images']['partnership']['logo_epos']); ?>"
            alt="EPOS360"
            loading="lazy"
            decoding="async">
        </div>
        <span class="sub-v2-partnership__brand-symbol" aria-hidden="true">x</span>
        <div class="sub-v2-partnership__brand">
          <img
            class="sub-v2-partnership__brand-logo sub-v2-partnership__brand-logo--tng-small"
            src="<?php echo esc_url($sub_v2['images']['partnership']['logo_tng_small']); ?>"
            alt="Touch 'n Go"
            loading="lazy"
            decoding="async">
        </div>
      </div>

      <div class="sub-v2-partnership__divider"></div>

      <div class="sub-v2-partnership__body">
        <p class="sub-v2-partnership__desc">
          Our Touch &rsquo;n Go eWallet integration offers instant onboarding and the confidence
          to manage all your business funds in one account.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- Section: Payment Methods (white bg) -->
<section class="sub-v2-payments">
  <div class="sub-shell">
    <div class="sub-v2-payments__head" data-animate-group="section-head">
      <p class="sub-v2-payments__eyebrow sub-v2-kicker">
        <img class="sub-v2-kicker__icon" src="<?php echo esc_url($sub_v2['section_kicker_icon']); ?>" alt="" aria-hidden="true" decoding="async">
        <span>Payment Methods</span>
      </p>
      <h2 class="sub-v2-payments__title sub-v2-section-title">
        Accept Popular Payments
        <span>With One Simple System</span>
      </h2>
    </div>

    <div class="sub-v2-payments__logos" aria-label="Supported payments" data-animate-group="payments-logos" data-animate="stagger">
      <div class="sub-v2-payments__logo-item sub-v2-payments__logo-item--alipay" data-animate="stagger-item" data-stagger-item>
        <img src="<?php echo esc_url($sub_v2['images']['payments']['logo_alipay']); ?>" alt="Alipay+" loading="lazy" decoding="async">
      </div>
      <div class="sub-v2-payments__logo-item sub-v2-payments__logo-item--tng" data-animate="stagger-item" data-stagger-item>
        <img src="<?php echo esc_url($sub_v2['images']['payments']['logo_tng_small']); ?>" alt="Touch 'n Go eWallet" loading="lazy" decoding="async">
      </div>
      <div class="sub-v2-payments__logo-item sub-v2-payments__logo-item--duitnow" data-animate="stagger-item" data-stagger-item>
        <img src="<?php echo esc_url($sub_v2['images']['payments']['logo_duitnow']); ?>" alt="DuitNow" loading="lazy" decoding="async">
      </div>
      <div class="sub-v2-payments__logo-item sub-v2-payments__logo-item--mastercard" data-animate="stagger-item" data-stagger-item>
        <img src="<?php echo esc_url($sub_v2['images']['payments']['logo_mastercard']); ?>" alt="Mastercard" loading="lazy" decoding="async">
      </div>
      <div class="sub-v2-payments__logo-item sub-v2-payments__logo-item--visa" data-animate="stagger-item" data-stagger-item>
        <img src="<?php echo esc_url($sub_v2['images']['payments']['logo_visa']); ?>" alt="Visa" loading="lazy" decoding="async">
      </div>
      <div class="sub-v2-payments__logo-item sub-v2-payments__logo-item--mydebit" data-animate="stagger-item" data-stagger-item>
        <img src="<?php echo esc_url($sub_v2['images']['payments']['logo_mydebit']); ?>" alt="MyDebit" loading="lazy" decoding="async">
      </div>
    </div>
  </div>
</section>
