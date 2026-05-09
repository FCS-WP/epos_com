<?php
if (! defined('ABSPATH')) exit;
$sub_v2 = $data ?? array();

$sub_v2_testimonials = [
  [
    'copy' => 'BlueTap has made payments very convenient for our customers. The instant sound notification and payment (amount) display features on EPOS give us confidence that every transaction is secured and well received. Their technical team is also very responsive where any issues are usually resolved by the next day.',
    'name' => 'DurianBB Park',
    'role' => 'DurianBB International Sdn Bhd',
    'image' => $sub_v2['images']['testimonials']['durianbb'],
    'avatar' => $sub_v2['images']['testimonials']['avatar_1'],
  ],
  [
    'copy' => 'Keeping a family business going these days isn&rsquo;t easy, but EPOS has really helped us stay modern without losing our roots. The team was patient and made sure we were comfortable with the tech from day one. I use it with confidence every day now, and being able to check our sales quickly on the EPOS360 Mini Program takes a huge weight off my shoulders. It&rsquo;s a simple but powerful tool that just makes running the shop a lot easier.',
    'name' => 'Dexon Button Shop',
    'role' => 'Owner',
    'image' => $sub_v2['images']['testimonials']['dexon'],
    // 'avatar' => $sub_v2['images']['testimonials']['avatar_2'],
  ],
  [
    'copy' => 'Coffee has always been my passion, and opening my own shop was a dream come true. EPOS360 and BlueTap made the tech side so much easier than I expected. The activation was straightforward, no hassles at all. What surprised me most was the POS feature inside the EPOS360 app, but now I use it daily to check sales and peak times. The AI even tells me my slowest times so I can plan better. Checking the EPOS360 dashboard has become part of my everyday routine.',
    'name' => 'AJOKAIDO Coffee',
    'role' => 'Owner',
    'image' => $sub_v2['images']['testimonials']['ajokaido'],
    // 'avatar' => $sub_v2['images']['testimonials']['avatar_3'],
  ],
];
?>

<!-- Section: Testimonials -->
<section class="sub-v2-testimonials">
  <div class="sub-shell">
    <div class="sub-v2-testimonials__head" data-animate-group="section-head">
      <p class="sub-v2-testimonials__eyebrow sub-v2-kicker">
        <img class="sub-v2-kicker__icon" src="<?php echo esc_url($sub_v2['section_kicker_icon']); ?>" alt="" aria-hidden="true" decoding="async">
        <span>Testimonial</span>
      </p>
      <h2 class="sub-v2-testimonials__title sub-v2-section-title">
        Trusted by
        <span>13,000+ Merchants</span>
      </h2>
      <p class="sub-v2-testimonials__desc">
        Built on the same platform 13,000+ Malaysian merchants already trust.
      </p>
    </div>

    <div class="sub-v2-testimonials__slider-wrap" data-animate="fade-up">
      <div class="sub-v2-testimonials__grid" data-sub-v2-testimonials-slider>
        <?php foreach ($sub_v2_testimonials as $testimonial) : ?>
          <div class="sub-v2-testimonials__slide">
          <article class="sub-v2-testimonials__card">
            <img class="sub-v2-testimonials__card-bg" src="<?php echo esc_url($testimonial['image']); ?>" alt="" aria-hidden="true" loading="lazy" decoding="async">
            <div class="sub-v2-testimonials__card-overlay"></div>
            <span class="sub-v2-testimonials__quote sub-v2-testimonials__quote--start">"</span>
            <p><?php echo wp_kses_post($testimonial['copy']); ?></p>
            <span class="sub-v2-testimonials__quote sub-v2-testimonials__quote--end">"</span>
            <div class="sub-v2-testimonials__meta">
              <div class="sub-v2-testimonials__meta-text">
                <?php if (!empty($testimonial['avatar'])) : ?>
                  <img class="sub-v2-testimonials__avatar" src="<?php echo esc_url($testimonial['avatar']); ?>" alt="" aria-hidden="true" loading="lazy" decoding="async">
                <?php endif; ?>
                <div>
                  <strong><?php echo esc_html($testimonial['name']); ?></strong>
                  <span><?php echo esc_html($testimonial['role']); ?></span>
                </div>
              </div>
            </div>
          </article>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="sub-v2-tools__dots" data-sub-v2-testimonials-dots></div>
    </div>
  </div>
</section>
