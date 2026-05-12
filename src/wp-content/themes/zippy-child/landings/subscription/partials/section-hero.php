<?php
if (! defined('ABSPATH')) exit;
$sub_v2 = $data ?? array();
?>
<!-- Section: Hero -->
<section class="sub-v2-hero" data-sub-v2-hero>
  <div class="sub-shell">
    <div class="sub-v2-hero__inner">
      <div class="sub-v2-hero__content" data-animate-group="hero-copy">
        <div class="sub-v2-hero__mobile-title" data-animate="hero-mobile-title" data-hero-animate>
          GROW
          <span>Your Business Digitally</span>
          <span>With Confidence</span>
        </div>

        <h1 class="sub-v2-hero__title" data-animate="hero-title" data-hero-animate>GROW</h1>
        <div class="sub-v2-hero__copy" data-animate="hero-copy" data-hero-animate>
          <p class="sub-v2-hero__lead">Your Business Digitally</p>
          <p class="sub-v2-hero__confidence">With Confidence</p>
        </div>

        <p class="sub-v2-hero__desc" data-animate="hero-desc" data-hero-animate>
          Reach new customers and boost your sales with tools designed to grow your business
        </p>

        <a href="#sub-v2-demo" class="sub-v2-hero__cta" data-animate="hero-cta" data-hero-animate>
          <span class="sub-v2-hero__cta-label">Get a demo</span>
          <span class="sub-v2-hero__cta-icon" aria-hidden="true">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M1.5 12.5L12.5 1.5M12.5 1.5H3.5M12.5 1.5V10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
        </a>
      </div>

      <div class="sub-v2-hero__visual" data-animate="fade-up">
        <img
          class="sub-v2-hero__visual-image"
          src="<?php echo esc_url($sub_v2['images']['hero']['visual']); ?>"
          alt="EPOS360 hero visual"
          loading="eager"
          decoding="async"
          fetchpriority="high">
      </div>

      <!-- Mobile only: visual image + CTA buttons -->
      <div class="sub-v2-hero__mobile-visual" data-animate="fade-up">
        <img
          class="sub-v2-hero__mobile-visual-image"
          src="<?php echo esc_url($sub_v2['images']['hero']['visual_mobile']); ?>"
          alt="EPOS360 hero visual"
          loading="eager"
          decoding="async"
          width="385"
          height="426"
          fetchpriority="high">
      </div>
      <div class="sub-v2-hero__mobile-actions">
        <a href="<?php echo esc_url($sub_v2['contact_sales_url']); ?>" class="sub-v2-header__button sub-v2-header__button--ghost">Contact Sales</a>
        <a href="#sub-v2-demo-modal" class="sub-v2-header__button sub-v2-header__button--primary" data-sub-v2-demo-modal-open>Get a Demo</a>
      </div>
    </div>
  </div>
</section>
