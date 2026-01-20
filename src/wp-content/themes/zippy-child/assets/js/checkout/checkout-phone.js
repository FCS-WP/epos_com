(function ($) {
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
    const input = document.querySelector("#billing_phone");
    if (!input || !iti) return;

    if (!iti.isValidNumber()) {
      $("#billing_phone_field").addClass("woocommerce-invalid");
    } else {
      $("#billing_phone_field").removeClass("woocommerce-invalid");
    }
  });

  $(document).on("change", "#billing_phone", function () {
    const input = document.querySelector("#billing_phone");
    if (!input || !iti) return;

    if (!iti.isValidNumber()) {
      $("#billing_phone_field").addClass("woocommerce-invalid");
    } else {
      $("#billing_phone_field").removeClass("woocommerce-invalid");
    }
  });

  $(document).on("click", "#place_order", function (e) {
    const input = document.querySelector("#billing_phone");
    if (!input || !iti) return;

    if (!iti.isValidNumber()) {
      e.preventDefault();
      e.stopImmediatePropagation();

      $(input).addClass("phone-invalid").focus();
      return;
    }

    input.value = iti.getNumber();
  });
})(jQuery);
