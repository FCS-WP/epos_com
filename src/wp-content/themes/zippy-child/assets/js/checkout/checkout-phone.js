document.addEventListener("DOMContentLoaded", () => {
  if (!document.body.classList.contains("woocommerce-checkout")) return;
  document
    .querySelectorAll("#billing_first_name_field, #billing_last_name_field")
    .forEach((el) => el.remove());
});

(function ($) {
  if (!document.body.classList.contains("woocommerce-checkout")) return;
  let iti = null;

  function initIntlTelInput() {
    const input = document.querySelector("#billing_phone");
    if (!input || input.dataset.itiInit) return;

    iti = window.intlTelInput(input, {
      initialCountry: "my",
      preferredCountries: ["my", "sg"],
      separateDialCode: true,
      utilsScript:
        "/wp-content/themes/zippy-child/assets/lib/intl-tel-input/js/utils.js",
    });

    input.dataset.itiInit = "1";
  }
  document.addEventListener("DOMContentLoaded", initIntlTelInput);

  const input = document.querySelector("#billing_phone");
  input.addEventListener("countrychange", function () {
    if (!input || !iti) return;

    if (!iti.isValidNumber()) {
      $("#billing_phone_field").addClass("woocommerce-invalid");
    } else {
      $("#billing_phone_field").removeClass("woocommerce-invalid");
    }
  });

  $(document).on("change", "#billing_phone", function () {
    if (!input || !iti) return;

    if (!iti.isValidNumber()) {
      $("#billing_phone_field").addClass("woocommerce-invalid");
    } else {
      $("#billing_phone_field").removeClass("woocommerce-invalid");
    }
  });

  $(document).on("click", "#place_order", function (e) {
    if (!input || !iti) return;

    const numberValue = input.value.trim();

    if (!numberValue) {
      e.preventDefault();
      e.stopImmediatePropagation();
      $(".woocommerce-error").remove();

      $("form.checkout").prepend(`
        <ul class="woocommerce-error" role="alert">
          <li class="alert-color"><strong>Phone number</strong> is required</li>
        </ul>
      `);

      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });

      return;
    }

    if (!iti.isValidNumber()) {
      e.preventDefault();
      e.stopImmediatePropagation();
      $(".woocommerce-error").remove();

      $("form.checkout").prepend(`
        <ul class="woocommerce-error" role="alert">
          <li class="alert-color"><strong>Phone number</strong> is not valid</li>
        </ul>
      `);

      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });

      return;
    }

    input.value = iti.getNumber(intlTelInputUtils.numberFormat.INTERNATIONAL);
  });
})(jQuery);
