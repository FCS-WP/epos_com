<?php
function footer_shortcode()
{

    $current_url = $_SERVER['REQUEST_URI'];

    if (trim($current_url, '/') === 'my') {
        return do_shortcode('[block id="footer-malaysia"]');
    } else {
        return do_shortcode('[block id="footer"]');
    }
}
add_shortcode('custom_footer', 'footer_shortcode');



