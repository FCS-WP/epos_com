<?php

/** @var $is_active */
?>

<div id="zippy_antom_message">

  <?php if (isset($is_active) && $is_active) : ?>
    <p><?php echo PAYMENT_ANTOM_NAME; ?> is ready for payment.</p>
  <?php else : ?>
    <span class="zippy-has-error">We can not process the payment at the moment. Please, try again later.</span>
  <?php endif; ?>
</div>
