// Remove firstname default field
document.addEventListener("DOMContentLoaded", () => {
  if (!document.body.classList.contains("woocommerce-checkout")) return;
  document
    .querySelectorAll("#billing_first_name_field, #billing_last_name_field")
    .forEach((el) => el.remove());
});

// Validate phonenumber
(function ($) {
  if (!document.body.classList.contains("woocommerce-checkout")) return;

  $(document).on("click", "#place_order[name='woocommerce_checkout_place_order']", function (e) {
    if (!window.PhoneValidation) return;

    $(".woocommerce-error").remove();

    const checkoutForm = $("form.checkout");
    if (!checkoutForm.length) return;

    if (PhoneValidation.isEmpty()) {
      e.preventDefault();
      e.stopImmediatePropagation();

      checkoutForm.prepend(`
        <ul class="woocommerce-error" role="alert">
          <li class="alert-color"><strong>Phone number</strong> is required</li>
        </ul>
      `);

      window.scrollTo({ top: 0, behavior: "smooth" });
      return;
    }

    if (!PhoneValidation.validate()) {
      e.preventDefault();
      e.stopImmediatePropagation();

      checkoutForm.prepend(`
        <ul class="woocommerce-error" role="alert">
          <li class="alert-color"><strong>Phone number</strong> is not valid</li>
        </ul>
      `);

      window.scrollTo({ top: 0, behavior: "smooth" });
      return;
    }

    PhoneValidation.format();
  });

  // $(document.body).on('wc_address_i18n_ready', function() {
  //   setTimeout(function() {

  //     console.log('yeah');
    
  //     const $fields = $([
  //       '#billing_recipient',
  //       '#billing_company',
  //       '#billing_address_1',
  //       '#billing_address_2',
  //       '#billing_city',
  //       '#billing_state',
  //       '#billing_postcode',
  //       '#referral_code'
  //     ].join(',')).closest('.form-row');
  
  //     console.log($fields);
  
  //     // if (!$fields.parent().hasClass('epos-checkout__content')) {
  //       $fields.wrapAll('<div class="epos-checkout__content"></div>');
  //     // }
  //   }, 0);
  // });
  
  // $(document.body).trigger('wc_address_i18n_ready');
})(jQuery);