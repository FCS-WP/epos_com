<?php
if (! defined('ABSPATH')) exit;
$sub_v2 = $data ?? array();

$sub_v2_tools = [
  [
    'title'   => 'Food Delivery In One Place',
    'desc'    => 'Stop juggling tablets and manage orders from one single screen.',
    'bullets' => [
      'Seamlessly connect your orders, POS, and delivery tracking',
      'Reach more customers, grow your business, and earn more profits',
    ],
    'image'   => $sub_v2['images']['tools']['food_delivery'],
  ],
  [
    'title'   => 'Direct Table QR Order',
    'desc'    => 'Make dining easier for everyone with scan-to-order technology.',
    'bullets' => [
      'Spend less on staff costs by letting customers order from their table',
      'Boost sales by making it easy for customers to add items any time',
    ],
    'image'   => $sub_v2['images']['tools']['table_qr'],
  ],
  [
    'title'   => 'Your Own Online Store',
    'desc'    => 'Give your customers a better way to order and pay online.',
    'bullets' => [
      'Make more profit by taking direct orders instead of paying high fees',
      'Reach more customers with easy order-ahead and pickup service',
    ],
    'image'   => $sub_v2['images']['tools']['online_store'],
  ],
  [
    'title'   => 'Loyalty And Rewards',
    'desc'    => 'Make customers feel like part of the family with customisable programmes.',
    'bullets' => [
      'Turn one-time visitors into regulars',
      'Create rewards that fit your products',
    ],
    'image'   => $sub_v2['images']['tools']['loyalty'],
  ],
  [
    'title'   => 'All-in-One Payment Hub',
    'desc'    => 'Everything you need to accept payments and grow your business.',
    'bullets' => [
      'Welcome and serve every customer no matter how they pay.',
      'Get dedicated support for the one device that accepts it all.',
    ],
    'image'   => $sub_v2['images']['tools']['google_business'],
  ],
  [
    'title'   => 'Smart Insights for Your Business',
    'desc'    => 'Grow your profit without the guesswork using the AI assistant.',
    'bullets' => [
      'Understand what is working for your shop and what isn&rsquo;t',
      'Get helpful suggestions on the best ways to grow',
    ],
    'image'   => $sub_v2['images']['tools']['smart_insights'],
  ],
];
?>

<!-- Section: Tools -->
<section class="sub-v2-tools">
  <div class="sub-shell">
    <div class="sub-v2-tools__head" data-animate-group="section-head">
      <p class="sub-v2-tools__eyebrow sub-v2-kicker">
        <img class="sub-v2-kicker__icon" src="<?php echo esc_url($sub_v2['section_kicker_icon']); ?>" alt="" aria-hidden="true" decoding="async">
        <span>Limited time offer</span>
      </p>
      <h2 class="sub-v2-tools__title sub-v2-section-title">
        The Right Tools
        <span>For Businesses That Want To Grow</span>
      </h2>
    </div>
  </div>

  <div class="sub-v2-tools__slider-wrap" data-animate-slider>
    <div class="sub-v2-tools__grid" data-sub-v2-tools-slider>
      <?php foreach ($sub_v2_tools as $i => $tool) : ?>
        <div class="sub-v2-tools__slide">
          <article class="sub-v2-tools__card sub-v2-tools__card--<?php echo $i + 1; ?>">
            <div class="sub-v2-tools__card-content">
              <div class="sub-v2-tools__card-text">
                <div class="sub-v2-tools__card-inner-text">
                  <h3><?php echo esc_html($tool['title']); ?></h3>
                  <p><?php echo esc_html($tool['desc']); ?></p>
                  <?php if (!empty($tool['bullets'])) : ?>
                    <ul>
                      <?php foreach ($tool['bullets'] as $bullet) : ?>
                        <li><?php echo wp_kses_post($bullet); ?></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </div>
                <div class="sub-v2-tools__card-inner-cta">
                  <a href="#sub-v2-demo" class="sub-v2-tools__button">Get a demo</a>
                </div>
              </div>
              <img class="sub-v2-tools__card-bg" src="<?php echo esc_url($tool['image']); ?>" alt="" aria-hidden="true" loading="lazy" decoding="async" width="536" height="360">

            </div>
          </article>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="sub-v2-tools__dots" data-sub-v2-tools-dots></div>
  </div>
</section>
