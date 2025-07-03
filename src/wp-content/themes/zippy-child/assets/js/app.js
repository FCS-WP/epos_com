import "../lib/slick/slick.min.js";

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
