<?php
/* @wordpress-plugin
 * Plugin Name:       Zippy Pay
 * Plugin URI:        https://zippy.sg/
 * Description:       Zippy Pay WooCommerce Payment Gateway Plugin
 * Version:           7.0.1
 * WC requires at least: 8.0
 * WC tested up to: 6.8
 * Author:            Zippy
 * Author URI:        https://zippy.sg/
 * URI:               https://zippy.sg/
 * Text Domain:       zippy-and-woocommerce
 * Requires Plugins: woocommerce
 * license:           GPL-2.0+
 * license URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

namespace ZIPPY_Pay;

use ZIPPY_Pay\Core\Adyen\ZIPPY_Adyen_Pay_Integration;
use ZIPPY_Pay\Core\Paynow\ZIPPY_Paynow_Integration;
use ZIPPY_Pay\Core\Antom\ZIPPY_Antom_Integration;
use ZIPPY_Pay\Core\TcTp\ZIPPY_2c2p_Pay_Integration;
use ZIPPY_Pay\Settings\Zippy_Pay_Ajax_Handle;
use ZIPPY_Pay\Core\ZIPPY_Pay_Core;
use ZIPPY_Pay\Src\Woocommerce\Zippy_Woo_Template;
use ZIPPY_Pay\Src\Antom\ZIPPY_Antom_Scheduler;
use ZIPPY_Pay\Src\Paynow\ZIPPY_Paynow_Scheduler;


/* Set constant enpoint to the plugin directory. */

define('ZIPPY_PAY_DIR_URL', plugin_dir_url(__FILE__));
define('ZIPPY_PAY_DIR_PATH', plugin_dir_path(__FILE__));
define('ZIPPY_PAY_ENDPOINT', plugin_dir_path(__FILE__));


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once ZIPPY_PAY_DIR_PATH . 'vendor/autoload.php';

require_once ZIPPY_PAY_DIR_PATH . '/includes/autoload.php';
require_once ZIPPY_PAY_DIR_PATH . '/includes/constants.php';

add_action('zippy_check_antom_payment_task', [ZIPPY_Antom_Scheduler::class, 'process_order'], 10, 2);

add_action('zippy_check_paynow_payment_task', [ZIPPY_Paynow_Scheduler::class, 'process_order'], 10, 2);

add_filter('cron_schedules', function ($schedules) {
  $schedules['zippy_antom_every_minute'] = [
    'interval' => 15, // 60 seconds (1 minute)
    'display'  => __('Every Minute'),
  ];
  $schedules['zippy_paynow_every_minute'] = [
    'interval' => 15, // 60 seconds (1 minute)
    'display'  => __('Every Minute'),
  ];
  return $schedules;
});


add_action(
  'before_woocommerce_init',
  function () {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
      \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, false);
      \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
  }
);


ZIPPY_Pay_Core::global_style();

Zippy_Woo_Template::get_instance();

ZIPPY_Adyen_Pay_Integration::get_instance();

ZIPPY_Paynow_Integration::get_instance();

ZIPPY_Antom_Integration::get_instance();

ZIPPY_2c2p_Pay_Integration::get_instance();

Zippy_Pay_Ajax_Handle::get_instance();
