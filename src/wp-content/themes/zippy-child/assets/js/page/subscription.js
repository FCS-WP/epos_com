import "slick-carousel";

(function () {
  "use strict";

  function initSubscriptionV2() {
    var root = document.querySelector(".sub-page--v2");
    if (!root) return;

    var faqRootV2 = root.querySelector("[data-sub-v2-faq]");
    if (faqRootV2 && !faqRootV2.hasAttribute("data-v2-faq-bound")) {
      faqRootV2.setAttribute("data-v2-faq-bound", "true");

      var faqItemsV2 = faqRootV2.querySelectorAll(".sub-v2-faq__item");

      faqItemsV2.forEach(function (item) {
        var trigger = item.querySelector(".sub-v2-faq__trigger");
        var body = item.querySelector(".sub-v2-faq__body");

        if (!trigger || !body) return;

        trigger.addEventListener("click", function () {
          var isOpen = trigger.getAttribute("aria-expanded") === "true";

          faqItemsV2.forEach(function (entry) {
            var entryTrigger = entry.querySelector(".sub-v2-faq__trigger");
            var entryBody = entry.querySelector(".sub-v2-faq__body");

            if (entryTrigger)
              entryTrigger.setAttribute("aria-expanded", "false");
            if (entryBody) entryBody.hidden = true;
          });

          if (!isOpen) {
            trigger.setAttribute("aria-expanded", "true");
            body.hidden = false;
          }
        });
      });
    }

    initDemoModal(root);

    initToolsSlider(root, 0);
    initTestimonialsSlider(root, 0);

    if (!root.hasAttribute("data-v2-tools-resize-bound")) {
      root.setAttribute("data-v2-tools-resize-bound", "true");
      window.addEventListener("resize", function () {
        syncToolsSlider(root);
      });
    }
  }

  function initDemoModal(root) {
    var modal = document.getElementById("sub-v2-demo-modal");
    var triggers = root.querySelectorAll("[data-sub-v2-demo-trigger]");
    var closers = modal
      ? modal.querySelectorAll("[data-sub-v2-modal-close]")
      : [];

    if (!modal || !triggers.length) return;

    triggers.forEach(function (trigger) {
      trigger.addEventListener("click", function (e) {
        e.preventDefault();
        openModal();
      });
    });

    closers.forEach(function (closer) {
      closer.addEventListener("click", function () {
        closeModal();
      });
    });

    function openModal() {
      modal.setAttribute("aria-hidden", "false");
      document.body.classList.add("modal-open");
    }

    function closeModal() {
      modal.setAttribute("aria-hidden", "true");
      document.body.classList.remove("modal-open");
    }

    // Close on ESC
    window.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && modal.getAttribute("aria-hidden") === "false") {
        closeModal();
      }
    });
  }

  function initToolsSlider(root, attempt) {
    var toolsSlider = root.querySelector("[data-sub-v2-tools-slider]");
    if (!toolsSlider) return;

    if (!(window.jQuery && window.jQuery.fn && window.jQuery.fn.slick)) {
      if (attempt < 10) {
        window.setTimeout(function () {
          initToolsSlider(root, attempt + 1);
        }, 250);
      }
      return;
    }

    var $ = window.jQuery;
    var $toolsSlider = $(toolsSlider);

    if ($toolsSlider.hasClass("slick-initialized")) {
      $toolsSlider.slick("unslick");
    }

    removeToolsLoopClones(toolsSlider);

    var $slides = $toolsSlider.children().filter("article");
    var slideCount = $slides.length;
    var isMobile = window.innerWidth < 768;

    // Force re-check on desktop
    if (!isMobile && slideCount <= 3) {
      $toolsSlider.css("display", "grid");
      return;
    }

    $toolsSlider.css("display", "block");

    $toolsSlider.slick({
      slidesToShow: 3,
      slidesToScroll: 1,
      infinite: slideCount > 3,
      arrows: true,
      dots: false,
      speed: 600,
      cssEase: "cubic-bezier(0.77, 0, 0.175, 1)",
      adaptiveHeight: false,
      prevArrow: root.querySelector(".sub-v2-tools__nav-btn--prev"),
      nextArrow: root.querySelector(".sub-v2-tools__nav-btn--next"),
      responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: Math.min(slideCount, 2),
            slidesToScroll: 1,
            infinite: slideCount > 2,
          },
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
            centerMode: false,
            infinite: slideCount > 1,
          },
        },
      ],
    });
  }

  function initTestimonialsSlider(root, attempt) {
    var testimonialsSlider = root.querySelector(
      "[data-sub-v2-testimonials-slider]",
    );
    if (!testimonialsSlider) return;

    if (!(window.jQuery && window.jQuery.fn && window.jQuery.fn.slick)) {
      if (attempt < 10) {
        window.setTimeout(function () {
          initTestimonialsSlider(root, attempt + 1);
        }, 250);
      }
      return;
    }

    var $ = window.jQuery;
    var $testimonialsSlider = $(testimonialsSlider);

    if ($testimonialsSlider.hasClass("slick-initialized")) {
      $testimonialsSlider.slick("unslick");
    }

    removeToolsLoopClones(testimonialsSlider);

    var $slides = $testimonialsSlider.children().filter("article");
    var slideCount = $slides.length;
    var isMobile = window.innerWidth < 768;

    if (!isMobile && slideCount <= 3) {
      $testimonialsSlider.css("display", "grid");
      return;
    }

    $testimonialsSlider.css("display", "block");

    $testimonialsSlider.slick({
      slidesToShow: 3,
      slidesToScroll: 1,
      infinite: slideCount > 3,
      arrows: true,
      dots: false,
      speed: 600,
      cssEase: "cubic-bezier(0.77, 0, 0.175, 1)",
      adaptiveHeight: false,
      prevArrow: root.querySelector(".sub-v2-testimonials__nav-btn--prev"),
      nextArrow: root.querySelector(".sub-v2-testimonials__nav-btn--next"),
      responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: Math.min(slideCount, 2),
            slidesToScroll: 1,
            infinite: slideCount > 2,
          },
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
            centerMode: false,
            infinite: slideCount > 1,
          },
        },
      ],
    });
  }

  function removeToolsLoopClones(toolsSlider) {
    var loopClones = toolsSlider.querySelectorAll("[data-v2-loop-clone]");

    loopClones.forEach(function (clone) {
      clone.remove();
    });

    toolsSlider.removeAttribute("data-v2-loop-prepared");
  }

  function syncToolsSlider(root) {
    initToolsSlider(root, 0);
    initTestimonialsSlider(root, 0);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initSubscriptionV2);
  } else {
    initSubscriptionV2();
  }

  window.addEventListener("load", initSubscriptionV2);
})();