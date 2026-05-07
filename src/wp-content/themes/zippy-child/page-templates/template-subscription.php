<?php
/*
Template Name: Subscription V2
*/

$sub_icon_base_uri = trailingslashit(get_stylesheet_directory_uri()) . 'assets/images/subscription';
$sub_icon = static function ($slot, $class = 'sub-icon-placeholder') use ($sub_icon_base_uri) {
  printf(
    '<img class="%1$s" src="%2$s" alt="" aria-hidden="true" loading="lazy" decoding="async" data-icon-slot="%3$s">',
    esc_attr($class),
    esc_url($sub_icon_base_uri . '/icon-placeholder.svg'),
    esc_attr($slot)
  );
};
$sub_svg_data_uri = static function ($svg) {
  return 'data:image/svg+xml;base64,' . base64_encode($svg);
};

$sub_v2_hero_image = '/wp-content/uploads/2026/05/KV-Website-copy-1-1.webp';
$sub_v2_mockup_image = '/wp-content/uploads/2026/05/KV-3-copy-1-1.webp';
$sub_v2_qr_image = "/wp-content/uploads/2026/05/Layer_1-4.webp";
$sub_v2_delivery_image = "/wp-content/uploads/2026/05/Layer_1-6.webp";
$sub_v2_delivery_man_image = "/wp-content/uploads/2026/05/Layer_1-6.webp";
$sub_v2_grow_sales_image = "/wp-content/uploads/2026/05/Layer_1-5.webp";
$sub_v2_left_merchant_image = "/wp-content/uploads/2026/05/Layer_1-2-1.webp";
$sub_v2_right_merchant_image = "/wp-content/uploads/2026/05/Layer_1-3.webp";

// Testimonials Avatars
$sub_v2_avatar_1 = '/wp-content/uploads/2026/05/921c1a.png';
$sub_v2_avatar_2 = '/wp-content/uploads/2026/05/921c1a.png';
$sub_v2_avatar_3 = '/wp-content/uploads/2026/05/avatar-3.webp';

$sub_v2_logo_epos =  '/wp-content/uploads/2026/05/Group-2117133424-1.webp';
$sub_v2_logo_alipay =  '/wp-content/uploads/2026/05/Isolation_Mode-1.webp';
$sub_v2_logo_tng =  '/wp-content/uploads/2026/05/Isolation_Mode-5.webp';
$sub_v2_logo_duitnow =  '/wp-content/uploads/2026/05/Isolation_Mode-6.webp';
$sub_v2_logo_tng_small =  '/wp-content/uploads/2026/05/Isolation_Mode-5.webp';
$sub_v2_logo_mydebit =  '/wp-content/uploads/2026/05/Isolation_Mode-4.webp';
$sub_v2_logo_visa =  '/wp-content/uploads/2026/05/Isolation_Mode-3.webp';
$sub_v2_logo_mastercard =  '/wp-content/uploads/2026/05/Isolation_Mode-2.webp';
$sub_v2_header_logo = the_custom_logo();;
$sub_v2_header_logo = $sub_v2_header_logo ?: "https://www.epos.com.sg/wp-content/uploads/2025/12/EPOS_Full-Color.webp";
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?> class="<?php flatsome_html_classes(); ?>">

<head>
  <meta charset="<?php bloginfo('charset'); ?>" />
  <link rel="profile" href="http://gmpg.org/xfn/11" />
  <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

  <?php wp_head(); ?>
</head>

<body <?php body_class('page-template-subscription-v2'); ?>>
  <?php do_action('flatsome_after_body_open'); ?>
  <?php wp_body_open(); ?>

  <?php do_action('flatsome_before_page'); ?>
  <?php do_action('flatsome_after_header'); ?>

  <div id="wrapper">
    <?php while (have_posts()) : the_post(); ?>
      <main id="main" class="sub-page sub-page--v2">

        <header class="sub-v2-header" aria-label="Landing page header">
          <div class="sub-shell">
            <div class="sub-v2-header__inner">
              <a href="<?php echo esc_url(home_url('/')); ?>" class="sub-v2-header__brand" aria-label="EPOS home">
                <img
                  class="sub-v2-header__brand-logo"
                  src="<?php echo esc_url($sub_v2_header_logo); ?>"
                  alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                  loading="eager"
                  decoding="async">
              </a>

              <nav class="sub-v2-header__nav" aria-label="Landing page navigation">
                <a href="#sub-v2-grow" class="sub-v2-header__link">Download</a>
                <a href="#sub-v2-faq" class="sub-v2-header__button sub-v2-header__button--ghost">Contact Sales</a>
                <a href="#sub-v2-faq" class="sub-v2-header__button sub-v2-header__button--primary">Get a Demo</a>
              </nav>
            </div>
          </div>
        </header>

        <!-- Section: Hero -->
        <section class="sub-v2-hero">
          <div class="sub-shell">
            <div class="sub-v2-hero__inner">
              <div class="sub-v2-hero__content">
                <h1 class="sub-v2-hero__title">GROW</h1>
                <div class="sub-v2-hero__copy">
                  <p class="sub-v2-hero__lead">your business digitally with</p>
                  <p class="sub-v2-hero__confidence">confidence</p>
                  <div class="sub-v2-hero__accent-row">
                    <span class="sub-v2-hero__accent-line" aria-hidden="true"></span>
                    <p class="sub-v2-hero__accent">with EPOS360</p>
                  </div>
                </div>

                <p class="sub-v2-hero__desc">
                  Win customers, boost sales, and run your business with confidence.
                </p>

                <a href="#sub-v2-partnership" class="sub-v2-hero__cta">
                  <span class="sub-v2-hero__cta-label">Start a free trial</span>
                  <span class="sub-v2-hero__cta-icon" aria-hidden="true">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M1.5 12.5L12.5 1.5M12.5 1.5H3.5M12.5 1.5V10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                  </span>
                </a>
              </div>

              <div class="sub-v2-hero__visual">
                <img
                  class="sub-v2-hero__visual-image"
                  src="<?php echo esc_url($sub_v2_hero_image); ?>"
                  alt="EPOS360 hero visual"
                  loading="eager"
                  decoding="async">
              </div>
            </div>
          </div>
        </section>

        <!-- Section: Partnership -->
        <section class="sub-v2-partnership" id="sub-v2-partnership">
          <div class="sub-shell">
            <p class="sub-v2-partnership__eyebrow">Partnership With TNG Digital</p>

            <div class="sub-v2-partnership__brand-row" aria-label="Partner brands">
              <div class="sub-v2-partnership__brand">
                <img
                  class="sub-v2-partnership__brand-logo sub-v2-partnership__brand-logo--epos"
                  src="<?php echo esc_url($sub_v2_logo_epos); ?>"
                  alt="EPOS360"
                  loading="lazy"
                  decoding="async">
              </div>

            </div>

            <div class="sub-v2-partnership__divider"></div>

            <div class="sub-v2-partnership__body">
              <p class="sub-v2-partnership__desc">
                EPOS360 works seamlessly with your<br>
                Touch 'n Go eWallet merchant dashboard.
              </p>
            </div>

            <div class="sub-v2-partnership__divider"></div>

            <p class="sub-v2-partnership__payments-copy">
              Accept popular payments with one simple system.
            </p>

            <div class="sub-v2-partnership__payments" aria-label="Supported payments">
              <div class="sub-v2-partnership__payment">
                <img class="sub-v2-partnership__payment-logo sub-v2-partnership__payment-logo--alipay" src="<?php echo esc_url($sub_v2_logo_alipay); ?>" alt="Alipay+" loading="lazy" decoding="async">
              </div>
              <div class="sub-v2-partnership__payment">
                <img class="sub-v2-partnership__payment-logo sub-v2-partnership__payment-logo--duitnow" src="<?php echo esc_url($sub_v2_logo_duitnow); ?>" alt="DuitNow" loading="lazy" decoding="async">
              </div>
              <div class="sub-v2-partnership__payment">
                <img class="sub-v2-partnership__payment-logo sub-v2-partnership__payment-logo--tngpay" src="<?php echo esc_url($sub_v2_logo_tng_small); ?>" alt="Touch 'n Go eWallet" loading="lazy" decoding="async">
              </div>
              <div class="sub-v2-partnership__payment">
                <img class="sub-v2-partnership__payment-logo sub-v2-partnership__payment-logo--mydebit" src="<?php echo esc_url($sub_v2_logo_mydebit); ?>" alt="MyDebit" loading="lazy" decoding="async">
              </div>
              <div class="sub-v2-partnership__payment">
                <img class="sub-v2-partnership__payment-logo sub-v2-partnership__payment-logo--visa" src="<?php echo esc_url($sub_v2_logo_visa); ?>" alt="Visa" loading="lazy" decoding="async">
              </div>
              <div class="sub-v2-partnership__payment">
                <img class="sub-v2-partnership__payment-logo sub-v2-partnership__payment-logo--mastercard" src="<?php echo esc_url($sub_v2_logo_mastercard); ?>" alt="Mastercard" loading="lazy" decoding="async">
              </div>
            </div>
          </div>
        </section>

        <!-- Section: Tools -->
        <section class="sub-v2-tools">
          <div class="sub-shell">
            <div class="sub-v2-tools__head">
              <p class="sub-v2-tools__eyebrow">Limited time offer</p>
              <h2 class="sub-v2-tools__title">
                The Right Tools
                <span>For Small Businesses With Big Plans</span>
              </h2>
            </div>
          </div>

          <div class="sub-v2-tools__slider-wrap">
            <div class="sub-v2-tools__grid" data-sub-v2-tools-slider>
              <article class="sub-v2-tools__card sub-v2-tools__card--delivery">
                <h3>Food Delivery In One Place</h3>
                <p>Skip the hassle and manage orders from one single screen.</p>
                <ul>
                  <li>Reduce clutter & integrate multiple platforms on one device.</li>
                  <li>Manage all your orders on one device.</li>
                </ul>
                <a href="#sub-v2-grow" class="sub-v2-tools__button">Get Started</a>

                <div class="sub-v2-tools__visual sub-v2-tools__visual--delivery" aria-hidden="true">
                  <img class="sub-v2-tools__visual-main" src="<?php echo esc_url($sub_v2_grow_sales_image); ?>" alt="" loading="lazy" decoding="async">
                </div>
              </article>

              <article class="sub-v2-tools__card sub-v2-tools__card--qr">
                <h3>Direct Table QR Order</h3>
                <p>Let your customers scan-to-order directly from their table.</p>
                <ul>
                  <li>Shrink the queue with QR code ordering.</li>
                  <li>Move the crowd faster with instant checkouts.</li>
                </ul>
                <a href="#sub-v2-faq" class="sub-v2-tools__button">Learn More</a>
              </article>

              <article class="sub-v2-tools__card sub-v2-tools__card--store">
                <h3>Your Own Online Store</h3>
                <p>Customers can order and pay without adding more work for your team.</p>
                <ul>
                  <li>Keep more profit by taking direct orders for pickup and dine-in.</li>
                  <li>Stay ahead of the rush with orders that land straight in your kitchen.</li>
                </ul>
                <a href="#sub-v2-grow" class="sub-v2-tools__button">Get Started</a>
              </article>
            </div>

            <div class="sub-v2-tools__nav">
              <button type="button" class="sub-v2-tools__nav-btn sub-v2-tools__nav-btn--prev" aria-label="Previous tool">&#8249;</button>
              <button type="button" class="sub-v2-tools__nav-btn sub-v2-tools__nav-btn--next" aria-label="Next tool">&#8250;</button>
            </div>
          </div>
        </section>

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
                  src="<?php echo esc_url($sub_v2_mockup_image); ?>"
                  alt="EPOS360 app mockup"
                  loading="lazy"
                  decoding="async">
              </div>

              <div class="sub-v2-everything__list">
                <article class="sub-v2-everything__item">
                  <div class="sub-v2-everything__icon">
                    <img src="<?php echo esc_url($sub_v2_qr_image); ?>" alt="" loading="lazy" decoding="async">
                  </div>
                  <div class="sub-v2-everything__copy">
                    <h3>Multi-platform order management</h3>
                    <p>Stop juggling multiple tablets and the stress of missed orders. EPOS360 brings your shop’s online orders, Foodpanda, and GrabFood into one manageable system.</p>
                  </div>
                </article>

                <article class="sub-v2-everything__item">
                  <div class="sub-v2-everything__icon">
                    <img src="<?php echo esc_url($sub_v2_grow_sales_image); ?>" alt="" loading="lazy" decoding="async">
                  </div>
                  <div class="sub-v2-everything__copy">
                    <h3>Stress-free operations</h3>
                    <p>We simplify your technology so you can spend less time worrying about apps and more time doing what you love.</p>
                  </div>
                </article>

                <article class="sub-v2-everything__item">
                  <div class="sub-v2-everything__icon">
                    <img src="<?php echo esc_url($sub_v2_delivery_image); ?>" alt="" loading="lazy" decoding="async">
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

        <!-- Section: Testimonials -->
        <section class="sub-v2-testimonials">
          <div class="sub-shell">
            <div class="sub-v2-testimonials__head">
              <p class="sub-v2-testimonials__eyebrow">Testimonial</p>
              <h2 class="sub-v2-testimonials__title">
                Trusted by
                <span>6,000+ Merchants</span>
              </h2>
              <p class="sub-v2-testimonials__desc">
                Built on the same platform 6,000+ Malaysian merchants already trust.
              </p>
            </div>

            <div class="sub-v2-testimonials__grid">
              <article class="sub-v2-testimonials__card">
                <span class="sub-v2-testimonials__quote sub-v2-testimonials__quote--start">“</span>
                <p>BlueTap has made payments very convenient for our customers. The instant sound notification and payment (amount) display features on EPOS give us confidence that every transaction is secured and well received. Their technical team is also very responsive where any issues are usually resolved by the next day.</p>
                <div class="sub-v2-testimonials__badge">
                  <img src="<?php echo esc_url($sub_v2_avatar_1); ?>" alt="DurianBB Park Badge" loading="lazy" decoding="async">
                </div>
                <span class="sub-v2-testimonials__quote sub-v2-testimonials__quote--end">”</span>
                <div class="sub-v2-testimonials__meta">
                  <strong>DurianBB Park</strong>
                  <span>DurianBB International Sdn Bhd</span>
                </div>
              </article>

              <article class="sub-v2-testimonials__card">
                <span class="sub-v2-testimonials__quote sub-v2-testimonials__quote--start">“</span>
                <p>Keeping a family business going these days isn’t easy, but EPOS has really helped us stay modern without losing our roots. The team was patient and made sure we were comfortable with the tech from day one. I use it with confidence every day now, and being able to check our sales quickly on the EPOS360 Mini Program takes a huge weight off my shoulders. It’s a simple but powerful tool that just makes running the shop a lot easier.</p>
                <div class="sub-v2-testimonials__avatar">
                  <!-- Empty or placeholder image to match grey circle in image -->
                </div>
                <span class="sub-v2-testimonials__quote sub-v2-testimonials__quote--end">”</span>
                <div class="sub-v2-testimonials__meta">
                  <strong>Dexon Button Shop</strong>
                </div>
              </article>

              <article class="sub-v2-testimonials__card">
                <span class="sub-v2-testimonials__quote sub-v2-testimonials__quote--start">“</span>
                <p>Coffee has always been my passion, and opening my own shop was a dream come true. EPOS360 and BlueTap made the tech side so much easier than I expected. The activation was straightforward, no hassles at all. What surprised me most was the POS feature inside the EPOS360 app &mdash; that wasn&rsquo;t even why I bought it, but now I use it daily to check sales and peak times. The AI even tells me my slowest times so I can plan better. Checking the EPOS360 dashboard has become part of my everyday routine.</p>
                <div class="sub-v2-testimonials__badge">
                  <span style="font-size: 11px; font-weight: 800; color: #000; letter-spacing: -0.02em;">AJOKAIDO</span>
                </div>
                <span class="sub-v2-testimonials__quote sub-v2-testimonials__quote--end">”</span>
                <div class="sub-v2-testimonials__meta">
                  <strong>AJOKAIDO Coffee</strong>
                </div>
              </article>
            </div>
          </div>
        </section>

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
                src="<?php echo esc_url($sub_v2_left_merchant_image); ?>"
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
                <a href="#sub-v2-faq" class="sub-v2-grow__button sub-v2-grow__button--ghost">Contact Sales</a>
                <a href="#sub-v2-faq" class="sub-v2-grow__button sub-v2-grow__button--primary">Get a Demo</a>
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
                src="<?php echo esc_url($sub_v2_right_merchant_image); ?>"
                alt="Merchant image"
                loading="lazy"
                decoding="async">
            </div>
          </div>
        </section>

        <!-- Section: FAQ -->
        <section class="sub-v2-faq" id="sub-v2-faq" data-sub-v2-faq>
          <div class="sub-shell">
            <div class="sub-v2-faq__layout">
              <div class="sub-v2-faq__intro">
                <p class="sub-v2-faq__eyebrow">HARDWARE &amp; PAYMENTS FAQ</p>
                <h2 class="sub-v2-faq__title">Frequently asked questions</h2>
              </div>

              <div class="sub-v2-faq__list">
                <div class="sub-v2-faq__item">
                  <button type="button" class="sub-v2-faq__trigger" aria-expanded="true">
                    <span>Can I just buy the the Bluetap or Series 1 device directly without a subscription?</span>
                    <span class="sub-v2-faq__icon" aria-hidden="true"></span>
                  </button>
                  <div class="sub-v2-faq__body">
                    <p>All our hardware is designed to be used with EPOS360, and cannot be used or sold independently without a subscription. A free Soundbox Series 1 is provided with every EPOS360 subscription. An upgrade to the Bluetap is available for a one time top up fee of 150MYR.</p>
                  </div>
                </div>

                <div class="sub-v2-faq__item">
                  <button type="button" class="sub-v2-faq__trigger" aria-expanded="false">
                    <span>Can this accept all the payment methods my customers use?</span>
                    <span class="sub-v2-faq__icon" aria-hidden="true"></span>
                  </button>
                  <div class="sub-v2-faq__body" hidden>
                    <p>Yes.</p>
                    <p>We support all major payment methods in Malaysia, including DuitNow QR (all local banking apps), e-wallets (Touch ’n Go), WeChat Pay, and Alipay. BlueTap also supports credit and debit cards (Visa &amp; Mastercard).</p>
                  </div>
                </div>

                <div class="sub-v2-faq__item">
                  <button type="button" class="sub-v2-faq__trigger" aria-expanded="false">
                    <span>Will I get my money quickly?</span>
                    <span class="sub-v2-faq__icon" aria-hidden="true"></span>
                  </button>
                  <div class="sub-v2-faq__body" hidden>
                    <p>Yes.</p>
                    <p>Payments are credited instantly to your Touch ’n Go wallet. Bank transfers take 1 business day, while weekend or public holiday transactions are settled on the next working day.</p>
                  </div>
                </div>

                <div class="sub-v2-faq__item">
                  <button type="button" class="sub-v2-faq__trigger" aria-expanded="false">
                    <span>Is it reliable during busy hours?</span>
                    <span class="sub-v2-faq__icon" aria-hidden="true"></span>
                  </button>
                  <div class="sub-v2-faq__body" hidden>
                    <p>Yes.</p>
                    <p>Both Series 1 and BlueTap provide instant voice confirmation for successful payments and automatically verifies amounts received. Transactions can also be checked in the app if there are network issues.</p>
                  </div>
                </div>

                <div class="sub-v2-faq__item">
                  <button type="button" class="sub-v2-faq__trigger" aria-expanded="false">
                    <span>Does it help prevent fake payments or mistake?</span>
                    <span class="sub-v2-faq__icon" aria-hidden="true"></span>
                  </button>
                  <div class="sub-v2-faq__body" hidden>
                    <p>Yes.</p>
                    <p>We uses instant voice confirmation and dynamic QR codes (prefilled amounts) to reduce fake payment claims and incorrect entries.</p>
                  </div>
                </div>

                <div class="sub-v2-faq__item">
                  <button type="button" class="sub-v2-faq__trigger" aria-expanded="false">
                    <span>Is it easy to set up and use?</span>
                    <span class="sub-v2-faq__icon" aria-hidden="true"></span>
                  </button>
                  <div class="sub-v2-faq__body" hidden>
                    <p>Yes.</p>
                    <p>Both Series 1 and BlueTap is plug-and-play with a simple onboarding process. No technical skills are required—just a registered Touch ’n Go merchant account.</p>
                  </div>
                </div>

                <div class="sub-v2-faq__item">
                  <button type="button" class="sub-v2-faq__trigger" aria-expanded="false">
                    <span>Can it handle real working environments like stalls or events?</span>
                    <span class="sub-v2-faq__icon" aria-hidden="true"></span>
                  </button>
                  <div class="sub-v2-faq__body" hidden>
                    <p>Yes.</p>
                    <p>It is designed for cafés, hawker stalls, popups, and both indoor and outdoor use. The device is portable (4-8 hours battery), supports Wi-Fi or SIM, and works during power outages if charged.</p>
                  </div>
                </div>

                <div class="sub-v2-faq__item">
                  <button type="button" class="sub-v2-faq__trigger" aria-expanded="false">
                    <span>Can I track sales without a POS system?</span>
                    <span class="sub-v2-faq__icon" aria-hidden="true"></span>
                  </button>
                  <div class="sub-v2-faq__body" hidden>
                    <p>Yes.</p>
                    <p>We includes the EPOS360 app for real time sales tracking, transaction history, AI marketing and analytics.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

      </main>
    <?php endwhile; ?>
  </div>

  <?php do_action('flatsome_after_page'); ?>

  <?php wp_footer(); ?>
</body>

</html>