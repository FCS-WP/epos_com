import 'slick-carousel';

(function () {
  'use strict';

  function initSubscriptionV2() {
    var root = document.querySelector('.sub-page--v2');
    if (!root) return;

    var faqRootV2 = root.querySelector('[data-sub-v2-faq]');
    if (faqRootV2 && !faqRootV2.hasAttribute('data-v2-faq-bound')) {
      faqRootV2.setAttribute('data-v2-faq-bound', 'true');

      var faqItemsV2 = faqRootV2.querySelectorAll('.sub-v2-faq__item');

      faqItemsV2.forEach(function (item) {
        var trigger = item.querySelector('.sub-v2-faq__trigger');
        var body = item.querySelector('.sub-v2-faq__body');

        if (!trigger || !body) return;

        trigger.addEventListener('click', function () {
          var isOpen = trigger.getAttribute('aria-expanded') === 'true';

          faqItemsV2.forEach(function (entry) {
            var entryTrigger = entry.querySelector('.sub-v2-faq__trigger');
            var entryBody = entry.querySelector('.sub-v2-faq__body');

            if (entryTrigger) entryTrigger.setAttribute('aria-expanded', 'false');
            if (entryBody) entryBody.hidden = true;
          });

          if (!isOpen) {
            trigger.setAttribute('aria-expanded', 'true');
            body.hidden = false;
          }
        });
      });
    }

    initToolsSlider(root, 0);

    if (!root.hasAttribute('data-v2-tools-resize-bound')) {
      root.setAttribute('data-v2-tools-resize-bound', 'true');
      window.addEventListener('resize', function () {
        syncToolsSlider(root);
      });
    }
  }

  function initToolsSlider(root, attempt) {
    var toolsSlider = root.querySelector('[data-sub-v2-tools-slider]');
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

    removeToolsLoopClones(toolsSlider);

    if (window.innerWidth >= 768) {
      if ($toolsSlider.hasClass('slick-initialized')) {
        $toolsSlider.slick('unslick');
      }
      return;
    }

    if ($toolsSlider.hasClass('slick-initialized')) {
      return;
    }

    $toolsSlider.slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      initialSlide: 0,
      infinite: true,
      arrows: true,
      dots: false,
      speed: 480,
      adaptiveHeight: false,
      centerMode: true,
      centerPadding: '24px',
      prevArrow: root.querySelector('.sub-v2-tools__nav-btn--prev'),
      nextArrow: root.querySelector('.sub-v2-tools__nav-btn--next'),
      responsive: [
        {
          breakpoint: 480,
          settings: {
            centerPadding: '16px'
          }
        }
      ]
    });
  }

  function removeToolsLoopClones(toolsSlider) {
    var loopClones = toolsSlider.querySelectorAll('[data-v2-loop-clone]');

    loopClones.forEach(function (clone) {
      clone.remove();
    });

    toolsSlider.removeAttribute('data-v2-loop-prepared');
  }

  function syncToolsSlider(root) {
    initToolsSlider(root, 0);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSubscriptionV2);
  } else {
    initSubscriptionV2();
  }

  window.addEventListener('load', initSubscriptionV2);
})();