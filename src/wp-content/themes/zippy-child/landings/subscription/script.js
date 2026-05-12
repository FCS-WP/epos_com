// Subscription V2 landing.
//
// Stack: Lenis (smooth scroll) + GSAP + ScrollTrigger (animations) + Slick (sliders)
// Pattern mirrors jim.com: Lenis drives smooth scroll, GSAP ScrollTrigger drives reveals.
// Lenis scroll position is fed into GSAP ticker so ScrollTrigger stays in sync.
//
// HubSpot form: submitted via our REST bridge — see initLandingForms() / form-bridge.js
import { LandingForm } from "../_shared/form-bridge";

(function () {
  "use strict";

  var lenis = null;

  // ── Lenis smooth scroll ───────────────────────────────────────────────────
  // Initialised as early as possible (before DOMContentLoaded) so the very
  // first wheel event is already intercepted.
  function initLenis() {
    if (!window.Lenis) return;
    if (isReducedMotionPreferred()) return;

    var isTouchDevice = window.matchMedia("(max-width: 767px)").matches;

    lenis = new window.Lenis({
      duration: isTouchDevice ? 1.6 : 1.2,
      smoothWheel: true,
      touchMultiplier: isTouchDevice ? 1.4 : 1.5,
      syncTouch: isTouchDevice,       // native-feel momentum on mobile
      syncTouchLerp: 0.04,            // softer deceleration on touch end
      overscroll: false,
    });

    // Feed Lenis into GSAP ticker so ScrollTrigger stays in sync.
    // Read window.gsap lazily here — libs are guaranteed loaded by this point
    // because initLenis() is called after DOMContentLoaded (all <script> tags
    // preceding this bundle have already executed).
    var g = window.gsap;
    if (g) {
      g.ticker.add(function (time) { lenis.raf(time * 1000); });
      g.ticker.lagSmoothing(0);
    } else {
      (function raf(time) { lenis.raf(time); requestAnimationFrame(raf); })(0);
    }
  }

  // ── Scroll animations (GSAP ScrollTrigger) ───────────────────────────────
  function initScrollAnimations(root) {
    // Read lazily — guaranteed available after DOMContentLoaded
    var g  = window.gsap;
    var ST = window.ScrollTrigger;

    if (!g || !ST || isReducedMotionPreferred()) {
      toArray(root.querySelectorAll("[data-hero-animate]")).forEach(function (el) {
        el.style.visibility = "visible";
        el.style.opacity    = "1";
      });
      return;
    }

    g.registerPlugin(ST);

    var isMobile = window.matchMedia("(max-width: 767px)").matches;
    var fadeY    = isMobile ? 20 : 52;

    // Pre-hide hero elements immediately so there's no flash before fromTo fires
    g.set(root.querySelectorAll("[data-hero-animate]"), { autoAlpha: 0 });

    // Shorthand helpers
    function fadeUp(targets, opts) {
      g.fromTo(targets,
        { autoAlpha: 0, y: fadeY },
        Object.assign({
          autoAlpha: 1, y: 0,
          duration: isMobile ? 0.7 : 1.0, ease: "expo.out",
          clearProps: "transform,opacity,visibility",
        }, opts)
      );
    }

    function staggerUp(targets, trigger, opts) {
      if (!targets.length) return;
      g.fromTo(targets,
        { autoAlpha: 0, y: isMobile ? 16 : 40 },
        Object.assign({
          autoAlpha: 1, y: 0,
          duration: isMobile ? 0.65 : 0.9, ease: "expo.out",
          stagger: isMobile ? 0.06 : 0.11,
          clearProps: "transform,opacity,visibility",
          scrollTrigger: { trigger: trigger, start: isMobile ? "top 92%" : "top 88%", once: true },
        }, opts)
      );
    }

    // ── 1. Hero: staggered fade + slide-up on page load ──────────────────
    var heroEls = toArray(root.querySelectorAll("[data-hero-animate]"));
    if (heroEls.length) {
      g.fromTo(heroEls,
        { autoAlpha: 0, y: isMobile ? 24 : 60 },
        {
          autoAlpha: 1, y: 0,
          duration: isMobile ? 0.8 : 1.2, ease: "expo.out",
          stagger: isMobile ? 0.08 : 0.12,
          clearProps: "transform,opacity,visibility",
          delay: 0.1,
        }
      );
    }

    // ── 2. Section headings: line-clip reveal ────────────────────────────
    toArray(root.querySelectorAll("[data-animate-group='section-head']")).forEach(function (group) {
      var lineEls = toArray(group.querySelectorAll(
        ".sub-v2-kicker, .sub-v2-section-title, .sub-v2-testimonials__desc"
      ));
      lineEls.forEach(wrapInRevealLine);

      var inners = toArray(group.querySelectorAll(".sv2-reveal-line__inner"));
      if (!inners.length) return;

      g.fromTo(inners,
        { yPercent: 110 },
        {
          yPercent: 0,
          duration: isMobile ? 0.75 : 1.1, ease: "expo.out",
          stagger: isMobile ? 0.07 : 0.1,
          clearProps: "transform",
          scrollTrigger: { trigger: group, start: isMobile ? "top 93%" : "top 88%", once: true },
        }
      );
    });

    // ── 3. Generic [data-animate="fade-up"] ──────────────────────────────
    toArray(root.querySelectorAll("[data-animate='fade-up']")).forEach(function (el) {
      fadeUp(el, { scrollTrigger: { trigger: el, start: "top 90%", once: true } });
    });

    // ── 4. Stagger groups ([data-animate="stagger"]) ─────────────────────
    toArray(root.querySelectorAll("[data-animate='stagger']")).forEach(function (group) {
      // Skip grow-features — handled separately after grow-card reveals
      if (group.classList.contains("sub-v2-grow__features")) return;

      var items = toArray(group.querySelectorAll("[data-stagger-item]"))
        .filter(function (el) { return !el.classList.contains("slick-cloned"); });
      staggerUp(items, group);
    });

    // ── 5. Grow section ───────────────────────────────────────────────────
    var growStage = root.querySelector(".sub-v2-grow__stage");
    var growCard  = root.querySelector(".sub-v2-grow__card");
    var growLeft  = root.querySelector(".sub-v2-grow__photo--left");
    var growRight = root.querySelector(".sub-v2-grow__photo--right");

    if (growStage) {
      // Photos slide in from sides
      if (growLeft) {
        g.fromTo(growLeft,
          { autoAlpha: 0, x: isMobile ? 0 : -60, y: isMobile ? 20 : 0 },
          {
            autoAlpha: 1, x: 0, y: 0,
            duration: isMobile ? 0.7 : 1.1, ease: "expo.out",
            clearProps: "transform,opacity,visibility",
            scrollTrigger: { trigger: growStage, start: isMobile ? "top 93%" : "top 85%", once: true },
          }
        );
      }
      if (growRight) {
        g.fromTo(growRight,
          { autoAlpha: 0, x: isMobile ? 0 : 60, y: isMobile ? 20 : 0 },
          {
            autoAlpha: 1, x: 0, y: 0,
            duration: isMobile ? 0.7 : 1.1, ease: "expo.out",
            clearProps: "transform,opacity,visibility",
            scrollTrigger: { trigger: growStage, start: isMobile ? "top 93%" : "top 85%", once: true },
          }
        );
      }

      // Card pops up from centre
      if (growCard) {
        g.fromTo(growCard,
          { autoAlpha: 0, y: isMobile ? 28 : 50, scale: isMobile ? 0.98 : 0.96 },
          {
            autoAlpha: 1, y: 0, scale: 1,
            duration: isMobile ? 0.7 : 1.0, ease: "expo.out",
            clearProps: "transform,opacity,visibility",
            scrollTrigger: { trigger: growStage, start: isMobile ? "top 93%" : "top 80%", once: true },
            onComplete: function () {
              // After card is visible, stagger-reveal the feature list inside it
              var featureItems = toArray(growCard.querySelectorAll("[data-stagger-item]"));
              staggerUp(featureItems, growCard, {
                scrollTrigger: { trigger: growCard, start: "top 85%", once: true },
              });
            },
          }
        );
      }
    }

    window.setTimeout(function () { ST.refresh(); }, 450);
  }

  // Fade-up a slider wrapper AFTER slick has laid out — safe because the
  // element is visible during slick init (no autoAlpha:0 set beforehand).
  function animateSliderWrap(wrap) {
    if (!wrap) return;
    var g  = window.gsap;
    var ST = window.ScrollTrigger;
    if (!g || !ST) return;
    var mob = window.matchMedia("(max-width: 767px)").matches;
    g.fromTo(wrap,
      { autoAlpha: 0, y: mob ? 20 : 40 },
      {
        autoAlpha: 1, y: 0,
        duration: mob ? 0.65 : 1.0, ease: "expo.out",
        clearProps: "transform,opacity,visibility",
        scrollTrigger: { trigger: wrap, start: mob ? "top 93%" : "top 90%", once: true },
      }
    );
  }

  // Wrap el in a clip container so its text can slide up from below
  function wrapInRevealLine(el) {
    if (el.querySelector(".sv2-reveal-line__inner")) return;
    el.classList.add("sv2-reveal-line");
    var inner = document.createElement("span");
    inner.className = "sv2-reveal-line__inner";
    while (el.firstChild) { inner.appendChild(el.firstChild); }
    el.appendChild(inner);
  }

  // ── Smooth anchor scroll ─────────────────────────────────────────────────
  function bindSmoothScroll(root) {
    if (root.hasAttribute("data-v2-smooth-scroll-bound")) return;
    root.setAttribute("data-v2-smooth-scroll-bound", "true");

    document.addEventListener("click", function (e) {
      var link = e.target.closest("a[href^='#']");
      if (!link || link.hasAttribute("data-sub-v2-demo-modal-open")) return;

      var hash = link.getAttribute("href");
      if (!hash || hash === "#") return;

      var target = document.querySelector(hash);
      if (!target) return;

      e.preventDefault();

      var header  = document.querySelector(".sub-v2-header");
      var headerH = header ? header.getBoundingClientRect().height : 0;
      var targetTop = target.getBoundingClientRect().top + window.pageYOffset - headerH - 12;

      if (lenis) {
        lenis.scrollTo(targetTop, { duration: 1.4 });
      } else {
        window.scrollTo({ top: targetTop, behavior: "smooth" });
      }
    });
  }

  // ── FAQ accordion ─────────────────────────────────────────────────────────
  function bindFaq(root) {
    var faqRoot = root.querySelector("[data-sub-v2-faq]");
    if (!faqRoot || faqRoot.hasAttribute("data-v2-faq-bound")) return;
    faqRoot.setAttribute("data-v2-faq-bound", "true");

    var faqItems = toArray(faqRoot.querySelectorAll(".sub-v2-faq__item"));

    faqItems.forEach(function (item) {
      var body    = item.querySelector(".sub-v2-faq__body");
      var trigger = item.querySelector(".sub-v2-faq__trigger");
      if (!body || !trigger) return;
      body.removeAttribute("hidden");
      var isOpen = trigger.getAttribute("aria-expanded") === "true";
      if (!isOpen) { body.style.height = "0"; body.style.overflow = "hidden"; }
    });

    faqItems.forEach(function (item) {
      var trigger = item.querySelector(".sub-v2-faq__trigger");
      var body    = item.querySelector(".sub-v2-faq__body");
      var icon    = item.querySelector(".sub-v2-faq__icon");
      if (!trigger || !body) return;

      trigger.addEventListener("click", function () {
        var isOpen = trigger.getAttribute("aria-expanded") === "true";

        // Close others
        faqItems.forEach(function (other) {
          if (other === item) return;
          var ot = other.querySelector(".sub-v2-faq__trigger");
          var ob = other.querySelector(".sub-v2-faq__body");
          var oi = other.querySelector(".sub-v2-faq__icon");
          if (ot && ot.getAttribute("aria-expanded") === "true") {
            ot.setAttribute("aria-expanded", "false");
            collapseBody(ob);
            if (oi) oi.classList.remove("is-open");
          }
        });

        if (isOpen) {
          trigger.setAttribute("aria-expanded", "false");
          collapseBody(body);
          if (icon) icon.classList.remove("is-open");
        } else {
          trigger.setAttribute("aria-expanded", "true");
          expandBody(body);
          if (icon) icon.classList.add("is-open");
        }

        // Let ScrollTrigger recalculate after height change
        if (window.ScrollTrigger) window.setTimeout(function () { window.ScrollTrigger.refresh(); }, 720);
      });
    });
  }

  function expandBody(body) {
    body.style.overflow = "hidden";
    body.style.height = body.scrollHeight + "px";
  }

  function collapseBody(body) {
    body.style.height = body.scrollHeight + "px";
    body.style.overflow = "hidden";
    requestAnimationFrame(function () { body.style.height = "0"; });
  }

  // ── Slick sliders ─────────────────────────────────────────────────────────
  function initToolsSlider(root, attempt) {
    var toolsSlider = root.querySelector("[data-sub-v2-tools-slider]");
    var dotsTarget  = root.querySelector("[data-sub-v2-tools-dots]");
    if (!toolsSlider) return;

    if (!($ && $.fn && $.fn.slick)) {
      if (attempt < 10) window.setTimeout(function () { initToolsSlider(root, attempt + 1); }, 250);
      return;
    }

    var $s = $(toolsSlider);
    if ($s.hasClass("slick-initialized")) $s.slick("unslick");
    var count = $s.children().length;
    if (!count) return;

    var wrap = root.querySelector("[data-animate-slider].sub-v2-tools__slider-wrap");

    var toolsArrowPrev = '<button type="button" class="sub-v2-slider-arrow sub-v2-slider-arrow--prev" aria-label="Previous"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';
    var toolsArrowNext = '<button type="button" class="sub-v2-slider-arrow sub-v2-slider-arrow--next" aria-label="Next"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';

    $s.css("display", "block").slick({
      slidesToShow: Math.min(count, 3),
      slidesToScroll: 1,
      infinite: false,
      autoplay: true,
      autoplaySpeed: 10000,
      arrows: true,
      prevArrow: toolsArrowPrev,
      nextArrow: toolsArrowNext,
      dots: count > 1,
      appendDots: dotsTarget ? $(dotsTarget) : $s,
      speed: 600,
      adaptiveHeight: false,
      responsive: [
        { breakpoint: 1024, settings: { slidesToShow: Math.max(1, Math.min(count, 2)), slidesToScroll: 1, infinite: false, autoplay: true, autoplaySpeed: 10000, arrows: true, prevArrow: toolsArrowPrev, nextArrow: toolsArrowNext, dots: count > 1, appendDots: dotsTarget ? $(dotsTarget) : $s } },
        { breakpoint: 768,  settings: { slidesToShow: 1, slidesToScroll: 1, infinite: false, autoplay: true, autoplaySpeed: 10000, arrows: false, dots: count > 1, appendDots: dotsTarget ? $(dotsTarget) : $s, centerMode: true, centerPadding: "32px" } },
      ],
    });

    $s.on("afterChange.sv2tools", function () {
      if (window.ScrollTrigger) window.ScrollTrigger.refresh();
    });

    // Animate the wrapper after slick finishes layout — avoids GSAP hiding
    // the element before slick can measure its width (broken slide sizes).
    animateSliderWrap(wrap);
  }

  function initTestimonialsSlider(root, attempt) {
    var slider = root.querySelector("[data-sub-v2-testimonials-slider]");
    if (!slider) return;

    if (!($ && $.fn && $.fn.slick)) {
      if (attempt < 10) window.setTimeout(function () { initTestimonialsSlider(root, attempt + 1); }, 250);
      return;
    }

    var $s = $(slider);
    if ($s.hasClass("slick-initialized")) $s.slick("unslick");
    var count = $s.children().length;
    if (!count) return;

    var dotsTarget = root.querySelector("[data-sub-v2-testimonials-dots]");
    var wrap = root.querySelector("[data-animate-slider].sub-v2-testimonials__slider-wrap");

    $s.css("display", "block").slick({
      slidesToShow: Math.min(count, 3),
      slidesToScroll: count > 3 ? 3 : 1,
      infinite: count > 3,
      arrows: false,
      dots: count > 3,
      appendDots: dotsTarget ? $(dotsTarget) : $s,
      speed: 600,
      adaptiveHeight: false,
      responsive: [
        { breakpoint: 1024, settings: { slidesToShow: Math.min(count, 2), slidesToScroll: count > 2 ? 2 : 1, infinite: count > 2, dots: count > 2, appendDots: dotsTarget ? $(dotsTarget) : $s } },
        { breakpoint: 768,  settings: { slidesToShow: 1, slidesToScroll: 1, infinite: count > 1, dots: count > 1, appendDots: dotsTarget ? $(dotsTarget) : $s, adaptiveHeight: false, centerMode: true, centerPadding: "32px" } },
      ],
    });

    animateSliderWrap(wrap);

    $s.on("setPosition.sv2 afterChange.sv2", function () { if (window.ScrollTrigger) window.ScrollTrigger.refresh(); });
  }

  // ── Demo modal ────────────────────────────────────────────────────────────
  function bindDemoModal(_root) {
    var modal  = document.querySelector("[data-sub-v2-demo-modal]");
    if (!modal || modal.hasAttribute("data-v2-demo-modal-bound")) return;

    var closers = modal.querySelectorAll("[data-sub-v2-demo-modal-close]");
    var dialog  = modal.querySelector(".sub-v2-modal-demo__dialog");
    if (!closers.length || !dialog) return;
    modal.setAttribute("data-v2-demo-modal-bound", "true");

    function openModal(e) {
      if (e) e.preventDefault();
      modal.hidden = false;
      modal.setAttribute("aria-hidden", "false");
      document.body.classList.add("modal-open");
      if (lenis) lenis.stop();
      requestAnimationFrame(function () { modal.classList.add("is-open"); dialog.focus(); });
    }

    document.addEventListener("click", function (e) {
      if (e.target.closest("[data-sub-v2-demo-modal-open]")) openModal(e);
    });

    function closeModal() {
      modal.classList.remove("is-open");
      modal.setAttribute("aria-hidden", "true");
      document.body.classList.remove("modal-open");
      window.setTimeout(function () {
        if (modal.getAttribute("aria-hidden") === "true") {
          modal.hidden = true;
          if (lenis) lenis.start();
        }
      }, 180);
    }

    closers.forEach(function (c) { c.addEventListener("click", function (e) { e.preventDefault(); closeModal(); }); });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && modal.getAttribute("aria-hidden") === "false") closeModal();
    });
  }

  // ── Landing forms ─────────────────────────────────────────────────────────
  function initLandingForms(_root) {
    var forms = document.querySelectorAll('form[data-landing-form="hubspot"]');
    if (!forms.length) return;
    forms.forEach(bindLandingForm);
  }

  function bindLandingForm(formEl) {
    if (formEl.hasAttribute("data-v2-form-bound")) return;
    formEl.setAttribute("data-v2-form-bound", "true");

    var statusEl  = formEl.querySelector(".sub-v2-form__status");
    var setStatus = function (msg, kind) {
      if (!statusEl) return;
      statusEl.textContent = msg || "";
      statusEl.dataset.state = kind || "";
    };

    var iti = null;
    var phoneEl = formEl.querySelector('input[name="phone"]');
    if (phoneEl && typeof window.intlTelInput === "function") {
      var cfg = window.LANDINGS_FORM_BRIDGE || {};
      var onlyCountries = (formEl.dataset.phoneCountries || "")
        .split(",").map(function (c) { return c.trim().toLowerCase(); }).filter(Boolean);
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
    formEl.addEventListener("input",  function (e) { if (e.target && e.target.name) clearFieldError(e.target.name); });
    formEl.addEventListener("change", function (e) { if (e.target && e.target.name) clearFieldError(e.target.name); });

    var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var FIELD_LABELS = {
      lastname: "your name", email: "your email", phone: "your WhatsApp phone number",
      your_industry: "your industry", state_dropdown: "your state / region",
      hs_language: "your preferred language", company: "your company name",
    };
    var friendlyFieldError = function (name, kind) {
      var label    = FIELD_LABELS[name] || "this field";
      var isSelect = !!formEl.querySelector('select[name="' + name + '"]');
      var verb     = isSelect ? "Please select " : "Please enter ";
      if (kind === "invalid") {
        return name === "email" ? "Please enter a valid email address."
          : (name === "phone" ? "Please enter a valid phone number." : "Please check " + label + ".");
      }
      return verb + label + ".";
    };

    var validate = function () {
      var firstInvalid = null;
      formEl.querySelectorAll("[data-error-for]").forEach(function (n) { n.textContent = ""; });
      formEl.querySelectorAll("[aria-invalid]").forEach(function (n) { n.removeAttribute("aria-invalid"); });

      var name = formEl.querySelector('[name="lastname"]');
      if (name && !name.value.trim()) { setFieldError("lastname", friendlyFieldError("lastname")); firstInvalid = firstInvalid || name; }

      var email = formEl.querySelector('[name="email"]');
      if (email) {
        var v = email.value.trim();
        if (!v) { setFieldError("email", friendlyFieldError("email")); firstInvalid = firstInvalid || email; }
        else if (!EMAIL_RE.test(v)) { setFieldError("email", friendlyFieldError("email", "invalid")); firstInvalid = firstInvalid || email; }
      }

      if (phoneEl) {
        if (!phoneEl.value.trim()) { setFieldError("phone", friendlyFieldError("phone")); firstInvalid = firstInvalid || phoneEl; }
        else if (iti && typeof iti.isValidNumber === "function" && iti.isValidNumber() === false) { setFieldError("phone", friendlyFieldError("phone", "invalid")); firstInvalid = firstInvalid || phoneEl; }
      }

      var industry = formEl.querySelector('[name="your_industry"]');
      if (industry && industry.required && !industry.value) { setFieldError("your_industry", friendlyFieldError("your_industry")); firstInvalid = firstInvalid || industry; }

      var state = formEl.querySelector('[name="state_dropdown"]');
      if (state && state.required && !state.value) { setFieldError("state_dropdown", friendlyFieldError("state_dropdown")); firstInvalid = firstInvalid || state; }

      if (firstInvalid) { try { firstInvalid.focus({ preventScroll: false }); } catch (_) {} return false; }
      return true;
    };

    var setSubmitting = function (on) {
      if (on) formEl.setAttribute("data-submitting", "true");
      else formEl.removeAttribute("data-submitting");
    };

    var handleSuccess = function () {
      var shell     = formEl.closest("[data-form-shell]");
      var successEl = shell ? shell.querySelector("[data-form-success]") : null;
      setStatus("", "");
      setSubmitting(false);
      formEl.classList.add("is-leaving");
      window.setTimeout(function () {
        formEl.style.display = "none";
        if (successEl) {
          successEl.removeAttribute("hidden");
          requestAnimationFrame(function () { requestAnimationFrame(function () { successEl.classList.add("is-visible"); }); });
        }
        var anchor = successEl || formEl.closest(".sub-v2-demo, .sub-v2-modal-demo__content") || statusEl;
        if (anchor && typeof anchor.scrollIntoView === "function") anchor.scrollIntoView({ behavior: "smooth", block: "center" });
      }, 200);
    };

    if (formEl.hasAttribute("data-mock-success")) {
      formEl.addEventListener("submit", function (e) { e.preventDefault(); if (!validate()) return; setSubmitting(true); setTimeout(handleSuccess, 600); });
      return;
    }

    new LandingForm({
      formElement: formEl,
      onSubmitStart: function (payload) {
        if (iti && typeof iti.getNumber === "function") { var e164 = iti.getNumber(); if (e164) payload.fields.phone = e164; }
        setSubmitting(true);
        setStatus("", "");
      },
      onSuccess: handleSuccess,
      onError: function (_message, fieldErrors) {
        var keys = Object.keys(fieldErrors || {});
        keys.forEach(function (n) {
          var raw  = typeof fieldErrors[n] === "string" ? fieldErrors[n] : "";
          var kind = /\binvalid\b|\bnot a valid\b/i.test(raw) ? "invalid" : "missing";
          setFieldError(n, friendlyFieldError(n, kind));
        });
        setStatus(keys.length ? "Please check the highlighted fields and try again." : "Something went wrong. Please try again in a moment.", "error");
        setSubmitting(false);
      },
    })._wrapValidation(validate).bind();
  }

  // ── Bootstrap ─────────────────────────────────────────────────────────────
  function initSubscriptionV2() {
    var root = document.querySelector(".subscription-v2, [data-subscription-v2], .sub-page--v2");
    if (!root) return;

    bindFaq(root);
    bindDemoModal(root);
    bindSmoothScroll(root);
    initToolsSlider(root, 0);
    initTestimonialsSlider(root, 0);
    initLandingForms(root);
    initScrollAnimations(root);

    var resizeTimer;
    window.addEventListener("resize", function () {
      clearTimeout(resizeTimer);
      resizeTimer = window.setTimeout(function () {
        var toolsSlider = root.querySelector("[data-sub-v2-tools-slider]");
        var testiSlider = root.querySelector("[data-sub-v2-testimonials-slider]");

        // If already slick-initialized just refresh — avoids unslick/reinit flicker.
        // Only full reinit when slick isn't running (e.g. first load edge case).
        if (toolsSlider && $(toolsSlider).hasClass("slick-initialized")) {
          $(toolsSlider).slick("refresh");
        } else {
          initToolsSlider(root, 0);
        }

        if (testiSlider && $(testiSlider).hasClass("slick-initialized")) {
          $(testiSlider).slick("refresh");
        } else {
          initTestimonialsSlider(root, 0);
        }

        if (window.ScrollTrigger) window.ScrollTrigger.refresh();
      }, 200);
    });
  }

  // ── Utilities ─────────────────────────────────────────────────────────────
  function toArray(collection) { return Array.prototype.slice.call(collection || []); }

  function isReducedMotionPreferred() {
    return !!(window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches);
  }

  // Lenis starts immediately — before DOM ready — so first scroll is already smooth
  initLenis();

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initSubscriptionV2);
  } else {
    initSubscriptionV2();
  }
})();
