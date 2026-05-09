<?php
if (! defined('ABSPATH')) exit;
$cta = landing_content()['footer_cta'] ?? array();
?>
<section class="landing__footer-cta" data-animate="fade-up">
    <?php if (! empty($cta['headline'])): ?>
        <h2 class="landing__footer-cta-headline"><?php echo esc_html($cta['headline']); ?></h2>
    <?php endif; ?>

    <?php if (! empty($cta['body'])): ?>
        <p class="landing__footer-cta-body"><?php echo esc_html($cta['body']); ?></p>
    <?php endif; ?>

    <div class="landing__footer-cta-actions">
        <?php if (! empty($cta['primary_label']) && ! empty($cta['primary_url'])): ?>
            <a class="landing__btn landing__btn--primary" href="<?php echo esc_url($cta['primary_url']); ?>">
                <?php echo esc_html($cta['primary_label']); ?>
            </a>
        <?php endif; ?>

        <?php if (! empty($cta['secondary_label']) && ! empty($cta['secondary_url'])): ?>
            <a class="landing__btn landing__btn--secondary" href="<?php echo esc_url($cta['secondary_url']); ?>">
                <?php echo esc_html($cta['secondary_label']); ?>
            </a>
        <?php endif; ?>
    </div>
</section>
