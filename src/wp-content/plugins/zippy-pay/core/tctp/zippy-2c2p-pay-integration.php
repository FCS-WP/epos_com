<?php

namespace ZIPPY_Pay\Core\TcTp;

use ZIPPY_Pay\Core\TcTp\ZIPPY_2c2p_Gateway;

use ZIPPY_Pay\Core\ZIPPY_Pay_Core;

use ZIPPY_Pay\Settings\ZIPPY_Pay_Settings;

class ZIPPY_2c2p_Pay_Integration
{

    /**
     * The single instance of the class.
     *
     * @var   ZIPPY_2c2p_Pay_Integration
     */
    protected static $_instance = null;

    /**
     * @return ZIPPY_2c2p_Pay_Integration
     */
    public static function get_instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * ZIPPY_Adyen_Pay_Integration constructor.
     */
    public function __construct()
    {

        if (!ZIPPY_Pay_Core::is_woocommerce_active()) {
            return;
        }
        // add_filter('woocommerce_get_settings_pages', [$this, 'setting_page']);

        add_filter('woocommerce_payment_gateways', [$this, 'add_zippy_to_woocommerce']);

        add_action('plugins_loaded', [$this, 'zippy_payment_load_plugin_textdomain']);

        // add_action('wp_enqueue_scripts', [$this, 'scripts_and_styles']);

        add_action('before_woocommerce_init', function () {
            if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(PAYMENT_2C2P_ID, __FILE__, true);
            }
        });
    }

    public function setting_page($settings)
    {

        $settings[] = new ZIPPY_2c2p_Gateway();
        return $settings;
    }

    public function scripts_and_styles()
    {

        if (!is_checkout()) {
            return;
        }
        wp_enqueue_script('adyen-sdk', 'https://pgw-ui.2c2p.com/sdk/js/pgw-sdk-4.2.1.js', [], '', true);
        wp_enqueue_style('adyen-css', 'https://pgw-ui.2c2p.com/sdk/css/pgw-sdk-style-4.2.1.css', [], '');
    }


    public function add_zippy_to_woocommerce($gateways)
    {

        $gateways[] = ZIPPY_2c2p_Gateway::class;
        return $gateways;
    }


    public function zippy_payment_load_plugin_textdomain()
    {
        load_plugin_textdomain('payment-gateway-for-adyen-and-woocommerce', false, basename(dirname(__FILE__)) . '/languages/');
    }
}
