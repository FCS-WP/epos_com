<?php

function load_gsap_scrolltrigger_only_page()
{
    if (is_page('epos360')) {

        $version = time();

        /* =====================
         * GSAP
         * ===================== */
        wp_enqueue_script(
            'gsap',
            get_stylesheet_directory_uri() . '/assets/js/gsap/gsap.min.js',
            [],
            null,
            true
        );

        wp_enqueue_script(
            'gsap-scrolltrigger',
            get_stylesheet_directory_uri() . '/assets/js/gsap/ScrollTrigger.min.js',
            ['gsap'],
            null,
            true
        );

        wp_enqueue_script(
            'gsap-scrollto',
            get_stylesheet_directory_uri() . '/assets/js/gsap/ScrollToPlugin.min.js',
            ['gsap'],
            null,
            true
        );

        /* =====================
         * Custom GSAP + fullPage
         * ===================== */
        wp_enqueue_script(
            'gsap-custom',
            get_stylesheet_directory_uri() . '/assets/js/gsap/gsap-scroll.js',
            [
                'gsap',
                'gsap-scrolltrigger',
                'gsap-scrollto'
            ],
            $version,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'load_gsap_scrolltrigger_only_page');
