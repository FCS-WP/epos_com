<?php
// Adyen Payment Gateway Constants
define('PGAWC_VERSION', '1.0.0');
define('PAYMENT_ADYEN_NAME', 'Online Payment');
define('PAYMENT_ADYEN_ID', 'zippy_adyen_payment');


// Paynow Payment Gateway Constants
define('PAYMENT_PAYNOW_NAME', 'Paynow');
define('PAYMENT_PAYNOW_ID', 'zippy_paynow_payment');

// Antom Payment Gateway Constants
define('PAYMENT_ANTOM_ID', 'zippy_antom_payment');
define('PAYMENT_ANTOM_NAME', 'EPOS Pay (Antom)');

// 2C2P Payment Gateway Constants
define('PAYMENT_2C2P_NAME', 'Credit / Debit Card Payment');
define('PAYMENT_2C2P_ID', 'zippy_2c2p_payment');
define('PAYMENT_2C2P_MERCHANT_ID', 'zippy_payment_2c2p_merchant_id');
define('PAYMENT_2C2P_SECRECT_KEY', 'zippy_payment_2c2p_secret_key');
define('PAYMENT_2C2P_ENDPOINT', 'https://pgw.2c2p.com/payment/4.3');
define('PAYMENT_2C2P_BASE_UI', 'https://pgw-ui.2c2p.com/payment/4.1');

// API Namespace
if (!defined('ZIPPY_PAYMENT_API_NAMESPACE')) {
  define('ZIPPY_PAYMENT_API_NAMESPACE', 'zippy-pay/v1');
}
define('PREFIX', 'zippy_payment_getway');
