<?php
add_action('wp_footer', function () {
    if (is_page('my/home')) {
        ?>
            <div id="BlueTap-Promo" class="bluetap-promo">
                <a href="/my/bluetap/" rel="noopener noreferrer">
                    <div class="bluetap-promo-overlay"></div>

                    <div class="bluetap-promo-content">
                        <button class="bluetap-promo-close" aria-label="Close popup">×</button>

                        <?php echo do_shortcode('[block id="bluetap-promo"]'); ?>
                    </div>
                </a>
            </div>
        <?php
    }
});
