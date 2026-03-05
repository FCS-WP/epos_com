<?php
// Language switch ajax handle
add_action('wp_ajax_set_lang', 'handle_set_lang');
add_action('wp_ajax_nopriv_set_lang', 'handle_set_lang');
function handle_set_lang() {
    if (!isset($_POST['lang'])) {
        wp_die();
    }
    if (function_exists('WC')) {
        if (!WC()->session) {
            WC()->initialize_session();
        }
        if (!WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }
    }
    $lang = sanitize_text_field($_POST['lang']);
    if (in_array($lang, Lang::supported())) {
        if (function_exists('WC') && WC()->session) {
            WC()->session->set('site_lang', $lang);
        }
    }
    wp_die();
}


// Language switch
add_action('wp_body_open', 'languages_switch');
function languages_switch() {
    if (empty($GLOBALS['is_multi_lang_page'])) return;

    // var_dump(WC()->session->get('site_lang'));
    $currentLang = Lang::get(); 
    // var_dump($currentLang);
    ?>
    <div class="lang-switch">
        <?php foreach (Lang::supported() as $lang): ?>
            <a href="#" data-lang="<?php echo esc_attr($lang); ?>" class="lang-btn <?php echo $currentLang === $lang ? 'active' : ''; ?>">
                <?php echo strtoupper($lang); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
}
