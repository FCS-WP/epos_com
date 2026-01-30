(function ($) {
  if (!document.body.classList.contains("woocommerce-checkout")) return;

  $(document).on("click", "#place_order", function (e) {
    if (!window.PhoneValidation) return;

    $(".woocommerce-error").remove();

    if (PhoneValidation.isEmpty()) {
      e.preventDefault();
      e.stopImmediatePropagation();

      $("form.checkout").prepend(`
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

      $("form.checkout").prepend(`
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