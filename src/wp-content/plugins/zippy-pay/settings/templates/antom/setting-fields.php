<?php

namespace ZIPPY_Pay\Settings;

defined('ABSPATH') || exit;
/** @var $params */

?>

<?php if (isset($params) && !empty($params)) : ?>

  <table class="form-table" id="zippy_setting_wrapper" style="display: none;">
    <tr>
      <th scope="row" class="titledesc">
        <?php _e('Merchant ID', PREFIX . 'zippy-settings-field'); ?>
      </th>
      <td class="forminp forminp-text">
        <span class="epos-paynow-details"><?php echo ($params->merchantId); ?></span>
      </td>
    </tr>
    <tr>
      <th scope="row" class="titledesc">
        <?php _e('Merchant Name', PREFIX . 'zippy-settings-field'); ?>
      </th>
      <td class="forminp forminp-text">
        <span class="epos-paynow-details"><?php echo ($params->merchantName); ?></span>
      </td>
    </tr>

  </table>

<?php endif; ?>
