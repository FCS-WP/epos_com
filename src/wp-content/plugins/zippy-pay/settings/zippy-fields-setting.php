<?php

namespace ZIPPY_Pay\Settings;

use WC_Admin_Settings;
use ZIPPY_Pay\Core\ZIPPY_Pay_Core;

defined('ABSPATH') || exit;

class ZIPPY_Fields_Setting
{

  /**
   * The single instance of the class.
   *
   * @var   ZIPPY_Field_Settings
   */
  protected static $_instance = null;


  private $keys = ['merchant_id', 'secret_key'];
  /**
   * @return ZIPPY_Field_Settings
   */
  public static function get_instance()
  {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * Constructor
   */
  public function __construct()
  {

    add_action('woocommerce_admin_field_zippy_credit_card_field', array($this, 'zippy_credit_card_settings'));
    add_action('woocommerce_admin_field_zippy_paynow_field', array($this, 'zippy_paynow_settings'));
    add_action('woocommerce_admin_field_zippy_general_field', array($this, 'zippy_general_settings'));
    add_action('woocommerce_admin_field_zippy_antom_field', array($this, 'zippy_antom_settings'));
    add_action('woocommerce_admin_field_zippy_2c2p_field', array($this, 'render_zippy_2c2p_settings_html'));
    add_action('woocommerce_update_option_zippy_2c2p_field', array($this, 'save_zippy_2c2p_settings_data'));
    // add_action("wp_ajax_sync_config_payment_callback", array($this, "sync_config_payment_callback"));
    // add_action("wp_ajax_nopriv_sync_config_payment_callback", array($this, "sync_config_payment_callback"));
  }


  /**
   * Sync config from zippy
   *
   * @return array
   */

  public function sync_config_payment_callback()
  {
    // Example response
    $response = array(
      'status' => 'success',
      'message' => 'AJAX request processed successfully',
    );

    // Send the JSON response
    wp_send_json($response);
  }

  /**
   * Add Additional Settings Of Zippy General Setting
   *
   *
   */
  public function zippy_general_settings($current_section = '')
  {
    $merchant_id =   WC_Admin_Settings::get_option(PREFIX . '_merchant_id');

    echo ZIPPY_Pay_Core::get_template('general/setting-fields.php', [
      'params' => $merchant_id,

    ], dirname(__FILE__), '/templates');
  }

  /**
   * Add Additional Settings Of Zippy credit card
   *
   *
   */
  public function zippy_credit_card_settings($current_section = '')
  {

    $credit_card_config = get_option('zippy_configs_adyen');

    if (empty($credit_card_config)) return;

    $credit_cards = $credit_card_config->paymentMethods->paymentMethods[0]->brands;

    echo ZIPPY_Pay_Core::get_template('credit-card/setting-fields.php', [
      'cards' => $credit_cards,

    ], dirname(__FILE__), '/templates');
  }

  /**
   * Add Additional Settings Of Zippy Paynow
   *
   *
   */
  public function zippy_paynow_settings($current_section = '')
  {

    $config_infor = get_option('zippy_configs_paynow');

    echo ZIPPY_Pay_Core::get_template('paynow/setting-fields.php', [
      'params' => $config_infor,
    ], dirname(__FILE__), '/templates');
  }

  /**
   * Add Additional Settings Of Zippy Paynow
   *
   *
   */
  public function zippy_antom_settings($current_section = '')
  {

    $config_infor = get_option('zippy_configs_antom');

    echo ZIPPY_Pay_Core::get_template('antom/setting-fields.php', [
      'params' => $config_infor,
    ], dirname(__FILE__), '/templates');
  }

  public function render_zippy_2c2p_settings_html($value)
  {
    $keysed_values = [];

    foreach ($this->keys as $key) {
      $keysed_values[$key] = get_option('zippy_payment_2c2p_' . $key, '');
    }

    echo ZIPPY_Pay_Core::get_template('2c2p/setting-fields.php', [
      'params' => $keysed_values,
    ], dirname(__FILE__), '/templates');
  }

  public function save_zippy_2c2p_settings_data($option)
  {
    foreach ($this->keys as $key) {
      $option_id = 'zippy_payment_2c2p_' . $key;

      if (!isset($_POST[$option_id])) {
        continue;
      }

      $value_to_save = wc_clean(wp_unslash($_POST[$option_id]));

      update_option($option_id, $value_to_save);
    }
  }
}
