<?php require __DIR__ . '/template-data.php'; ?>

<!-- Section: Everything -->
<section class="sub-v2-everything">
  <div class="sub-shell">
    <div class="sub-v2-everything__head">
      <p class="sub-v2-everything__eyebrow">Get your trial today</p>
      <h2 class="sub-v2-everything__title">
        Everything You Need
        <span>All In One Place</span>
      </h2>
    </div>

    <div class="sub-v2-everything__layout">
      <div class="sub-v2-everything__visual">
        <img
          class="sub-v2-everything__mockup"
          src="<?php echo esc_url($sub_v2['mockup_image']); ?>"
          alt="EPOS360 app mockup"
          loading="lazy"
          decoding="async">
      </div>

      <div class="sub-v2-everything__list">
        <article class="sub-v2-everything__item">
          <div class="sub-v2-everything__icon">
            <img src="<?php echo esc_url($sub_v2['qr_image']); ?>" alt="" loading="lazy" decoding="async">
          </div>
          <div class="sub-v2-everything__copy">
            <h3>Multi-platform order management</h3>
            <p>Stop juggling multiple tablets and the stress of missed orders. EPOS360 brings your shop&rsquo;s online orders, Foodpanda, and GrabFood into one manageable system.</p>
          </div>
        </article>

        <article class="sub-v2-everything__item">
          <div class="sub-v2-everything__icon">
            <img src="<?php echo esc_url($sub_v2['grow_sales_image']); ?>" alt="" loading="lazy" decoding="async">
          </div>
          <div class="sub-v2-everything__copy">
            <h3>Stress-free operations</h3>
            <p>We simplify your technology so you can spend less time worrying about apps and more time doing what you love.</p>
          </div>
        </article>

        <article class="sub-v2-everything__item">
          <div class="sub-v2-everything__icon">
            <img src="<?php echo esc_url($sub_v2['delivery_image']); ?>" alt="" loading="lazy" decoding="async">
          </div>
          <div class="sub-v2-everything__copy">
            <h3>No extra delivery charges</h3>
            <p>No added fees on deliveries*<br>*EPOS360 does not charge you extra for the orders you receive.</p>
          </div>
        </article>
      </div>
    </div>
  </div>
</section>