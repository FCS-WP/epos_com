(function (window, $) {
  if (!document.body.classList.contains("woocommerce-checkout")) return;

  const PhoneValidation = {
    iti: null,

    init() {
      const input = document.querySelector("#billing_phone");
      if (!input || input.dataset.itiInit) return;

      this.iti = window.intlTelInput(input, {
        initialCountry: "my",
        preferredCountries: ["my", "sg"],
        separateDialCode: true,
        utilsScript:
          "/wp-content/themes/zippy-child/assets/lib/intl-tel-input/js/utils.js",
      });

      input.dataset.itiInit = "1";

      // events
      input.addEventListener("countrychange", () => this.validate());
      $(document).on("change", "#billing_phone", () => this.validate());
    },

    validate() {
      const input = document.querySelector("#billing_phone");
      if (!input || !this.iti) return true;

      const value = input.value.trim();

      if (!value || !this.iti.isValidNumber()) {
        $("#billing_phone_field").addClass("woocommerce-invalid");
        return false;
      }

      $("#billing_phone_field").removeClass("woocommerce-invalid");
      return true;
    },

    isEmpty() {
      const input = document.querySelector("#billing_phone");
      return !input || !input.value.trim();
    },

    format() {
      const input = document.querySelector("#billing_phone");
      if (!input || !this.iti) return;

      input.value = this.iti.getNumber(
        intlTelInputUtils.numberFormat.INTERNATIONAL
      );
    },
  };

  // expose ra global cho checkout dùng
  window.PhoneValidation = PhoneValidation;

  // init khi load & khi checkout ajax reload
  document.addEventListener("DOMContentLoaded", () => PhoneValidation.init());
  $(document.body).on("updated_checkout", () => PhoneValidation.init());
})(window, jQuery);
