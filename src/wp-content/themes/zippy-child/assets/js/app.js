import "../lib/slick/slick.min.js";
// import "../js/widgetWhatsapp.js";
import "../js/video_hub.js";
import "../js/careers-page.js";
import "../js/checkout/checkout.js";
import "../js/checkout/phone-validation.js";
import "../js/scrollToBullet.js";
import "./page/epos-bluetap-onboarding.js";
import "./promo_popup/promo-popup.js";

("use strict");
$ = jQuery;
$(document).ready(function () {
  const $slider = $(".featured-on-slider");

  if ($(window).width() < 920) {
    $slider.slick({
      speed: 3000,
      autoplay: false,
      cssEase: "linear",
      slidesToShow: 2,
      slidesToScroll: 1,
      variableWidth: true,
      infinite: true,
      arrows: false,
      pauseOnHover: false,
      pauseOnFocus: false,
    });

    const $track = $slider.find(".slick-track");

    $track.find("style").remove();

    const $originalSlides = $track
      .children(".slick-slide:not(.slick-cloned)")
      .clone(true);
    $track.append($originalSlides);

    let velocity = 1.2;
    let offset = 0;

    function animate() {
      offset += velocity;
      const trackWidth = $track.width() / 2;

      if (Math.abs(offset) >= trackWidth) {
        offset = 0;
      }

      $track.css("transform", `translate3d(${-offset}px, 0, 0)`);
      requestAnimationFrame(animate);
    }

    animate();

    $slider.on("mousemove", function (e) {
      const sliderOffset = $slider.offset().left;
      const sliderWidth = $slider.outerWidth();
      const center = sliderOffset + sliderWidth / 2;
      const mouseX = e.pageX;
      const dist = mouseX - center;

      const maxSpeed = 1;

      velocity = (dist / (sliderWidth / 2)) * maxSpeed;

      if (Math.abs(velocity) < 0.2) {
        velocity = 0;
      }
    });

    $slider.on("mouseleave", function () {
      velocity = 1.2;
    });
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const mapping = {
    "button-tab-f&b": { tab: "tab-f&b", panel: "tab_f&b" },
    "button-tab-retail": { tab: "tab-retail", panel: "tab_retail" },
    "button-tab-services": { tab: "tab-services", panel: "tab_services" },
    "button-tab-all-businesses": {
      tab: "tab-all-businesses",
      panel: "tab_all-businesses",
    },
  };

  Object.entries(mapping).forEach(([buttonClass, { tab, panel }]) => {
    const buttons = document.getElementsByClassName(buttonClass);
    Array.from(buttons).forEach((button) => {
      button.addEventListener("click", function () {
        document
          .querySelectorAll(".tabbed-content .tab")
          .forEach((el) => el.classList.remove("active"));
        document
          .querySelectorAll(".tabbed-content .panel")
          .forEach((el) => el.classList.remove("active"));
        document
          .querySelectorAll(".stack a.button")
          .forEach((el) => el.classList.remove("active"));

        button.classList.add("active");

        const tabEl = document.getElementById(tab);
        if (tabEl) {
          tabEl.classList.add("active");
          const a = tabEl.querySelector("a");
          if (a) {
            a.setAttribute("aria-selected", "true");
          }
        }
        const panelEl = document.getElementById(panel);
        if (panelEl) {
          panelEl.classList.add("active");
        }
      });
    });
  });
});


