// Subscription V2 landing.
//
// Dependencies are loaded as separate <script> tags (declared in content.json
// `libs` array, emitted by landing_footer()). We read them off the window
// global rather than `import` so webpack doesn't bundle them.
//
// HubSpot form submission goes through our own bridge — see initLandingForms()
// below. We do NOT load HubSpot's iframe embed.
import { LandingForm } from "../_shared/form-bridge";

(function () {
  "use strict";

  // Pull deps off window. They are loaded by <script> tags before this file runs.
  var $ = window.jQuery;
  var gsap = window.gsap;
  var ScrollTrigger = window.ScrollTrigger;
  var ScrollToPlugin = window.ScrollToPlugin;
  var ScrollSmoother = window.ScrollSmoother;

  var scrollRefreshTimer = null;
  var animationResetTimer = null;
  var smoother = null;

  if (gsap && typeof gsap.registerPlugin === "function") {
    var plugins = [];
    if (ScrollTrigger)   plugins.push(ScrollTrigger);
    if (ScrollToPlugin)  plugins.push(ScrollToPlugin);
    if (ScrollSmoother)  plugins.push(ScrollSmoother);
    if (plugins.length)  gsap.registerPlugin.apply(gsap, plugins);
  }

  function initScrollSmoother() {
    if (!ScrollSmoother || typeof ScrollSmoother.create !== "function") return;
    var wrapper = document.getElementById("sub-v2-smoother-wrapper");
    var content = document.getElementById("sub-v2-smoother-content");
    if (!wrapper || !content) return;
    if (isReducedMotionPreferred()) return;

    // Destroy any previous instance (e.g. after HMR / resize reset)
    if (smoother) {
      smoother.kill();
      smoother = null;
    }

    smoother = ScrollSmoother.create({
      wrapper: wrapper,
      content: content,
      // Higher = more lag / more cinematic feel. 1.2–1.6 is the sweet spot.
      smooth: 1.5,
      // Normalize scroll delta across trackpad / mouse wheel / touch for
      // consistent velocity — this is the biggest factor for "feeling smooth"
      normalizeScroll: true,
      // Slight smoothing on touch devices — 0 = native, 1 = full smooth
      smoothTouch: 0.08,
      // Prevent ScrollTrigger from recalculating on every mobile resize
      // (address bar show/hide causes constant jitter without this)
      ignoreMobileResize: true,
    });
  }

  function initSubscriptionV2() {
    var root = document.querySelector(
      ".subscription-v2, [data-subscription-v2], .sub-page--v2",
    );
    if (!root) return;

    initScrollSmoother();
    bindFaq(root);
    bindDemoModal(root);
    bindSmoothScroll(root);
    initToolsSlider(root, 0);
    initTestimonialsSlider(root, 0);
    initLandingForms(root);
    root.__subV2AnimationCleanup = initScrollAnimations(root);

    if (!root.hasAttribute("data-v2-resize-bound")) {
      root.setAttribute("data-v2-resize-bound", "true");
      window.addEventListener("resize", function () {
        syncSliders(root);
        scheduleAnimationReset(root);
      });
    }
  }

  function initScrollAnimations(root) {
    if (!root || !gsap || !ScrollTrigger) return null;

    markAnimationTargets(root);

    if (typeof root.__subV2AnimationCleanup === "function") {
      root.__subV2AnimationCleanup();
      root.__subV2AnimationCleanup = null;
    }

    if (isReducedMotionPreferred()) return null;

    var isCompact = window.matchMedia("(max-width: 767px)").matches;
    var revealDistance = isCompact ? 32 : 60;
    var heroDistance = isCompact ? 48 : 80;
    var revealDuration = isCompact ? 0.9 : 1.15;
    var heroDuration = isCompact ? 1.0 : 1.35;

    var ctx = gsap.context(function () {
      createHeroTimeline(root, {
        duration: heroDuration,
        distance: heroDistance,
      });

      toArray(root.querySelectorAll("[data-animate-group='section-head']")).forEach(
        function (group) {
          createStaggerReveal(group, {
            targets: getAnimationTargets(
              group.querySelectorAll(
                ".sub-v2-kicker, .sub-v2-section-title, .sub-v2-testimonials__desc",
              ),
            ),
            distance: revealDistance,
            duration: revealDuration,
            stagger: 0.12,
            start: "top 95%",
          });
        },
      );

      createFadeRevealBatch(root, "[data-animate='fade-up']", {
        distance: revealDistance,
        duration: revealDuration,
        start: "top 90%",
      });

      createGenericStaggerBatch(root, "[data-animate='stagger']", {
        distance: revealDistance,
        duration: revealDuration,
        stagger: isCompact ? 0.08 : 0.15,
        start: "top 90%",
      });

      createGrowStageReveal(root, {
        distance: isCompact ? 24 : 42,
        duration: isCompact ? 0.95 : 1.1,
        start: isCompact ? "top 90%" : "top 88%",
      });

      if (!isCompact) {
        createPinnedSections(root);
      }
    }, root);

    scheduleScrollRefresh();

    return function () {
      ctx.revert();
    };
  }

  function bindSmoothScroll(root) {
    if (root.hasAttribute("data-v2-smooth-scroll-bound")) return;
    root.setAttribute("data-v2-smooth-scroll-bound", "true");

    document.addEventListener("click", function (e) {
      var link = e.target.closest("a[href^='#']");
      if (!link) return;

      var hash = link.getAttribute("href");
      if (!hash || hash === "#") return;

      var target = document.querySelector(hash);
      if (!target) return;

      // skip modal triggers — they handle their own logic
      if (link.hasAttribute("data-sub-v2-demo-modal-open")) return;

      e.preventDefault();

      var header = document.querySelector(".sub-v2-header");
      var headerH = header ? header.getBoundingClientRect().height : 0;
      var targetTop = target.getBoundingClientRect().top + window.pageYOffset - headerH - 12;

      if (smoother) {
        // ScrollSmoother.scrollTo animates through the smoother's virtual
        // scroll position — so easing & inertia stay consistent
        gsap.to(smoother, {
          scrollTop: targetTop,
          duration: 1.2,
          ease: "power4.inOut",
          overwrite: "auto",
        });
      } else {
        gsap.to(window, {
          scrollTo: { y: targetTop, autoKill: true },
          duration: 1.2,
          ease: "power4.inOut",
        });
      }
    });
  }

  function bindFaq(root) {
    var faqRoot = root.querySelector("[data-sub-v2-faq]");
    if (!faqRoot || faqRoot.hasAttribute("data-v2-faq-bound")) return;

    faqRoot.setAttribute("data-v2-faq-bound", "true");

    var faqItems    = toArray(faqRoot.querySelectorAll(".sub-v2-faq__item"));
    var activeTween = null; // global lock — kill before starting new sequence

    var CLOSE_DUR  = 0.32;
    var OPEN_DUR   = 0.48;
    var CLOSE_EASE = "power2.inOut";
    var OPEN_EASE  = "power3.out";

    // Bootstrap: strip hidden attr, set initial collapsed/expanded state
    faqItems.forEach(function (item) {
      var body    = item.querySelector(".sub-v2-faq__body");
      var trigger = item.querySelector(".sub-v2-faq__trigger");
      var icon    = item.querySelector(".sub-v2-faq__icon");
      if (!body || !trigger) return;

      body.removeAttribute("hidden");
      var isOpen = trigger.getAttribute("aria-expanded") === "true";
      gsap.set(body, { height: isOpen ? "auto" : 0, overflow: "hidden" });
      if (icon) gsap.set(icon, { rotation: isOpen ? 180 : 0, transformOrigin: "50% 50%" });
    });

    function getOpenItem() {
      return faqItems.find(function (item) {
        var t = item.querySelector(".sub-v2-faq__trigger");
        return t && t.getAttribute("aria-expanded") === "true";
      }) || null;
    }

    function runSequence(itemToClose, itemToOpen) {
      // Kill whatever is running — prevents mid-animation click fighting
      if (activeTween) {
        activeTween.kill();
        activeTween = null;
      }

      var tl = gsap.timeline({
        onComplete: function () {
          activeTween = null;
          scheduleScrollRefresh();
        },
      });

      // ── Phase 1: close current open item (if any) ────────────────────────
      if (itemToClose) {
        var closeBody    = itemToClose.querySelector(".sub-v2-faq__body");
        var closeTrigger = itemToClose.querySelector(".sub-v2-faq__trigger");
        var closeIcon    = itemToClose.querySelector(".sub-v2-faq__icon");

        closeTrigger.setAttribute("aria-expanded", "false");

        tl.to(closeBody, {
          height: 0,
          duration: CLOSE_DUR,
          ease: CLOSE_EASE,
        }, 0);

        if (closeIcon) {
          tl.to(closeIcon, {
            rotation: 0,
            duration: CLOSE_DUR,
            ease: CLOSE_EASE,
          }, 0); // same position — runs in parallel with body close
        }
      }

      // ── Phase 2: open target item — starts when close is 60% done ────────
      if (itemToOpen) {
        var openBody    = itemToOpen.querySelector(".sub-v2-faq__body");
        var openTrigger = itemToOpen.querySelector(".sub-v2-faq__trigger");
        var openIcon    = itemToOpen.querySelector(".sub-v2-faq__icon");
        // Overlap: start opening before close finishes — feels snappy, not laggy
        var openOffset  = itemToClose ? CLOSE_DUR * 0.55 : 0;

        openTrigger.setAttribute("aria-expanded", "true");

        tl.to(openBody, {
          height: "auto",
          duration: OPEN_DUR,
          ease: OPEN_EASE,
        }, openOffset);

        if (openIcon) {
          tl.to(openIcon, {
            rotation: 180,
            duration: OPEN_DUR,
            ease: OPEN_EASE,
          }, openOffset);
        }
      }

      activeTween = tl;
    }

    faqItems.forEach(function (item) {
      var trigger = item.querySelector(".sub-v2-faq__trigger");
      if (!trigger) return;

      trigger.addEventListener("click", function () {
        var isOpen    = trigger.getAttribute("aria-expanded") === "true";
        var openItem  = getOpenItem();

        if (isOpen) {
          // Toggle closed — no item to open after
          runSequence(item, null);
        } else {
          // Close current open (may be null), then open clicked item
          runSequence(openItem !== item ? openItem : null, item);
        }
      });
    });
  }

  function initToolsSlider(root, attempt) {
    var toolsSlider = root.querySelector("[data-sub-v2-tools-slider]");
    var dotsTarget = root.querySelector("[data-sub-v2-tools-dots]");
    if (!toolsSlider) return;

    if (!($ && $.fn && $.fn.slick)) {
      if (attempt < 10) {
        window.setTimeout(function () {
          initToolsSlider(root, attempt + 1);
        }, 250);
      }
      return;
    }

    var $toolsSlider = $(toolsSlider);
    resetSlider($toolsSlider);

    var slideCount = $toolsSlider.children().length;
    if (!slideCount) return;

    $toolsSlider.css("display", "block");
    $toolsSlider.off(".subV2Scroll");

    $toolsSlider.slick({
      slidesToShow: Math.min(slideCount, 3),
      slidesToScroll: slideCount > 3 ? 2 : 1,
      infinite: slideCount > 3,
      arrows: false,
      dots: slideCount > 1,
      appendDots: dotsTarget ? $(dotsTarget) : $toolsSlider,
      speed: 600,
      adaptiveHeight: false,
      responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: Math.max(1, Math.min(slideCount, 2)),
            slidesToScroll: slideCount > 2 ? 2 : 1,
            infinite: slideCount > 2,
            dots: slideCount > 1,
            appendDots: dotsTarget ? $(dotsTarget) : $toolsSlider,
          },
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
            infinite: slideCount > 1,
            dots: slideCount > 1,
            appendDots: dotsTarget ? $(dotsTarget) : $toolsSlider,
            centerMode: true,
            centerPadding: "28px",
          },
        },
      ],
    });

    bindSliderRefreshEvents($toolsSlider);
    scheduleScrollRefresh();
  }

  function initTestimonialsSlider(root, attempt) {
    var testimonialsSlider = root.querySelector(
      "[data-sub-v2-testimonials-slider]",
    );
    if (!testimonialsSlider) return;

    if (!($ && $.fn && $.fn.slick)) {
      if (attempt < 10) {
        window.setTimeout(function () {
          initTestimonialsSlider(root, attempt + 1);
        }, 250);
      }
      return;
    }

    var $testimonialsSlider = $(testimonialsSlider);
    resetSlider($testimonialsSlider);

    var slideCount = $testimonialsSlider.children().length;
    if (!slideCount) return;

    var dotsTarget = root.querySelector("[data-sub-v2-testimonials-dots]");

    $testimonialsSlider.css("display", "block");
    $testimonialsSlider.off(".subV2Scroll");

    $testimonialsSlider.slick({
      slidesToShow: Math.min(slideCount, 3),
      slidesToScroll: slideCount > 3 ? 3 : 1,
      infinite: slideCount > 3,
      arrows: false,
      dots: slideCount > 3,
      appendDots: dotsTarget ? $(dotsTarget) : $testimonialsSlider,
      speed: 600,
      adaptiveHeight: true,
      responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: Math.min(slideCount, 2),
            slidesToScroll: slideCount > 2 ? 2 : 1,
            infinite: slideCount > 2,
            dots: slideCount > 2,
            appendDots: dotsTarget ? $(dotsTarget) : $testimonialsSlider,
          },
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
            infinite: slideCount > 1,
            dots: slideCount > 1,
            appendDots: dotsTarget ? $(dotsTarget) : $testimonialsSlider,
            adaptiveHeight: true,
            centerMode: true,
            centerPadding: "28px",
          },
        },
      ],
    });

    bindSliderRefreshEvents($testimonialsSlider);
    scheduleScrollRefresh();
  }

  function resetSlider($slider) {
    $slider.off(".subV2Scroll");
    if ($slider.hasClass("slick-initialized")) {
      $slider.slick("unslick");
    }
  }

  function syncSliders(root) {
    initToolsSlider(root, 0);
    initTestimonialsSlider(root, 0);
  }

  function bindDemoModal(root) {
    var modal = root.querySelector("[data-sub-v2-demo-modal]");
    if (!modal || modal.hasAttribute("data-v2-demo-modal-bound")) return;

    var openers = root.querySelectorAll("[data-sub-v2-demo-modal-open]");
    var closers = modal.querySelectorAll("[data-sub-v2-demo-modal-close]");
    var dialog = modal.querySelector(".sub-v2-modal-demo__dialog");

    if (!closers.length || !dialog) return;

    modal.setAttribute("data-v2-demo-modal-bound", "true");

    function openModal(event) {
      if (event) event.preventDefault();

      modal.hidden = false;
      modal.setAttribute("aria-hidden", "false");
      document.body.classList.add("modal-open");

      // Pause ScrollSmoother so it doesn't fight the modal's scroll lock
      if (smoother) smoother.paused(true);

      window.requestAnimationFrame(function () {
        modal.classList.add("is-open");
        dialog.focus();
      });
    }

    // Use delegation so any current or future opener triggers the modal
    document.addEventListener("click", function (e) {
      if (e.target.closest("[data-sub-v2-demo-modal-open]")) {
        openModal(e);
      }
    });

    function closeModal() {
      modal.classList.remove("is-open");
      modal.setAttribute("aria-hidden", "true");
      document.body.classList.remove("modal-open");

      // Resume smoother after modal transition completes
      window.setTimeout(function () {
        if (modal.getAttribute("aria-hidden") === "true") {
          modal.hidden = true;
          if (smoother) smoother.paused(false);
        }
      }, 180);
    }

    openers.forEach(function (opener) {
      opener.addEventListener("click", openModal);
    });

    closers.forEach(function (closer) {
      closer.addEventListener("click", function (event) {
        event.preventDefault();
        closeModal();
      });
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && modal.getAttribute("aria-hidden") === "false") {
        closeModal();
      }
    });
  }

  // ── Landing forms (our REST bridge — replaces HubSpot iframe) ──
  // Two <form data-landing-form="hubspot"> instances render on the page:
  // inline (#sub-v2-demo) + modal. Each is wired independently to the bridge.
  function initLandingForms(root) {
    var forms = root.querySelectorAll('form[data-landing-form="hubspot"]');
    if (!forms.length) return;
    forms.forEach(function (formEl) {
      bindLandingForm(formEl);
    });
  }

  function bindLandingForm(formEl) {
    if (formEl.hasAttribute("data-v2-form-bound")) return;
    formEl.setAttribute("data-v2-form-bound", "true");

    var statusEl = formEl.querySelector(".sub-v2-form__status");
    var setStatus = function (msg, kind) {
      if (!statusEl) return;
      statusEl.textContent = msg || "";
      statusEl.dataset.state = kind || "";
    };

    // intl-tel-input on this form's phone field. Each form gets its own iti.
    var iti = null;
    var phoneEl = formEl.querySelector('input[name="phone"]');
    if (phoneEl && typeof window.intlTelInput === "function") {
      var cfg = window.LANDINGS_FORM_BRIDGE || {};
      var onlyCountries = (formEl.dataset.phoneCountries || "")
        .split(",")
        .map(function (c) { return c.trim().toLowerCase(); })
        .filter(Boolean);
      iti = window.intlTelInput(phoneEl, {
        onlyCountries: onlyCountries.length ? onlyCountries : ["my", "sg", "vn"],
        initialCountry: (formEl.dataset.phoneDefault || "my").toLowerCase(),
        separateDialCode: false,
        autoPlaceholder: "polite",
        utilsScript: cfg.intlTelInputUtilsUrl || undefined,
      });
    }

    var clearFieldError = function (name) {
      var node = formEl.querySelector('[data-error-for="' + name + '"]');
      if (node) node.textContent = "";
      var input = formEl.querySelector('[name="' + name + '"]');
      if (input) input.removeAttribute("aria-invalid");
    };
    var setFieldError = function (name, message) {
      var node = formEl.querySelector('[data-error-for="' + name + '"]');
      if (node) node.textContent = message;
      var input = formEl.querySelector('[name="' + name + '"]');
      if (input) input.setAttribute("aria-invalid", "true");
    };
    formEl.addEventListener("input", function (e) {
      if (e.target && e.target.name) clearFieldError(e.target.name);
    });
    formEl.addEventListener("change", function (e) {
      if (e.target && e.target.name) clearFieldError(e.target.name);
    });

    var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var validate = function () {
      var firstInvalid = null;
      formEl.querySelectorAll("[data-error-for]").forEach(function (n) { n.textContent = ""; });
      formEl.querySelectorAll("[aria-invalid]").forEach(function (n) { n.removeAttribute("aria-invalid"); });

      var name = formEl.querySelector('[name="lastname"]');
      if (name && !name.value.trim()) {
        setFieldError("lastname", "Please enter your name.");
        firstInvalid = firstInvalid || name;
      }
      var email = formEl.querySelector('[name="email"]');
      if (email) {
        var v = email.value.trim();
        if (!v) {
          setFieldError("email", "Please enter your email.");
          firstInvalid = firstInvalid || email;
        } else if (!EMAIL_RE.test(v)) {
          setFieldError("email", "Please enter a valid email address.");
          firstInvalid = firstInvalid || email;
        }
      }
      if (phoneEl) {
        if (!phoneEl.value.trim()) {
          setFieldError("phone", "Please enter your WhatsApp phone number.");
          firstInvalid = firstInvalid || phoneEl;
        } else if (iti && typeof iti.isValidNumber === "function" && iti.isValidNumber() === false) {
          setFieldError("phone", "Please enter a valid phone number.");
          firstInvalid = firstInvalid || phoneEl;
        }
      }
      var industry = formEl.querySelector('[name="your_industry"]');
      if (industry && industry.required && !industry.value) {
        setFieldError("your_industry", "Please select your industry.");
        firstInvalid = firstInvalid || industry;
      }

      if (firstInvalid) {
        try { firstInvalid.focus({ preventScroll: false }); } catch (_) {}
        return false;
      }
      return true;
    };

    var handleSuccess = function () {
      formEl.style.display = "none";
      setStatus(
        formEl.dataset.successMessage ||
          "Thanks — we'll be in touch within one business day.",
        "success"
      );
      var section = formEl.closest(".sub-v2-demo, .sub-v2-modal-demo__content") || statusEl;
      if (section && typeof section.scrollIntoView === "function") {
        section.scrollIntoView({ behavior: "smooth", block: "center" });
      }
    };

    new LandingForm({
      formElement: formEl,
      onSubmitStart: function (payload) {
        if (iti && typeof iti.getNumber === "function") {
          var e164 = iti.getNumber();
          if (e164) payload.fields.phone = e164;
        }
        setStatus("Submitting…", "pending");
      },
      onSuccess: handleSuccess,
      onError: function (message, fieldErrors) {
        Object.keys(fieldErrors || {}).forEach(function (n) {
          var msg = typeof fieldErrors[n] === "string" ? fieldErrors[n] : "Please check this field.";
          setFieldError(n, msg);
        });
        setStatus(message, "error");
      },
    })
      ._wrapValidation(validate)
      .bind();
  }

  // ── Legacy HubSpot iframe code below — unused after the migration to our
  // bridge. Kept as a safety net but not invoked by initSubscriptionV2.
  function _unusedHubspotStub(root, attempt) {
    var demoForms = getAnimationTargets(
      root.querySelectorAll("[data-sub-v2-demo-form]"),
    );

    if (!demoForms.length) return;

    var pending = false;

    demoForms.forEach(function (demoForm) {
      if (!initSingleHubspotDemoForm(demoForm)) {
        pending = true;
      }
    });

    if (pending && attempt < 40) {
      window.setTimeout(function () {
        initHubspotDemoForms(root, attempt + 1);
      }, 250);
    }
  }

  function initSingleHubspotDemoForm(demoForm) {
    if (!demoForm) return true;
    var isModal = demoForm.classList.contains("sub-v2-modal-demo__form");

    if (demoForm.querySelector(".hs-form")) {
      customizeHubspotDemoForm(demoForm);
      return true;
    }

    var iframe = demoForm.querySelector("iframe[id^='hs-form-iframe']");
    if (iframe) {
      return customizeHubspotDemoIframe(iframe, isModal);
    }

    if (!(window.hbspt && window.hbspt.forms && window.hbspt.forms.create)) {
      return false;
    }

    if (demoForm.hasAttribute("data-v2-demo-rendered")) return true;

    demoForm.setAttribute("data-v2-demo-rendered", "true");

    window.hbspt.forms.create({
      region: demoForm.getAttribute("data-region") || "na2",
      portalId: demoForm.getAttribute("data-portal-id"),
      formId: demoForm.getAttribute("data-form-id"),
      target: "#" + demoForm.id,
      onFormReady: function () {
        window.setTimeout(function () {
          customizeHubspotDemoForm(demoForm);
        }, 0);
      },
    });

    return true;
  }

  function customizeHubspotDemoIframe(iframe, isModal) {
    if (!iframe) return false;
    if (iframe.hasAttribute("data-v2-demo-iframe-ready")) return true;

    try {
      var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
      var head = iframeDoc && iframeDoc.head;
      var form = iframeDoc && iframeDoc.querySelector(".hs-form");

      if (!head || !form) return false;

      if (!head.querySelector(".sub-v2-hs-style")) {
        var css =
          ".hs-form__virality-link{display:none!important;}" +
          "body{margin:0!important;background:transparent!important;font-family:'Poppins',sans-serif!important;}" +
          ".hs-form{margin:0!important;font-family:'Poppins',sans-serif!important;}" +
          ".hs-form fieldset{max-width:none!important;margin:0!important;padding:0!important;border:0!important;}" +
          ".hs-form-field{margin:0!important;max-width:none!important;width:100%!important;float:none!important;}" +
          ".sub-v2-hs-grid{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;column-gap:" + (isModal ? "30px" : "83px") + "!important;row-gap:" + (isModal ? "18px" : "29px") + "!important;align-items:end!important;}" +
          ".sub-v2-hs-actions{align-self:end!important;justify-self:end!important;padding-top:0!important;margin:0!important;}" +
          ".hs-form label{display:block!important;margin:0 0 " + (isModal ? "9px" : "10px") + "!important;font-size:" + (isModal ? "16px" : "24px") + "!important;line-height:" + (isModal ? "24px" : "36px") + "!important;font-weight:400!important;color:#000!important;letter-spacing:0!important;}" +
          ".hs-form-required{color:#ff3b30!important;}" +
          ".hs-form .input,.hs-form .hs-input,.hs-form .inputs-list{margin:0!important;}" +
          ".hs-input:not([type='checkbox']):not([type='radio']),select,textarea{width:100%!important;height:" + (isModal ? "46px" : "49px") + "!important;padding:0 " + (isModal ? "16px" : "28px") + "!important;border:1.5px solid " + (isModal ? "#e5e7eb" : "rgba(0,0,0,0.12)") + "!important;border-radius:" + (isModal ? "10px" : "50px") + "!important;background:#fff!important;box-shadow:none!important;font-family:'Poppins',sans-serif!important;font-size:" + (isModal ? "16px" : "18px") + "!important;line-height:" + (isModal ? "24px" : "36px") + "!important;color:#3e4042!important;}" +
          "textarea.hs-input{min-height:" + (isModal ? "108px" : "140px") + "!important;height:auto!important;padding:" + (isModal ? "14px 16px" : "18px 28px") + "!important;border-radius:" + (isModal ? "12px" : "30px") + "!important;resize:vertical!important;}" +
          ".hs-input::placeholder,textarea::placeholder{color:#c0c2c6!important;opacity:1!important;}" +
          ".hs-input:focus,select:focus,textarea:focus{outline:none!important;border-color:rgba(30,75,203,0.45)!important;box-shadow:0 0 0 4px rgba(30,75,203,0.08)!important;}" +
          "select{-webkit-appearance:none!important;appearance:none!important;background-image:linear-gradient(45deg,transparent 50%,#3e4042 50%),linear-gradient(135deg,#3e4042 50%,transparent 50%)!important;background-position:calc(100% - 28px) calc(50% - 3px),calc(100% - 20px) calc(50% - 3px)!important;background-size:8px 8px,8px 8px!important;background-repeat:no-repeat!important;padding-right:54px!important;}" +
          ".sub-v2-hs-field--phone-number .input,.sub-v2-hs-field--whatsapp-phone-number .input{display:grid!important;grid-template-columns:" + (isModal ? "122px" : "115px") + " minmax(0,1fr)!important;gap:" + (isModal ? "0" : "18px") + "!important;}" +
          ".sub-v2-hs-field--phone-number select,.sub-v2-hs-field--whatsapp-phone-number select{padding-left:" + (isModal ? "12px" : "22px") + "!important;padding-right:40px!important;background-position:calc(100% - 18px) calc(50% - 3px),calc(100% - 10px) calc(50% - 3px)!important;}" +
          ".legal-consent-container,.hs-richtext,.submitted-message{margin-top:" + (isModal ? "16px" : "22px") + "!important;font-size:" + (isModal ? "12px" : "13px") + "!important;line-height:" + (isModal ? "18px" : "21px") + "!important;color:#667085!important;}" +
          ".legal-consent-container a,.hs-richtext a{color:#1e4bcb!important;}" +
          ".actions{margin:0!important;padding:0!important;display:flex!important;justify-content:" + (isModal ? "flex-end" : "flex-end") + "!important;}" +
          ".actions .hs-button,.actions input[type='submit'],.actions button{-webkit-appearance:none!important;appearance:none!important;min-width:" + (isModal ? "213px" : "207px") + "!important;height:" + (isModal ? "46px" : "58px") + "!important;padding:0 32px!important;border:0!important;border-radius:" + (isModal ? "10px" : "999px") + "!important;background:" + (isModal ? "#32b52b" : "#1e4bcb") + "!important;color:#fff!important;box-shadow:" + (isModal ? "0 8px 18px rgba(50,181,43,0.22)" : "none") + "!important;font-family:'Poppins',sans-serif!important;font-size:" + (isModal ? "18px" : "22px") + "!important;line-height:" + (isModal ? "22px" : "20px") + "!important;font-weight:600!important;letter-spacing:-0.02em!important;}" +
          "@media (max-width:767px){.sub-v2-hs-grid{grid-template-columns:minmax(0,1fr)!important;row-gap:" + (isModal ? "16px" : "20px") + "!important;}.sub-v2-hs-field--phone-number .input,.sub-v2-hs-field--whatsapp-phone-number .input{grid-template-columns:" + (isModal ? "108px" : "96px") + " minmax(0,1fr)!important;gap:" + (isModal ? "0" : "12px") + "!important;}.hs-form label{font-size:" + (isModal ? "16px" : "18px") + "!important;line-height:" + (isModal ? "24px" : "28px") + "!important;margin-bottom:8px!important;}.hs-input:not([type='checkbox']):not([type='radio']),select,textarea{height:" + (isModal ? "46px" : "52px") + "!important;font-size:16px!important;line-height:24px!important;padding:0 " + (isModal ? "14px" : "20px") + "!important;border-radius:" + (isModal ? "10px" : "50px") + "!important;}textarea.hs-input{padding:" + (isModal ? "14px" : "16px 20px") + "!important;}.sub-v2-hs-actions,.actions{width:100%!important;justify-content:" + (isModal ? "flex-end" : "stretch") + "!important;}.actions .hs-button,.actions input[type='submit'],.actions button{" + (isModal ? "width:auto!important;min-width:180px!important;" : "width:100%!important;min-width:0!important;") + "font-size:" + (isModal ? "18px" : "18px") + "!important;}}";

        var styleTag = iframeDoc.createElement("style");
        styleTag.className = "sub-v2-hs-style";
        styleTag.appendChild(iframeDoc.createTextNode(css));
        head.appendChild(styleTag);
      }

      reorderHubspotFields(form);
      tagHubspotFields(form);
      updateHubspotLabels(form);
      updateHubspotPlaceholders(form);
      updateHubspotSubmit(form);

      iframe.setAttribute("data-v2-demo-iframe-ready", "true");
      scheduleScrollRefresh();
      return true;
    } catch (error) {
      return false;
    }
  }

  function customizeHubspotDemoForm(demoForm) {
    var form = demoForm.querySelector(".hs-form");
    if (!form || form.hasAttribute("data-v2-demo-ready")) return;

    reorderHubspotFields(form);
    tagHubspotFields(form);
    updateHubspotLabels(form);
    updateHubspotPlaceholders(form);
    updateHubspotSubmit(form);

    form.setAttribute("data-v2-demo-ready", "true");
    scheduleScrollRefresh();
  }

  function markAnimationTargets(root) {
    toArray(
      root.querySelectorAll(
        "[data-animate], [data-hero-animate], [data-scroll-pin], [data-pin-animate], [data-stagger-item]",
      ),
    ).forEach(function (element) {
      element.classList.add("sub-v2-animate");
    });
  }

  function createHeroTimeline(root, options) {
    var hero = root.querySelector("[data-sub-v2-hero]") || root.querySelector(".sub-v2-hero");
    if (!hero) return;

    var targets = getAnimationTargets(
      hero.querySelectorAll("[data-hero-animate]"),
    );

    if (!targets.length) return;

    gsap.set(targets, {
      autoAlpha: 0,
      y: options.distance * 0.5,
    });

    var timeline = gsap.timeline({
      defaults: { ease: "power3.out" },
    });

    timeline.to(targets, {
      autoAlpha: 1,
      y: 0,
      duration: options.duration,
      stagger: 0.1,
      clearProps: "transform,opacity,visibility",
    });
  }

  function createFadeRevealBatch(root, selector, options) {
    getAnimationTargets(root.querySelectorAll(selector)).forEach(function (
      element,
    ) {
      createFadeReveal(element, options);
    });
  }

  function createGenericStaggerBatch(root, selector, options) {
    toArray(root.querySelectorAll(selector)).forEach(function (group) {
      var targets = getAnimationTargets(
        group.querySelectorAll(
          ".sub-v2-tool-card, .sub-v2-testimonial-card, .sub-v2-pricing-card, .sub-v2-feature-card, [data-stagger-item]",
        ),
      );

      if (!targets.length) return;

      createStaggerReveal(group, {
        targets: targets,
        distance: options.distance,
        duration: options.duration,
        stagger: options.stagger,
        start: options.start,
      });
    });
  }

  function createFadeReveal(element, options) {
    if (!element) return;

    gsap.set(element, {
      autoAlpha: 0,
      y: options.distance * 0.6,
    });

    ScrollTrigger.create({
      trigger: element,
      start: options.start || "top 90%",
      once: true,
      onEnter: function () {
        gsap.to(element, {
          autoAlpha: 1,
          y: 0,
          duration: options.duration,
          ease: "power3.out",
          clearProps: "transform,opacity,visibility",
        });
      },
    });
  }

  function createGrowStageReveal(root, options) {
    var stage = root.querySelector(".sub-v2-grow__stage");
    var card = root.querySelector("[data-animate='grow-card']");

    if (!stage || !card) return;

    gsap.set(card, {
      autoAlpha: 0,
      y: options.distance * 0.6,
    });

    ScrollTrigger.create({
      trigger: stage,
      start: options.start || "top 84%",
      once: true,
      onEnter: function () {
        gsap.to(card, {
          autoAlpha: 1,
          y: 0,
          duration: options.duration,
          ease: "power3.out",
          clearProps: "transform,opacity,visibility",
        });
      },
    });
  }

  function createStaggerReveal(group, options) {
    if (!group || !options.targets.length) return;

    gsap.set(options.targets, {
      autoAlpha: 0,
      y: options.distance * 0.5,
    });

    ScrollTrigger.create({
      trigger: group,
      start: options.start || "top 90%",
      once: true,
      onEnter: function () {
        gsap.to(options.targets, {
          autoAlpha: 1,
          y: 0,
          duration: options.duration,
          ease: "power3.out",
          stagger: options.stagger || 0.1,
          clearProps: "transform,opacity,visibility",
        });
      },
    });
  }

  function createPinnedSections(root) {
    toArray(root.querySelectorAll("[data-scroll-pin]")).forEach(function (
      section,
    ) {
      var animatedItems = getAnimationTargets(
        section.querySelectorAll("[data-pin-animate], .sub-v2-pin-item, .sub-v2-pin-visual"),
      );

      if (animatedItems.length) {
        gsap.fromTo(
          animatedItems,
          {
            y: 28,
            opacity: 0.5,
          },
          {
            y: 0,
            opacity: 1,
            stagger: 0.1,
            ease: "power1.inOut",
            scrollTrigger: {
              trigger: section,
              start: "top top",
              end: "+=100%",
              scrub: 1,
              pin: true,
              anticipatePin: 1,
            },
          },
        );
      } else {
        ScrollTrigger.create({
          trigger: section,
          start: "top top",
          end: "+=100%",
          scrub: 1,
          pin: true,
          anticipatePin: 1,
        });
      }
    });
  }

  function bindSliderRefreshEvents($slider) {
    $slider.on(
      "setPosition.subV2Scroll afterChange.subV2Scroll",
      function () {
        scheduleScrollRefresh();
      },
    );
  }

  function scheduleAnimationReset(root) {
    if (!root) return;

    window.clearTimeout(animationResetTimer);
    animationResetTimer = window.setTimeout(function () {
      root.__subV2AnimationCleanup = initScrollAnimations(root);
    }, 180);
  }

  function scheduleScrollRefresh() {
    if (!ScrollTrigger || isReducedMotionPreferred()) return;

    window.clearTimeout(scrollRefreshTimer);
    scrollRefreshTimer = window.setTimeout(function () {
      ScrollTrigger.refresh();
    }, 120);
  }

  function getAnimationTargets(collection) {
    return toArray(collection).filter(function (element) {
      return (
        element &&
        !(element.classList && element.classList.contains("slick-cloned"))
      );
    });
  }

  function toArray(collection) {
    return Array.prototype.slice.call(collection || []);
  }

  function isReducedMotionPreferred() {
    return !!(
      window.matchMedia &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches
    );
  }

  function reorderHubspotFields(form) {
    if (!form || form.hasAttribute("data-v2-ordered")) return;

    var doc = form.ownerDocument;
    var fields = Array.prototype.slice.call(
      form.querySelectorAll(".hs-form-field"),
    );
    if (!fields.length) return;

    var grid = doc.createElement("div");
    grid.className = "sub-v2-hs-grid";

    var wantedOrder = [
      "name",
      "company name",
      "email",
      "state/ region",
      "phone number",
      "preferred language",
      "your industry",
    ];

    wantedOrder.forEach(function (targetLabel) {
      var field = findHubspotField(fields, targetLabel);
      if (!field) return;
      grid.appendChild(field);
    });

    fields.forEach(function (field) {
      if (!grid.contains(field)) grid.appendChild(field);
    });

    var actions = form.querySelector(".actions");
    if (actions) {
      actions.classList.add("sub-v2-hs-actions");
      grid.appendChild(actions);
    }

    var legalConsent = form.querySelector(".legal-consent-container");

    var firstFieldset = form.querySelector("fieldset");
    if (firstFieldset) {
      form.insertBefore(grid, firstFieldset);
      form.querySelectorAll("fieldset").forEach(function (fieldset) {
        fieldset.remove();
      });
    } else {
      form.insertBefore(grid, form.firstChild);
    }

    if (legalConsent) form.appendChild(legalConsent);

    form.setAttribute("data-v2-ordered", "true");
  }

  function tagHubspotFields(form) {
    form.querySelectorAll(".hs-form-field").forEach(function (field) {
      var label = field.querySelector("label");
      var slug = normalizeHubspotLabel(label ? label.textContent : "")
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "");

      if (slug) {
        field.classList.add("sub-v2-hs-field--" + slug);
      }
    });
  }

  function updateHubspotLabels(form) {
    var labels = {
      "name": "Name",
      "company name": "Company Name",
      "email": "Email",
      "state/ region": "State/ Region",
      "phone number": "WhatsApp Phone Number",
      "preferred language": "Preferred Language",
      "your industry": "Your Industry",
    };

    Object.keys(labels).forEach(function (key) {
      var field = findHubspotField(
        Array.prototype.slice.call(form.querySelectorAll(".hs-form-field")),
        key,
      );
      if (!field) return;
      setHubspotFieldLabel(field, labels[key]);
      field.removeAttribute("data-v2-used");
    });
  }

  function updateHubspotPlaceholders(form) {
    var placeholders = {
      "name": "Your name",
      "company name": "Company name",
      "email": "Youremail@example.com",
      "phone number": "(+60)1234 5678",
      "state/ region": "Region",
      "preferred language": "Preferred language",
      "your industry": "Select your industry",
    };

    Object.keys(placeholders).forEach(function (key) {
      var field = findHubspotField(
        Array.prototype.slice.call(form.querySelectorAll(".hs-form-field")),
        key,
      );
      if (!field) return;
      setHubspotFieldPlaceholder(field, key, placeholders[key]);
      field.removeAttribute("data-v2-used");
    });
  }

  function updateHubspotSubmit(form) {
    var submit =
      form.querySelector(".actions input[type='submit']") ||
      form.querySelector(".actions .hs-button") ||
      form.querySelector(".actions button");

    if (!submit) return;

    if (typeof submit.value === "string") {
      submit.value = "Submit";
    }

    submit.textContent = "Submit";
  }

  function findHubspotField(fields, targetLabel) {
    var normalizedTarget = normalizeHubspotLabel(targetLabel);

    for (var index = 0; index < fields.length; index += 1) {
      var field = fields[index];
      if (field.hasAttribute("data-v2-used")) continue;

      var label = field.querySelector("label");
      var normalizedLabel = normalizeHubspotLabel(
        label ? label.textContent : "",
      );

      if (
        normalizedLabel === normalizedTarget ||
        normalizedLabel.indexOf(normalizedTarget) !== -1
      ) {
        field.setAttribute("data-v2-used", "true");
        return field;
      }
    }

    return null;
  }

  function setHubspotFieldLabel(field, text) {
    var label = field.querySelector("label");
    if (!label) return;
    var doc = label.ownerDocument;

    var isRequired = !!label.querySelector(".hs-form-required");
    label.textContent = text;

    if (isRequired) {
      label.appendChild(doc.createTextNode(" "));

      var required = doc.createElement("span");
      required.className = "hs-form-required";
      required.textContent = "*";
      label.appendChild(required);
    }
  }

  function setHubspotFieldPlaceholder(field, key, text) {
    var controls = Array.prototype.slice.call(
      field.querySelectorAll(
        "input:not([type='hidden']):not([type='submit']), select, textarea",
      ),
    );

    if (!controls.length) return;

    if (key === "phone number" && controls.length > 1) {
      controls.forEach(function (control, index) {
        if (control.tagName === "SELECT" && index === 0) {
          setHubspotSelectPlaceholder(control, "+60");
          return;
        }

        if (control.tagName !== "SELECT") {
          control.setAttribute("placeholder", text);
        }
      });
      return;
    }

    controls.forEach(function (control) {
      if (control.tagName === "SELECT") {
        setHubspotSelectPlaceholder(control, text);
        return;
      }

      control.setAttribute("placeholder", text);
    });
  }

  function setHubspotSelectPlaceholder(select, text) {
    var firstOption =
      select.querySelector("option[value='']") || select.querySelector("option");

    if (!firstOption) return;

    firstOption.textContent = text;
  }

  function normalizeHubspotLabel(value) {
    return String(value || "")
      .replace(/\*/g, "")
      .replace(/\s+/g, " ")
      .trim()
      .toLowerCase();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initSubscriptionV2);
  } else {
    initSubscriptionV2();
  }

})();
