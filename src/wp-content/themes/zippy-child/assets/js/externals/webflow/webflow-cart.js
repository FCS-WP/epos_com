(function () {
  "use strict";

  var globalConfig = window.BlueTapWebflowCartConfig || {};
  var selectors = globalConfig.selectors || {};

  var config = {
    apiBase: typeof globalConfig.apiBase === "string" ? globalConfig.apiBase.trim() : "",
    cartUrl: typeof globalConfig.cartUrl === "string" ? globalConfig.cartUrl.trim() : "",
    selectors: {
      cartBadge:
        typeof selectors.cartBadge === "string" && selectors.cartBadge.trim()
          ? selectors.cartBadge
          : ".bluetap-cart-badge",
      cartIcon:
        typeof selectors.cartIcon === "string" && selectors.cartIcon.trim()
          ? selectors.cartIcon
          : '[data-bluetap-cart-icon="true"]',
    },
  };

  var cartCountEndpoint = config.apiBase ? config.apiBase.replace(/\/+$/, "") + "/cart/count" : "";
  var isRequestInFlight = false;
  var MIN_REFRESH_GAP_MS = 1000;
  var lastRefreshAt = 0;

  function safeQueryAll(selector) {
    try {
      return document.querySelectorAll(selector);
    } catch (error) {
      return [];
    }
  }

  function updateBadge(count) {
    var badges = safeQueryAll(config.selectors.cartBadge);
    if (!badges || !badges.length) {
      return;
    }

    var normalizedCount = Number.isFinite(count) ? Math.max(0, Math.floor(count)) : 0;

    badges.forEach(function (badge) {
      badge.textContent = String(normalizedCount);
      badge.setAttribute("data-cart-count", String(normalizedCount));
      badge.style.display = normalizedCount > 0 ? "" : "none";
    });
  }

  function bindCartIconClick() {
    if (!config.cartUrl) {
      return;
    }

    var icons = safeQueryAll(config.selectors.cartIcon);
    if (!icons || !icons.length) {
      return;
    }

    icons.forEach(function (icon) {
      if (icon.tagName === "A") {
        icon.setAttribute("href", config.cartUrl);
      }

      if (icon.dataset.bluetapCartBound === "true") {
        return;
      }

      icon.dataset.bluetapCartBound = "true";
      icon.addEventListener("click", function () {
        window.location.href = config.cartUrl;
      });
    });
  }

  function fetchCartCount(force) {
    var now = Date.now();

    if (!force && now - lastRefreshAt < MIN_REFRESH_GAP_MS) {
      return Promise.resolve(null);
    }

    if (isRequestInFlight) {
      return Promise.resolve(null);
    }

    if (!cartCountEndpoint) {
      return Promise.resolve(null);
    }

    isRequestInFlight = true;
    lastRefreshAt = now;

    var endpoint = cartCountEndpoint + (cartCountEndpoint.indexOf("?") === -1 ? "?" : "&") + "_ts=" + now;

    return fetch(endpoint, {
      method: "GET",
      credentials: "include",
      cache: "no-store",
      headers: {
        Accept: "application/json",
      },
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error("Cart count request failed with status " + response.status);
        }

        return response.json();
      })
      .then(function (data) {
        if (!data || data.success !== true) {
          return null;
        }

        var count = Number(data.cart_count);
        if (!Number.isFinite(count)) {
          return null;
        }

        updateBadge(count);
        return data;
      })
      .catch(function () {
        return null;
      })
      .finally(function () {
        isRequestInFlight = false;
      });
  }

  function init() {
    bindCartIconClick();
    fetchCartCount(true);

    window.addEventListener("pageshow", function () {
      fetchCartCount(true);
    });

    document.addEventListener("visibilitychange", function () {
      if (document.visibilityState === "visible") {
        fetchCartCount(true);
      }
    });
  }

  document.addEventListener("DOMContentLoaded", init);
})();