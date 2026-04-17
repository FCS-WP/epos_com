/**
 * EPOS Tracking SDK
 *
 * Modules:
 *   - UTM: Forward URL params to all internal links
 *   - PostHog: Track clicks via [data-event] attributes
 */
import "./webflow-cart.js";
const EPOS = (() => {
  const CONFIG = {
    utm: {
      keys: [
        "utm_source",
        "utm_medium",
        "utm_campaign",
        "utm_term",
        "utm_content",
      ],
    },
    posthog: {
      fallbackTimeout: 300,
      eventAttr: "data-event",
    },
  };

  // ─── UTM ──────────────────────────────────────────────────────

  function initUTM() {
    const params = new URLSearchParams(window.location.search);
    const utmData = {};

    CONFIG.utm.keys.forEach((key) => {
      const value = params.get(key);
      if (value) utmData[key] = value;
    });

    if (Object.keys(utmData).length === 0) return;

    decorateLinks(utmData);

    new MutationObserver(() => decorateLinks(utmData)).observe(document.body, {
      childList: true,
      subtree: true,
    });
  }

  function decorateLinks(utmData) {
    document.querySelectorAll("a[href]").forEach((link) => {
      try {
        const url = new URL(link.href);
        if (url.hostname !== window.location.hostname) return;
        if (link.dataset.utmDecorated) return;

        Object.entries(utmData).forEach(([k, v]) => {
          url.searchParams.set(k, v);
        });

        link.href = url.toString();
        link.dataset.utmDecorated = "true";
      } catch (e) {}
    });
  }

  // ─── PostHog ──────────────────────────────────────────────────

  function initPostHog() {
    document.addEventListener("click", function (e) {
      const el = e.target.closest(`[${CONFIG.posthog.eventAttr}]`);
      if (!el) return;

      const eventName = el.getAttribute(CONFIG.posthog.eventAttr);
      const url = el.href || el.getAttribute("data-href");
      const isLink = el.tagName === "A" && url;

      if (isLink) e.preventDefault();

      const properties = {
        text: el.innerText?.trim() || "",
        url: url || null,
        page: window.location.pathname,
        ...getDataProps(el),
      };

      if (typeof posthog === "undefined" || !posthog.capture) {
        if (isLink) window.location.href = url;
        return;
      }

      let redirected = false;

      const fallback = setTimeout(() => {
        if (isLink && !redirected) {
          redirected = true;
          window.location.href = url;
        }
      }, CONFIG.posthog.fallbackTimeout);

      posthog.capture(eventName, properties, {
        send_instantly: true,
        _onCapture: function () {
          clearTimeout(fallback);
          if (isLink && !redirected) {
            redirected = true;
            window.location.href = url;
          }
        },
      });
    });
  }

  function getDataProps(el) {
    const props = {};
    Array.from(el.attributes).forEach((attr) => {
      if (attr.name.startsWith("data-prop-")) {
        props[attr.name.replace("data-prop-", "")] = attr.value;
      }
    });
    return props;
  }

  // ─── Init ─────────────────────────────────────────────────────

  function init() {
    initUTM();
    initPostHog();
  }

  return { init, CONFIG };
})();

document.addEventListener("DOMContentLoaded", EPOS.init);
