<?php
if (! defined('ABSPATH')) exit;
$hero = landing_content()['hero'] ?? array();
?>
<section class="landing__hero" data-animate="fade-up">
    <div class="landing__hero-content">
        <?php if (! empty($hero['eyebrow'])): ?>
            <span class="landing__hero-eyebrow"><?php echo esc_html($hero['eyebrow']); ?></span>
        <?php endif; ?>

        <?php if (! empty($hero['headline'])): ?>
            <h1 class="landing__hero-headline"><?php echo esc_html($hero['headline']); ?></h1>
        <?php endif; ?>

        <?php if (! empty($hero['subheadline'])): ?>
            <p class="landing__hero-subheadline"><?php echo esc_html($hero['subheadline']); ?></p>
        <?php endif; ?>

        <?php if (! empty($hero['cta_label']) && ! empty($hero['cta_url'])): ?>
            <a class="landing__hero-cta" href="<?php echo esc_url($hero['cta_url']); ?>">
                <?php echo esc_html($hero['cta_label']); ?>
            </a>
        <?php endif; ?>
    </div>

    <?php if (! empty($hero['image_id'])): ?>
        <div class="landing__hero-image">
            <?php landing_image($hero['image_id'], 'large', array('class' => 'landing__hero-img')); ?>
        </div>
    <?php endif; ?>
</section>
