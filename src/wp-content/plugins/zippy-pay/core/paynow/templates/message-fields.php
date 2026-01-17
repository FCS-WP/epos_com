<?php

/** @var $is_active */
?>

<div class="zippy-paynow-payment-mess">
  <?php if (isset($is_active) && $is_active) : ?>
    <p>Paynow is ready for payment.</p>
    <script>
      "use strict";
      $ = jQuery;
      $(document).ready(function() {
        handleChoosePaynowPayment();

        function handleChoosePaynowPayment() {

          var payment_methods = jQuery('#payment input[type="radio"]');

          if (payment_methods.length == 1) {
            $("#payment_method_zippy_paynow_payment").prop("checked", true);
          }
        }

        if (isMobile()) {
          var paynow_checkbox = $("#payment_method_zippy_paynow_payment");

          $("#payment_method_zippy_paynow_payment").prop("checked", false);

          var btn_paynow = $("#paynow-cofirm");

          paynow_checkbox.click(function() {
            if ($(this).is(":checked")) {
              openPopup();
            }
          });

          btn_paynow.click(function(e) {
            e.preventDefault();
            closePopup();
          });
        }

        function openPopup() {
          var pop_up = $("#paynow-pop-up");
          pop_up.addClass("active");
          pop_up.show();
          $('body').addClass("paynow-pop-up-open");
        }

        function closePopup() {
          var pop_up = $("#paynow-pop-up");
          pop_up.removeClass("active");
          pop_up.hide();
          $('body').removeClass("paynow-pop-up-open");
        }

        function isMobile() {
          const width = window.innerWidth;

          if (width <= 1024) {
            return true;
          } else {
            return false;
          }
        }
      });
    </script>

  <?php else : ?>
    <span class="zippy-has-error">We can not process the payment at the moment. Please, try again later.</span>
  <?php endif; ?>
</div>
