<?php 

class Lang {
    protected static $default = 'en';
    protected static $supported = ['en', 'ms'];

    public static function supported() {
        return self::$supported;
    }

    protected static function load($lang) {
        $file = get_theme_file_path(
            "includes/language_translate/languages/{$lang}.php"
        );
        if (!file_exists($file)) {
            $file = get_theme_file_path(
                "includes/language_translate/languages/" . self::$default . ".php"
            );
        }
        return include $file;
    }

    public static function set($lang) {
        if (function_exists('WC') && WC()->session) {
            WC()->session->set('site_lang', $lang);
        }
    }

    public static function get() {
        if (function_exists('WC') && WC()->session) {
            return WC()->session->get('site_lang', self::$default);
        }
        return self::$default;
    }

    public static function translate($key) {
        static $cache = [];
        $lang = self::get();
        if (!isset($cache[$lang])) {
            $cache[$lang] = self::load($lang);
        }
        return $cache[$lang][$key] ?? $key;
    }

    public static function dictionary($lang = null) {
        static $cache = [];
        $lang = $lang ?: self::get();
        if (!isset($cache[$lang])) {
            $cache[$lang] = self::load($lang);
        }
        return $cache[$lang];
    }
}


// Filter cart, checkout contexts
add_filter('gettext', function($translated, $text, $domain) {
    if (!in_array($domain, ['woocommerce', 'flatsome'])) {
        return $translated;
    }
    if (Lang::get() == 'en') {
        return $translated;
    }
    $is_ajax = defined('DOING_AJAX') && DOING_AJAX;
    if (!$is_ajax && empty($GLOBALS['is_multi_lang_page'])) {
        return $translated;
    }
    // Use dictionary method
    $dictionary = Lang::dictionary();
    return $dictionary[$text] ?? $translated;
}, 20, 3);


// Filter for shipping method label
add_filter('woocommerce_shipping_package_name', function($package_name) {
    if (Lang::get() == 'en') {
        return $package_name;
    }
    if (strpos($package_name, 'Shipping') !== false) {
        return str_replace('Shipping', Lang::translate('Shipping'), $package_name);
    }
    return $package_name;
});
add_filter('woocommerce_cart_shipping_method_full_label', function ($label, $method) {
    if (Lang::get() == 'en') {
        return $label;
    }
    $original_label = $method->get_label();
    $translated = Lang::translate($original_label);
    return str_replace($original_label, $translated, $label);
}, 10, 2);


// Filter payment method label
add_filter('woocommerce_gateway_title', function($title, $gateway_id) {
    if (Lang::get() == 'en') {
        return $title;
    }
    if ($gateway_id === 'bacs') {
        return Lang::translate('Direct bank transfer');
    }
    if ($gateway_id === 'cheque') {
        return Lang::translate('Check payments');
    }
    if ($gateway_id === 'cod') {
        return Lang::translate('Cash on delivery');
    }
    return $title;
}, 10, 2);
add_filter('woocommerce_gateway_description', function($description, $gateway_id) {
    if ($gateway_id === 'bacs') {
        return Lang::translate(
            'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.'
        );
    }
    if ($gateway_id === 'cheque') {
        return Lang::translate(
            'Please send a check to Store Name, Store Street, Store Town, Store State / County, Store Postcode.'
        );
    }
    if ($gateway_id === 'cod') {
        return Lang::translate('Pay with cash upon delivery.');
    }
    return $description;
}, 10, 2);
// Policy context
add_filter('woocommerce_get_privacy_policy_text', function ($text) {
    if (Lang::get() == 'en') {
        return $text;
    }
    return Lang::translate(
        'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our privacy policy.'
    );
});
