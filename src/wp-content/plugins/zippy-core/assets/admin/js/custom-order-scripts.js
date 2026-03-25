jQuery(function ($) {
    $('#toplevel_page_woocommerce a').each(function () {
        const href = $(this).attr('href') || '';

        if (
            href.includes('post_type=shop_order') ||
            href.endsWith('wc-orders')
        ) {
            $(this).closest('li').hide();
        }
    });
});