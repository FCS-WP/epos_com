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
      // Touch smoothing — 0 = native scroll, 1 = full lerp.
      // 0.3 feels deliberate without breaking finger-to-content tracking;
      // higher values risk feeling like input lag on iOS Safari.
      smoothTouch: 0.3,
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
    // Modal lives outside `root` (rendered as a sibling of <main> in
    // template.php so it isn't disabled by the smoother's pointer-events).
    // Look for it page-wide.
    var modal = document.querySelector("[data-sub-v2-demo-modal]");
    if (!modal || modal.hasAttribute("data-v2-demo-modal-bound")) return;

    var openers = document.querySelectorAll("[data-sub-v2-demo-modal-open]");
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
  function initLandingForms(_root) {
    // Search page-wide because the modal-demo form lives OUTSIDE the
    // subscription-v2 root (rendered as a sibling of <main> so it stays
    // clickable when the smoother wrapper has pointer-events: none).
    var forms = document.querySelectorAll('form[data-landing-form="hubspot"]');
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

    // Friendly per-field error messages. Used by BOTH our client-side
    // pre-flight validation and the response handler that surfaces field
    // errors HubSpot returns (HubSpot's raw text reads like
    // "Required field 'state_dropdown' is missing" — not user-facing).
    var FIELD_LABELS = {
      lastname:       "your name",
      email:          "your email",
      phone:          "your WhatsApp phone number",
      your_industry:  "your industry",
      state_dropdown: "your state / region",
      hs_language:    "your preferred language",
      company:        "your company name",
    };
    var friendlyFieldError = function (name, kind) {
      var label = FIELD_LABELS[name] || "this field";
      // Verbs differ between text inputs and dropdowns for cleaner copy.
      var isSelect = !!formEl.querySelector('select[name="' + name + '"]');
      var verb = isSelect ? "Please select " : "Please enter ";
      if (kind === "invalid") {
        return name === "email"
          ? "Please enter a valid email address."
          : (name === "phone" ? "Please enter a valid phone number." : "Please check " + label + ".");
      }
      return verb + label + ".";
    };

    var validate = function () {
      var firstInvalid = null;
      formEl.querySelectorAll("[data-error-for]").forEach(function (n) { n.textContent = ""; });
      formEl.querySelectorAll("[aria-invalid]").forEach(function (n) { n.removeAttribute("aria-invalid"); });

      var name = formEl.querySelector('[name="lastname"]');
      if (name && !name.value.trim()) {
        setFieldError("lastname", friendlyFieldError("lastname"));
        firstInvalid = firstInvalid || name;
      }
      var email = formEl.querySelector('[name="email"]');
      if (email) {
        var v = email.value.trim();
        if (!v) {
          setFieldError("email", friendlyFieldError("email"));
          firstInvalid = firstInvalid || email;
        } else if (!EMAIL_RE.test(v)) {
          setFieldError("email", friendlyFieldError("email", "invalid"));
          firstInvalid = firstInvalid || email;
        }
      }
      if (phoneEl) {
        if (!phoneEl.value.trim()) {
          setFieldError("phone", friendlyFieldError("phone"));
          firstInvalid = firstInvalid || phoneEl;
        } else if (iti && typeof iti.isValidNumber === "function" && iti.isValidNumber() === false) {
          setFieldError("phone", friendlyFieldError("phone", "invalid"));
          firstInvalid = firstInvalid || phoneEl;
        }
      }
      var industry = formEl.querySelector('[name="your_industry"]');
      if (industry && industry.required && !industry.value) {
        setFieldError("your_industry", friendlyFieldError("your_industry"));
        firstInvalid = firstInvalid || industry;
      }
      var state = formEl.querySelector('[name="state_dropdown"]');
      if (state && state.required && !state.value) {
        setFieldError("state_dropdown", friendlyFieldError("state_dropdown"));
        firstInvalid = firstInvalid || state;
      }

      if (firstInvalid) {
        try { firstInvalid.focus({ preventScroll: false }); } catch (_) {}
        return false;
      }
      return true;
    };

    // In-flight indicator: toggling data-submitting on the form swaps the
    // button label for a CSS spinner (no status text needed).
    var setSubmitting = function (on) {
      if (on) formEl.setAttribute("data-submitting", "true");
      else formEl.removeAttribute("data-submitting");
    };

    var handleSuccess = function () {
      // Two-phase animation:
      //   1. Form fades out (CSS transition on .is-leaving) — 200ms
      //   2. Form display:none, success block painted in initial state,
      //      then on the next frame .is-visible flips on so children's
      //      transitions kick in (fade + slide-up, staggered).
      var shell = formEl.closest("[data-form-shell]");
      var successEl = shell ? shell.querySelector("[data-form-success]") : null;

      setStatus("", ""); // clear any prior status text
      setSubmitting(false);

      formEl.classList.add("is-leaving");

      var FADE_OUT_MS = 200;
      window.setTimeout(function () {
        formEl.style.display = "none";

        if (successEl) {
          // Reveal in its initial (pre-animation) state.
          successEl.removeAttribute("hidden");

          // Force a paint at the initial state, THEN on the next frame
          // flip .is-visible so the CSS transition runs. Without the rAF
          // the browser may collapse both states into one and skip the
          // animation entirely.
          window.requestAnimationFrame(function () {
            window.requestAnimationFrame(function () {
              successEl.classList.add("is-visible");
            });
          });
        }

        var anchor = successEl || formEl.closest(".sub-v2-demo, .sub-v2-modal-demo__content") || statusEl;
        if (anchor && typeof anchor.scrollIntoView === "function") {
          anchor.scrollIntoView({ behavior: "smooth", block: "center" });
        }
      }, FADE_OUT_MS);
    };

    // ── MOCK MODE (UI preview only) ─────────────────────
    // When the form has data-mock-success, skip the bridge entirely and just
    // show the success UI on submit. To disable: remove the attribute from
    // partials/_form.php. Validation still runs.
    if (formEl.hasAttribute("data-mock-success")) {
      formEl.addEventListener("submit", function (e) {
        e.preventDefault();
        if (!validate()) return;
        setSubmitting(true);
        // Slight delay so the spinner is visible before swap.
        setTimeout(handleSuccess, 600);
      });
      return;
    }

    new LandingForm({
      formElement: formEl,
      onSubmitStart: function (payload) {
        if (iti && typeof iti.getNumber === "function") {
          var e164 = iti.getNumber();
          if (e164) payload.fields.phone = e164;
        }
        setSubmitting(true);
        setStatus("", ""); // any prior error text is cleared as we retry
      },
      onSuccess: handleSuccess,
      onError: function (message, fieldErrors) {
        // HubSpot's raw error text (e.g. "Required field 'state_dropdown'
        // is missing") is developer-facing — translate to the user-friendly
        // version using our per-field label map.
        var keys = Object.keys(fieldErrors || {});
        keys.forEach(function (n) {
          var raw = typeof fieldErrors[n] === "string" ? fieldErrors[n] : "";
          var kind = /\binvalid\b|\bnot a valid\b/i.test(raw) ? "invalid" : "missing";
          setFieldError(n, friendlyFieldError(n, kind));
        });

        // Top-level message: prefer something specific to what failed.
        if (keys.length) {
          setStatus("Please check the highlighted fields and try again.", "error");
        } else {
          setStatus(
            "Something went wrong. Please try again in a moment.",
            "error"
          );
        }

        // Restore submit button so the user can retry.
        setSubmitting(false);
      },
    })
      ._wrapValidation(validate)
      .bind();
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

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initSubscriptionV2);
  } else {
    initSubscriptionV2();
  }
})();
