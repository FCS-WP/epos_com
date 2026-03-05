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
})(jQuery);