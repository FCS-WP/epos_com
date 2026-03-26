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


  // ------------- Init -------------
  $window = $(window);


  // ------------- Expand / Collapse form fields -------------
  let $blocks = $('.js-checkout-block');

  $window.resize(function() {
    $blocks.each(function() {
      let $block = $(this);
      let $header = $block.children('.js-checkout-header');
      let $content = $block.children('.js-checkout-content');
      let $inner = $content.children('.js-checkout-inner');
      let innerHeight = $inner.outerHeight();

      $content.css('height', innerHeight);

      $header.on('click', function() {
        $block.toggleClass('is-collapsed');
      });
    });
  });

  setTimeout(function() {
    $window.trigger('resize');
  }, 300);


  // ------------- Fix phone field dropdown -------------
  setTimeout(function() {
    let $phoneSelects = $blocks.find('.iti__flag-container');

    $window.scroll(function() {
      $phoneSelects.each(function() {
        let $select = $(this);
        let $option = $select.children('.iti__dropdown-content');
        let windowTop = $window.scrollTop();
        let top = $select.offset().top + $select.outerHeight() - windowTop - 1;
        let left = $select.offset().left;

        $option.css('top', top);
        $option.css('left', left);
      });
    });
  
    setTimeout(function() {
      $window.trigger('scroll');
    }, 300);
  }, 1000);


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
  let runOnce = false;

  const scrollToFormBlock = function() {
    let headerheight = $header.outerHeight(); 
    let top = $secondBlock.offset().top - headerheight;

    $secondBlock.removeClass('is-collapsed');

    $('html, body').animate({
      scrollTop: top,
    }, 1000, function() {
      runOnce = true;
    });
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
    if (runOnce) {
      return;
    }

    isFullnameEmpty = !$(this).val();

    clearAllTimeOut();

    fullnameTimeout = setTimeout(function() {
      if (!isFullnameEmpty && !isEmailEmpty && !isPhoneEmpty) {
        scrollToFormBlock();
      }
    }, 1000);
  });

  $email.on('input', function() {
    if (runOnce) {
      return;
    }

    isEmailEmpty = !$(this).val();

    clearAllTimeOut();

    emailTimeout = setTimeout(function() {
      if (!isFullnameEmpty && !isEmailEmpty && !isPhoneEmpty) {
        scrollToFormBlock();
      }
    }, 1000);
  });

  $phone.on('input', function() {
    if (runOnce) {
      return;
    }

    isPhoneEmpty = !$(this).val();

    clearAllTimeOut();

    phoneTimeout = setTimeout(function() {
      if (!isFullnameEmpty && !isEmailEmpty && !isPhoneEmpty) {
        scrollToFormBlock();
      }
    }, 1000);
  });


  // ------------- Prevent the coupon from submitting -------------
  let $couponInput = $('.js-coupon-input');
  let $couponBtn = $('.js-coupon-submit');
  let $formCoupon = $('form.checkout_coupon');
  let $realCouponInput = $formCoupon.find('#coupon_code');
  let $realCouponBtn = $formCoupon.find('button[name="apply_coupon"]');

  if ($couponBtn.length && $formCoupon.length) {
    $couponBtn.on('click', function(e) {
      e.preventDefault(); // Prevent form submission
      let value = $couponInput.val();

      if (value) {
        $realCouponInput.val(value);
        $realCouponBtn.trigger('click');
      }
    });
  }


  // ------------- Move error messages to below the fields -------------
  let $checkoutForm = $('form.checkout');

  // Hide all errors on submit
  $checkoutForm.on('submit', function() {
    $('.js-epos-error').slideUp();

    setTimeout(function() {
      $window.trigger('resize');
    }, 400);
  });

  $(document.body).on('checkout_error', function(e, messages) {
    let $errorWrapper = $(messages);

    // Remove all previously created errors
    $('.js-epos-error').remove();

    if ($errorWrapper.length) {
      let $errorNodes = $errorWrapper.find('li');
  
      $errorNodes.each(function() {
        let $node = $(this);
        let id = $node.data('id');
        let error = $node.text().trim();
        let $field = $('input#'+id);
        let $domError = $('.woocommerce-error li[data-id="'+id+'"]');
        
        // Some error messages are email address related
        // despite having no id
        if (!$field.length && error.includes('email address')) {
          id = 'billing_email';
          $field = $('input#'+id);

          if ($field.length) {
            let $wrapper = $field.closest('.form-row');
            
            // Add error class to input field
            $wrapper.addClass('woocommerce-invalid-email');
            // Look for the error message inserted by Woo
            $domError = $('.woocommerce-error li:contains("'+error+'")');
          }
        }

        // Hide matched original error message inserted by Woo
        if ($domError.length) {
          $domError.hide();
        }

        // Insert custom error message to below input
        if ($field.length) {
          let $parent = $field.parent();
          
          $parent.append(`
            <p class="js-epos-error checkout-inline-error-message">
              ${error}
            </p>
          `);
        }
      });

      // Slide down all error messages
      $('.js-epos-error').slideDown();

      // $.scroll_to_notices( $('.js-epos-error').first().closest('.form-row') );
    }

    // Resize window to make sure all the form wrappers are properly sized
    $window.trigger('resize');
  });
})(jQuery);