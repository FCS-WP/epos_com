<?php
if (! defined('ABSPATH')) exit;
$features = landing_content()['features'] ?? array();
$items    = $features['items'] ?? array();
if (empty($items)) return;
?>
<section class="landing__features" data-animate="fade-up">
    <?php if (! empty($features['section_title'])): ?>
        <h2 class="landing__section-title"><?php echo esc_html($features['section_title']); ?></h2>
    <?php endif; ?>

    <ul class="landing__features-grid">
        <?php foreach ($items as $item): ?>
            <li class="landing__features-item" data-animate="fade-up">
                <?php if (! empty($item['image_id'])): ?>
                    <div class="landing__features-icon">
                        <?php landing_image($item['image_id'], 'medium', array('alt' => esc_attr($item['title'] ?? ''))); ?>
                    </div>
                <?php endif; ?>

                <?php if (! empty($item['title'])): ?>
                    <h3 class="landing__features-title"><?php echo esc_html($item['title']); ?></h3>
                <?php endif; ?>

                <?php if (! empty($item['body'])): ?>
                    <p class="landing__features-body"><?php echo esc_html($item['body']); ?></p>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
