// Landings — client-side form submission bridge.
//
// Each landing controls its own <form> markup. This library handles the
// submission behavior: collect fields, POST JSON to the WP REST endpoint
// with the WP nonce, call onSuccess / onError so the landing's script can
// drive its UI.
//
// Wire-up from landings/{slug}/script.js:
//
//   import { LandingForm } from "../_shared/form-bridge";
//
//   new LandingForm({
//     formElement: document.querySelector('form[data-landing-form="hubspot"]'),
//     onSubmitStart: (payload) => { ... },  // mutate payload before send
//     onSuccess:     (data)    => { ... },  // 2xx response
//     onError:       (msg, fieldErrors) => { ... },
//   })._wrapValidation(() => myValidator())  // optional preflight
//     .bind();
//
// window.LANDINGS_FORM_BRIDGE = { endpoint, nonce, slug, pageUri, pageName }
// is injected by landing_footer() in landings/loader.php — do not set manually.

const DEFAULT_HONEYPOT_FIELD = "website_url";

export class LandingForm {
  /**
   * @param {Object}   opts
   * @param {HTMLFormElement} opts.formElement              The <form> to bind.
   * @param {string}   [opts.honeypotField]                 Name of a hidden honeypot input. Bots fill it; real users don't see it.
   * @param {function} [opts.onSubmitStart]                 Called as the request leaves. Receives the form payload.
   * @param {function} [opts.onSuccess]                     Called on 2xx. Receives the JSON response body.
   * @param {function} [opts.onError]                       Called on failure. Receives (errorMessage, fieldErrors).
   */
  constructor(opts) {
    if (!opts || !opts.formElement) {
      throw new Error("LandingForm: formElement is required");
    }
    this.form = opts.formElement;
    this.honeypotField = opts.honeypotField || DEFAULT_HONEYPOT_FIELD;
    this.onSubmitStart = typeof opts.onSubmitStart === "function" ? opts.onSubmitStart : null;
    this.onSuccess     = typeof opts.onSuccess     === "function" ? opts.onSuccess     : null;
    this.onError       = typeof opts.onError       === "function" ? opts.onError       : null;
    this.inFlight      = false;
    this.preflight     = null;
  }

  /**
   * Register a synchronous pre-flight check that runs before each submit.
   * Return false from the function to block the submission (the function
   * is responsible for surfacing its own error UI).
   *
   * @param {() => boolean} fn
   * @returns {LandingForm}
   */
  _wrapValidation(fn) {
    if (typeof fn === "function") this.preflight = fn;
    return this;
  }

  bind() {
    this.form.addEventListener("submit", this.handleSubmit.bind(this));
    return this;
  }

  /**
   * @private
   * @param {SubmitEvent} event
   */
  async handleSubmit(event) {
    event.preventDefault();
    if (this.inFlight) return;

    // Run the preflight validator if one was registered. It owns its own
    // error UI; we just gate the request on its return value.
    if (this.preflight && this.preflight() === false) return;

    const config = window.LANDINGS_FORM_BRIDGE;
    if (!config || !config.endpoint || !config.nonce || !config.slug) {
      this.fireError("Form is not configured.", {});
      return;
    }

    const fields = this.collectFields();
    const honeypot = fields[this.honeypotField] || "";
    delete fields[this.honeypotField];

    const payload = {
      landing_slug: config.slug,
      page_uri:     config.pageUri || (typeof window !== "undefined" ? window.location.href : ""),
      page_name:    config.pageName || (typeof document !== "undefined" ? document.title : ""),
      fields:       fields,
      honeypot:     honeypot,
    };

    this.inFlight = true;
    this.setSubmittingState(true);
    if (this.onSubmitStart) this.onSubmitStart(payload);

    try {
      const response = await fetch(config.endpoint, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          "Accept":       "application/json",
          "X-WP-Nonce":   config.nonce,
        },
        body: JSON.stringify(payload),
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok || data.success === false) {
        const message = data.error || "Submission failed. Please try again.";
        const fieldErrors = data.errors || {};
        this.fireError(message, fieldErrors);
        return;
      }

      if (this.onSuccess) this.onSuccess(data);
      this.form.reset();
    } catch (err) {
      this.fireError("Network error. Please try again.", {});
    } finally {
      this.inFlight = false;
      this.setSubmittingState(false);
    }
  }

  /**
   * @private
   * Collects all named fields from the form into a flat object.
   * Multi-value inputs (checkbox groups) become arrays.
   */
  collectFields() {
    const out = {};
    const fd = new FormData(this.form);
    for (const [key, value] of fd.entries()) {
      if (out[key] === undefined) {
        out[key] = value;
      } else if (Array.isArray(out[key])) {
        out[key].push(value);
      } else {
        out[key] = [out[key], value];
      }
    }
    return out;
  }

  /**
   * @private
   * Toggle the form's submit button between idle and submitting states.
   * Adds aria-busy + disables the button while in flight.
   */
  setSubmittingState(submitting) {
    const button = this.form.querySelector('button[type="submit"], input[type="submit"]');
    if (!button) return;
    button.disabled = !!submitting;
    if (submitting) {
      button.setAttribute("aria-busy", "true");
      button.dataset.bridgeOriginalLabel = button.dataset.bridgeOriginalLabel || button.textContent;
    } else {
      button.removeAttribute("aria-busy");
    }
  }

  /**
   * @private
   */
  fireError(message, fieldErrors) {
    if (this.onError) this.onError(message, fieldErrors || {});
  }
}
