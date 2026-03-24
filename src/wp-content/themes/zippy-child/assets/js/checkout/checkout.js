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

  let $country = $('#billing_country');
  // $country.prop("disabled", true);

  // ------------- Expand / Collapse form fields -------------
  let $blocks = $('.js-checkout-block');

  $(window).resize(function() {
    $blocks.each(function() {
      let $block = $(this);
      let $content = $block.children('.js-checkout-content');
      let $inner = $content.children('.js-checkout-inner');
      let innerHeight = $inner.outerHeight();

      $content.css('height', innerHeight);

      $block.on('click', function(e) {
        let $target = $(e.target).closest('.js-checkout-content');

        if (!$target.hasClass('js-checkout-content')) {
          $block.toggleClass('is-collapsed');
        }
      });
    });
  });

  setTimeout(function() {
    $(window).trigger('resize');
  }, 300);


  // ------------- Populate recipient field -------------
  let $fullname = $('#billing_full_name');
  let $recipient = $('#billing_recipient');
  let edited = false;

  $fullname.on('input', function() {
    let value = $fullname.val();
    
    if (!$recipient.val() || !edited) {
      $recipient.val(value);
    }
  });

  $fullname.trigger('input');

  $recipient.on('input', function() {
    edited = true;
  });


  // ------------- Auto scroll to the next form section -------------
  let $secondBlock = $blocks.last();
  let $header = $('#header');
  let $email = $('#billing_email');
  let $phone = $('#billing_phone');
  let isFullnameEmpty = !$fullname.val();
  let isEmailEmpty = !$email.val();
  let isPhoneEmpty = !$phone.val();
  let fullnameTimeout;
  let emailTimeout;
  let phoneTimeout;

  const scrollToFormBlock = function() {
    let headerheight = $header.outerHeight(); 
    let top = $secondBlock.offset().top - headerheight;

    $('html, body').animate({
      scrollTop: top,
    }, 1000);

    $secondBlock.removeClass('is-collapsed');
  };

  const clearAllTimeOut = function() {
    if (fullnameTimeout) {
      clearTimeout(fullnameTimeout);
    }
    if (emailTimeout) {
      clearTimeout(emailTimeout);
    }
    if (phoneTimeout) {
      clearTimeout(phoneTimeout);
    }
  }
  
  $fullname.on('input', function() {
    isFullnameEmpty = !$(this).val();

    clearAllTimeOut();

    fullnameTimeout = setTimeout(function() {
      if (!isFullnameEmpty && !isEmailEmpty && !isPhoneEmpty) {
        scrollToFormBlock();
      }
    }, 1000);
  });

  $email.on('input', function() {
    isEmailEmpty = !$(this).val();

    clearAllTimeOut();

    emailTimeout = setTimeout(function() {
      if (!isFullnameEmpty && !isEmailEmpty && !isPhoneEmpty) {
        scrollToFormBlock();
      }
    }, 1000);
  });

  $phone.on('input', function() {
    isPhoneEmpty = !$(this).val();

    clearAllTimeOut();

    phoneTimeout = setTimeout(function() {
      if (!isFullnameEmpty && !isEmailEmpty && !isPhoneEmpty) {
        scrollToFormBlock();
      }
    }, 1000);
  });
})(jQuery);