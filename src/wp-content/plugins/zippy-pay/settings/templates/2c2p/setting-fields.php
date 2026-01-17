<?php

namespace ZIPPY_Pay\Settings;

defined('ABSPATH') || exit;
/** @var $params */


?>
<tr valign="top">
  <th scope="row" class="titledesc">
    <label>2C2P Merchant ID</label>
  </th>
  <td class="forminp">
    <input
      name="<?php echo PAYMENT_2C2P_MERCHANT_ID; ?>"
      id="<?php echo PAYMENT_2C2P_MERCHANT_ID; ?>"
      type="text"
      value="<?php echo esc_attr($params['merchant_id']); ?>"
      style="width: 400px;"
      placeholder="Enter 2C2P Merchant ID here" />
  </td>
</tr>

<tr valign="top">
  <th scope="row" class="titledesc">
    <label>2C2P Secret Key</label>
  </th>
  <td class="forminp">
    <input
      name="<?php echo PAYMENT_2C2P_SECRECT_KEY; ?>"
      id="<?php echo PAYMENT_2C2P_SECRECT_KEY; ?>"
      type="text"
      value="<?php echo esc_attr($params['secret_key']); ?>"
      style="width: 400px;"
      placeholder="Enter 2C2P Secret Key here" />
  </td>
</tr>
