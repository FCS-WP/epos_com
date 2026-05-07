<?php require __DIR__ . '/template-data.php'; ?>

<header class="sub-v2-header" aria-label="Landing page header">
  <div class="sub-shell">
    <div class="sub-v2-header__inner">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="sub-v2-header__brand" aria-label="EPOS home">
        <img
          class="sub-v2-header__brand-logo"
          src="<?php echo esc_url($sub_v2['header_logo']); ?>"
          alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
          loading="eager"
          decoding="async">
      </a>

      <nav class="sub-v2-header__nav" aria-label="Landing page navigation">
        <a href="#sub-v2-grow" class="sub-v2-header__link">Download</a>
        <a href="<?php echo esc_url($sub_v2['contact_sales_url']); ?>" class="sub-v2-header__button sub-v2-header__button--ghost">Contact Sales</a>
        <a href="javascript:void(0)" class="sub-v2-header__button sub-v2-header__button--primary" data-sub-v2-demo-trigger>Get a Demo</a>
      </nav>
    </div>
  </div>
</header>