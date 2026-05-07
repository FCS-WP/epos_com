<?php require __DIR__ . '/template-data.php'; ?>

<!-- Section: Grow Plan -->
<section class="sub-v2-grow" id="sub-v2-grow">
  <div class="sub-shell">
    <div class="sub-v2-grow__head">
      <p class="sub-v2-grow__eyebrow">EPOS360</p>
      <h2 class="sub-v2-grow__title">
        Run A Smarter Business
        <span>With The Grow Plan</span>
      </h2>
    </div>
  </div>

  <div class="sub-v2-grow__stage">
    <div class="sub-v2-grow__photo sub-v2-grow__photo--left">
      <img
        src="<?php echo esc_url($sub_v2['left_merchant_image']); ?>"
        alt="Merchant image"
        loading="lazy"
        decoding="async">
    </div>

    <article class="sub-v2-grow__card">
      <p class="sub-v2-grow__plan-name">Grow</p>
      <div class="sub-v2-grow__price-row">
        <span class="sub-v2-grow__price">RM49</span>
        <span class="sub-v2-grow__price-suffix">/month</span>
      </div>
      <p class="sub-v2-grow__billing">Billed Monthly</p>

      <div class="sub-v2-grow__actions">
        <a href="<?php echo esc_url($sub_v2['contact_sales_url']); ?>" class="sub-v2-grow__button sub-v2-grow__button--ghost">Contact Sales</a>
        <a href="javascript:void(0)" class="sub-v2-grow__button sub-v2-grow__button--primary" data-sub-v2-demo-trigger>Get a Demo</a>
      </div>

      <ul class="sub-v2-grow__features">
        <li>Point of Sale system</li>
        <li>0% fees for QR payments</li>
        <li>Standard fees for card payments</li>
        <li>Food delivery integration (Foodpanda and GrabFood)</li>
        <li>Table QR code and online ordering</li>
        <li>Product management</li>
        <li>Customer management</li>
        <li>Customisable loyalty programme</li>
        <li>Reports and analytics</li>
        <li>AI assistant 24/7 customer support</li>
        <li>FREE Series 1 Soundbox (RM299 value)</li>
      </ul>
    </article>

    <div class="sub-v2-grow__photo sub-v2-grow__photo--right">
      <img
        src="<?php echo esc_url($sub_v2['right_merchant_image']); ?>"
        alt="Merchant image"
        loading="lazy"
        decoding="async">
    </div>
  </div>
</section>