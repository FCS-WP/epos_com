// Homepage GROW promo popup (/my/home)
// Auto-shows after 3 s on every page load + exit-intent on desktop.
(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    var modal = document.querySelector("[data-sub-v2-promo-modal]");
    if (!modal) return;

    var dialog  = modal.querySelector(".sub-v2-modal-promo__dialog");
    var closers = modal.querySelectorAll("[data-sub-v2-promo-modal-close]");
    if (!dialog) return;

    var opened = false;

    function openPromo() {
      if (opened) return;
      opened = true;

      modal.hidden = false;
      modal.setAttribute("aria-hidden", "false");
      document.body.classList.add("modal-open");
      requestAnimationFrame(function () {
        modal.classList.add("is-open");
        // No focus hijack — intentionally not calling dialog.focus()
      });
    }

    function closePromo() {
      modal.classList.remove("is-open");
      modal.setAttribute("aria-hidden", "true");
      document.body.classList.remove("modal-open");
      window.setTimeout(function () {
        if (modal.getAttribute("aria-hidden") === "true") {
          modal.hidden = true;
        }
      }, 220);
    }

    // Close via backdrop / × button
    closers.forEach(function (c) {
      c.addEventListener("click", function (e) { e.preventDefault(); closePromo(); });
    });

    // Close via Escape key
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && modal.getAttribute("aria-hidden") === "false") closePromo();
    });

    // "Learn More" — let the link navigate normally; close the modal
    var learnMoreBtns = modal.querySelectorAll("[data-sub-v2-promo-learn-more]");
    learnMoreBtns.forEach(function (btn) {
      btn.addEventListener("click", function () { closePromo(); });
    });

    // Trigger 1: timed (3 s, both desktop & mobile)
    var timeoutId = window.setTimeout(openPromo, 2500);

    // Trigger 2: exit-intent (desktop only) — mouse leaves through the top edge
    // but only after the user has been on the page for at least 3 s.
    var isMobile    = window.matchMedia("(max-width: 767px)").matches;
    var pageLoadTime = Date.now();

    if (!isMobile) {
      document.addEventListener("mouseleave", function (e) {
        if (e.clientY > 10) return;                     // not a top-exit
        if (Date.now() - pageLoadTime < 2500) return;  // too soon
        clearTimeout(timeoutId);
        openPromo();
      });
    }
  });
})();
