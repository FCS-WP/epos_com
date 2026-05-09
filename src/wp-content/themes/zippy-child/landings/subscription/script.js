// Subscription landing — runs only when this template is active.
// Bundle: dist/js/landings/subscription.min.js
//
// Responsibilities:
//   1. Reveal animations driven by [data-animate] attributes.
//   2. Mount intl-tel-input on the phone input (flag picker + E.164 format).
//      Restricted to MY/SG/VN, default MY. Utils.js loaded lazily on first focus.
//   3. Inline client-side validation (required, email format, phone validity).
//   4. Submit via the shared form bridge (browser → WP REST → HubSpot).
//
// intl-tel-input is loaded separately via wp_enqueue_script (window.intlTelInput);
// it is NOT bundled into this file. See landings/loader.php.

import { LandingForm } from "../_shared/form-bridge";

(function () {
  "use strict";

  // ── Reveal animations ───────────────────────────────────
  function initReveal() {
    const targets = document.querySelectorAll("[data-animate]");
    if (!targets.length) return;

    if (!("IntersectionObserver" in window)) {
      targets.forEach((t) => t.classList.add("is-visible"));
      return;
    }

    const io = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          if (!entry.isIntersecting) continue;
          entry.target.classList.add("is-visible");
          io.unobserve(entry.target);
        }
      },
      { threshold: 0.15, rootMargin: "0px 0px -10% 0px" }
    );
    targets.forEach((t) => io.observe(t));
  }

  // ── intl-tel-input ──────────────────────────────────────
  function initPhoneInput(formEl) {
    const phoneEl = formEl.querySelector('input[name="phone"]');
    if (!phoneEl) return null;
    if (typeof window.intlTelInput !== "function") {
      // Library not loaded; leave the input as a plain tel field.
      return null;
    }

    const cfg = window.LANDINGS_FORM_BRIDGE || {};
    const onlyCountries = (formEl.dataset.phoneCountries || "")
      .split(",")
      .map((c) => c.trim().toLowerCase())
      .filter(Boolean);
    const initialCountry = (formEl.dataset.phoneDefault || "my").toLowerCase();
    const utilsUrl = (cfg.intlTelInputUtilsUrl || "").trim();

    const iti = window.intlTelInput(phoneEl, {
      onlyCountries: onlyCountries.length ? onlyCountries : ["my", "sg", "vn"],
      initialCountry: initialCountry,
      separateDialCode: false,
      autoPlaceholder: "polite",
      // Lazy-load the utils bundle on first focus so it doesn't block initial render.
      utilsScript: utilsUrl || undefined,
    });

    return iti;
  }

  // ── Field error helpers ─────────────────────────────────
  function clearFieldError(formEl, name) {
    const node = formEl.querySelector(`[data-error-for="${name}"]`);
    if (node) node.textContent = "";
    const input = formEl.querySelector(`[name="${name}"]`);
    if (input) input.removeAttribute("aria-invalid");
  }
  function setFieldError(formEl, name, message) {
    const node = formEl.querySelector(`[data-error-for="${name}"]`);
    if (node) node.textContent = message;
    const input = formEl.querySelector(`[name="${name}"]`);
    if (input) input.setAttribute("aria-invalid", "true");
  }
  function clearAllErrors(formEl) {
    formEl
      .querySelectorAll("[data-error-for]")
      .forEach((n) => (n.textContent = ""));
    formEl
      .querySelectorAll("[aria-invalid]")
      .forEach((n) => n.removeAttribute("aria-invalid"));
  }

  // ── Validators ──────────────────────────────────────────
  // Email pattern is intentionally lax: server still validates with
  // sanitize_email + HubSpot's own check.
  const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  function validate(formEl, iti) {
    let firstInvalid = null;
    clearAllErrors(formEl);

    const name = formEl.querySelector('[name="lastname"]');
    if (name && !name.value.trim()) {
      setFieldError(formEl, "lastname", "Please enter your name.");
      firstInvalid = firstInvalid || name;
    }

    const email = formEl.querySelector('[name="email"]');
    if (email) {
      const v = email.value.trim();
      if (!v) {
        setFieldError(formEl, "email", "Please enter your email.");
        firstInvalid = firstInvalid || email;
      } else if (!EMAIL_RE.test(v)) {
        setFieldError(formEl, "email", "Please enter a valid email address.");
        firstInvalid = firstInvalid || email;
      }
    }

    const phoneInput = formEl.querySelector('[name="phone"]');
    if (phoneInput) {
      const raw = phoneInput.value.trim();
      if (!raw) {
        setFieldError(formEl, "phone", "Please enter your WhatsApp phone number.");
        firstInvalid = firstInvalid || phoneInput;
      } else if (iti && typeof iti.isValidNumber === "function") {
        // isValidNumber requires utils.js — returns true while utils still
        // loading is fine; the check is best-effort UX, server doesn't rely on it.
        if (iti.isValidNumber() === false) {
          setFieldError(formEl, "phone", "Please enter a valid phone number.");
          firstInvalid = firstInvalid || phoneInput;
        }
      }
    }

    const industry = formEl.querySelector('[name="your_industry"]');
    if (industry && industry.required && !industry.value) {
      setFieldError(formEl, "your_industry", "Please select your industry.");
      firstInvalid = firstInvalid || industry;
    }

    if (firstInvalid) {
      try { firstInvalid.focus({ preventScroll: false }); } catch (_) {}
      return false;
    }
    return true;
  }

  // ── Form ────────────────────────────────────────────────
  function initForm() {
    const formEl = document.querySelector('form[data-landing-form="hubspot"]');
    if (!formEl) return;

    const statusEl = formEl.querySelector(".landing__form-status");
    const setStatus = (msg, kind) => {
      if (!statusEl) return;
      statusEl.textContent = msg || "";
      statusEl.dataset.state = kind || "";
    };

    const iti = initPhoneInput(formEl);

    // Reusable success handler — used by both the real bridge response and
    // the mock-success path below.
    const handleSuccess = () => {
      formEl.style.display = "none";
      setStatus(
        formEl.dataset.successMessage ||
          "Thanks — we'll be in touch within one business day.",
        "success"
      );
      // Re-anchor the user on the confirmation block so it's visible even if
      // they scrolled down inside the form before submit.
      const section = formEl.closest(".landing__form") || statusEl;
      if (section && typeof section.scrollIntoView === "function") {
        section.scrollIntoView({ behavior: "smooth", block: "center" });
      }
    };

    // Clear error styling as the user fixes a field.
    formEl.addEventListener("input", (e) => {
      const t = e.target;
      if (!t || !t.name) return;
      clearFieldError(formEl, t.name);
    });
    formEl.addEventListener("change", (e) => {
      const t = e.target;
      if (!t || !t.name) return;
      clearFieldError(formEl, t.name);
    });

    // ── MOCK MODE (UI preview only) ─────────────────────
    // When the form has data-mock-success, skip the bridge entirely and just
    // show the success UI on submit. To disable: remove the attribute from
    // partials/form.php. Validation still runs.
    if (formEl.hasAttribute("data-mock-success")) {
      formEl.addEventListener("submit", (e) => {
        e.preventDefault();
        if (!validate(formEl, iti)) return;
        setStatus("Submitting…", "pending");
        // Slight delay so the UI shows the pending state before swapping.
        setTimeout(handleSuccess, 600);
      });
      return;
    }

    new LandingForm({
      formElement: formEl,
      onSubmitStart: (payload) => {
        // Replace the phone input value with the normalized E.164 number
        // so HubSpot stores it in a consistent format. Best-effort: if
        // utils.js hasn't loaded yet, getNumber() returns the raw string.
        if (iti && typeof iti.getNumber === "function") {
          const e164 = iti.getNumber();
          if (e164) payload.fields.phone = e164;
        }
        setStatus("Submitting…", "pending");
      },
      onSuccess: handleSuccess,
      onError: (message, fieldErrors) => {
        Object.keys(fieldErrors || {}).forEach((name) => {
          const msg =
            typeof fieldErrors[name] === "string"
              ? fieldErrors[name]
              : "Please check this field.";
          setFieldError(formEl, name, msg);
        });
        setStatus(message, "error");
      },
    })
      // Intercept submit to run our validation BEFORE the bridge fires the request.
      ._wrapValidation(() => validate(formEl, iti))
      .bind();
  }

  // ── Init ────────────────────────────────────────────────
  function init() {
    initReveal();
    initForm();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
