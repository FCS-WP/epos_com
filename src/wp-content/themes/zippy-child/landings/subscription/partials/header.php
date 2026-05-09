<?php
if (! defined('ABSPATH')) exit;
$sub_v2 = $data ?? array();

// Logo from content.json, with the WP custom logo (Customizer → Site Identity)
// as fallback.
$logo_url = $sub_v2['images']['header']['logo'] ?? '';
if (! $logo_url) {
    $custom_logo_id = (int) get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $logo_url = (string) wp_get_attachment_image_url($custom_logo_id, 'full');
    }
}
?><header class="sub-v2-header" aria-label="Landing page header">
  <div class="sub-shell">
    <div class="sub-v2-header__inner">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="sub-v2-header__brand" aria-label="EPOS home">
        <?php if ($logo_url): ?>
          <img
            class="sub-v2-header__brand-logo"
            src="<?php echo esc_url($logo_url); ?>"
            alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
            loading="eager"
            decoding="async">
        <?php endif; ?>
      </a>

      <nav class="sub-v2-header__nav" aria-label="Landing page navigation">
        <a href="<?php echo esc_url($sub_v2['contact_sales_url']); ?>" class="sub-v2-header__button sub-v2-header__button--ghost">Contact Sales</a>
        <a href="#sub-v2-demo-modal" class="sub-v2-header__button sub-v2-header__button--primary" data-sub-v2-demo-modal-open>Get a Demo</a>
      </nav>
    </div>
  </div>
</header>
