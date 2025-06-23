/* Do not remove this code if use Jquery */
import "../lib/slick/slick.min.js";

("use strict");
$ = jQuery;
$(document).ready(function () {
  console.log("ready");
  $('.featured-on-slider .col-inner').slick({
    speed: 3000,
    autoplay: true,
    autoplaySpeed: 0,
    cssEase: 'linear',
    slidesToShow: 1,
    slidesToScroll: 1,
    variableWidth: true,
    infinite: true,
    arrows: false,
    pauseOnHover: false,
    pauseOnFocus: false,
  });
});
