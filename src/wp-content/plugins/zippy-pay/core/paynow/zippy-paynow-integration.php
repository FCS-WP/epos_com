<?php

namespace ZIPPY_Pay\Core\Paynow;

use ZIPPY_Pay\Core\Paynow\ZIPPY_Paynow_Gateway;

use ZIPPY_Pay\Core\ZIPPY_Pay_Core;


class ZIPPY_Paynow_Integration
{

    /**
     * The single instance of the class.
     *
     * @var   ZIPPY_Paynow_Integration
     */
    protected static $_instance = null;

    /**
     * @return ZIPPY_Paynow_Integration
     */
    public static function get_instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * ZIPPY_Paynow_Integration constructor.
     */
    public function __construct()
    {

        if (!ZIPPY_Pay_Core::is_woocommerce_active()) {
            return;
        }

        add_filter('woocommerce_payment_gateways',  array($this, 'add_zippy_paynow_to_woocommerce'));
        add_action('plugins_loaded',  array($this, 'zippy_paynow_load_plugin_textdomain'));
    }

    public function add_zippy_paynow_to_woocommerce($gateways)
    {

        $gateways[] = ZIPPY_Paynow_Gateway::class;
        return $gateways;
    }

    public function init_zippy_payment_gateway()
    {
        include ZIPPY_PAY_DIR_PATH . '/zippy-payment-getway.php';
    }

    public function zippy_paynow_load_plugin_textdomain()
    {
        load_plugin_textdomain('payment-gateway-for-zippy-and-woocommerce', false, basename(dirname(__FILE__)) . '/languages/');
    }

}
