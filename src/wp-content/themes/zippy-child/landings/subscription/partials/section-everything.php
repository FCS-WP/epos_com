<?php
if (! defined('ABSPATH')) exit;
$sub_v2 = $data ?? array();
?>
<!-- Section: Everything -->
<section class="sub-v2-everything">
  <div class="sub-v2-everything__shell">
    <div class="sub-v2-everything__head" data-animate-group="section-head">
      <p class="sub-v2-everything__eyebrow sub-v2-kicker">
        <img class="sub-v2-kicker__icon" src="<?php echo esc_url($sub_v2['section_kicker_icon']); ?>" alt="" aria-hidden="true" decoding="async">
        <span>Multi-Platform</span>
      </p>
      <h2 class="sub-v2-everything__title sub-v2-section-title">
        Everything You Need
        <span>All In One Place</span>
      </h2>
    </div>

    <div class="sub-v2-everything__layout">
      <div class="sub-v2-everything__visual" data-animate="fade-up">
        <img
          class="sub-v2-everything__mockup"
          src="<?php echo esc_url($sub_v2['images']['everything']['mockup']); ?>"
          alt="EPOS360 app mockup"
          loading="lazy"
          decoding="async">
      </div>

      <div class="sub-v2-everything__list" data-animate-group="everything-items" data-animate="stagger">
        <article class="sub-v2-everything__item" data-animate="stagger-item" data-stagger-item>
          <div class="sub-v2-everything__icon">
            <img src="<?php echo esc_url($sub_v2['images']['everything']['qr']); ?>" alt="" loading="lazy" decoding="async">
          </div>
          <div class="sub-v2-everything__copy">
            <h3>Boost sales across every platform</h3>
            <p>Instead of juggling tablets and missing orders, use one system to manage your shop, Foodpanda, and GrabFood. By simplifying order management, you can serve more customers and grow your business without the stress.</p>
          </div>
        </article>

        <article class="sub-v2-everything__item" data-animate="stagger-item" data-stagger-item>
          <div class="sub-v2-everything__icon">
            <img src="<?php echo esc_url($sub_v2['images']['everything']['grow_sales']); ?>" alt="" loading="lazy" decoding="async">
          </div>
          <div class="sub-v2-everything__copy">
            <h3>Stress-free operations</h3>
            <p>We simplify your technology so you can spend less time worrying about apps and more time growing your business.</p>
          </div>
        </article>

        <article class="sub-v2-everything__item" data-animate="stagger-item" data-stagger-item>
          <div class="sub-v2-everything__icon">
            <img src="<?php echo esc_url($sub_v2['images']['everything']['delivery']); ?>" alt="" loading="lazy" decoding="async">
          </div>
          <div class="sub-v2-everything__copy">
            <h3>No extra delivery charges</h3>
            <p>No added fees on deliveries. EPOS360 does not charge you extra for the orders you receive.</p>
          </div>
        </article>
      </div>
    </div>
  </div>
</section>
