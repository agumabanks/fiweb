/*---------------------------------------------
	Template name:  6valley Multipurpose Theme
	Version:        1.0
	Author:         6amtech
	Author url:     https://6amtech.com/

NOTE:
------
Please DO NOT EDIT THIS JS, you may need to use "custom.js" file for writing your custom js.
We may release future updates so it will overwrite this file. it's better and safer to use "custom.js".

[Table of Content]

    01: Main Menu
    02: Sticky Nav
    03: Mobile Menu
    04: Background Image
    05: Check Data
    06: Preloader 
    07: currentYear
    08: Collapse
    09: Swiper
    10: PreventDefault
    11: Back to top button
    12: Changing svg color
    13: Secure Payment Cards
    14: Viewport Detect
----------------------------------------------*/

(function ($) {
  "use strict";

  /*===================
  01: Main Menu
  =====================*/
  $('ul.nav li a[href="#"]').on('click', function (event) {
    event.preventDefault();
  });

  /* Parent li add class */
  $(".header .nav-wrapper, .aside .main-nav, .common-nav")
    .find(".sub-menu, .sub_menu")
    .parents("ul li")
    .addClass("has-sub-item");
  
  
  /* Submenu Opened */
  $(".aside .aside-body, .common-nav")
    .find(".has-sub-item > a, .has-sub-item > label")
    .on("click", function (event) {
      event.preventDefault();
      $(this).parent(".has-sub-item").toggleClass("sub-menu-opened");
      if ($(this).siblings("ul").hasClass("open")) {
        $(this).siblings("ul").removeClass("open").slideUp("200");
      } else {
        $(this).siblings("ul").addClass("open").slideDown("200");
      }
    });  
  
  /*========================
  02: Sticky Nav
  ==========================*/
  let headerMain = $('.header-main');

  $(window).on("scroll", function () {
      var scroll = $(window).scrollTop();

      if (scroll < 100) {
        $(".love-sticky").removeClass("sticky fadeInDown animated");
        headerMain.removeClass('sticky fadeInUp animated');
      }
      else {
          $(".love-sticky").addClass("sticky fadeInDown animated");
      }
  });

  /*========================
  03: Mobile Menu
  ==========================*/
  /* Toggle Menu */
  let aside = $('.aside');

  $('.menu-btn').on('click', () => toggleAside());
  $('.aside-close-btn, .aside-overlay').on('click', () => removeAside());
  
  $(window).on('resize', function () {
    if ($(window).width() > 1199) { 
      removeAside();
    }
  })

  function removeAside() {
    aside.removeClass('active');
  }
  function toggleAside() {
    aside.toggleClass('active');
  }

  /*========================
  04: Background Image
  ==========================*/
  var $bgImg = $("[data-bg-img]");
  $bgImg
    .css("background-image", function () {
      return 'url("' + $(this).data("bg-img") + '")';
    })
    .removeAttr("data-bg-img")
    .addClass("bg-img");

  /*==================================
  05: Check Data
  ====================================*/
  var checkData = function (data, value) {
    return typeof data === "undefined" ? value : data;
  };

  /*==================================
  06: Preloader 
  ====================================*/
  $(window).on("load", function () {
    $(".preloader").fadeOut(500);
  });

  /*==================================
  07: currentYear
  ====================================*/
  var currentYear = new Date().getFullYear();
  $(".currentYear").html(currentYear);

  /*==================================
  08: Collapse
  ====================================*/
  function collapse() {
    $(document.body).on('click', '[data-toggle="collapse"]', function (e) {
        e.preventDefault();
        var target = '#' + $(this).data('target');

        $(this).toggleClass('collapsed');
        $(target).slideToggle();
        
    })
  }
  collapse();

  /*==================================
  09: Swiper
  ====================================*/
  var $swiper = $(".swiper");
  var categorySlider = [];

  $swiper.each(function () {
    var $t = $(this);
    var slider = new Swiper($t[0], {
      slidesPerView: checkData($t.data("swiper-items"), 1),
      spaceBetween: checkData($t.data("swiper-margin"), 0),
      loop: checkData($t.data("swiper-loop"), true),
      autoHeight: checkData($t.data("swiper-auto-height"), false),
      breakpoints: checkData($t.data("swiper-breakpoints"), {}),
      centeredSlides: checkData($t.data("swiper-center"), false),
      speed: checkData($t.data("swiper-speed"), 1000),
      grabCursor: checkData($t.data("swiper-grab-cursor"), true),
      direction: checkData($t.data("swiper-direction"), "horizontal"),
      mousewheel: checkData($t.data("swiper-mouse-wheel"), false),
      autoPlay: checkData($t.data("swiper-auto-play"), false),
      effect: checkData($t.data("swiper-effect"), "slide"),
      // autoplay: {
      //   delay: checkData($t.data("swiper-delay"), 3000),
      //   disableOnInteraction: false,
      //   pauseOnMouseEnter: true
      // },
      navigation: {
        nextEl: checkData(
          $t.data("swiper-navigation-next"),
          ".swiper-button-next"
        ),
        prevEl: checkData(
          $t.data("swiper-navigation-prev"),
          ".swiper-button-prev"
        ),
      },
      pagination: {
        el: checkData($t.data("swiper-pagination-el"), ".swiper-pagination"),
        dynamicBullets: checkData(
          $t.data("swiper-pagination-dynamic-bullets"),
          false
        ),
        clickable: checkData($t.data("swiper-pagination-clickable"), true),
      },
    });
  });


  // Initialize Swiper
  var autoWheelSwiper = new Swiper('.autoWheelSwiper', {
    loop: true, 
    autoHeight: true, 
    autoPlay: true,
    // effect: "fade",
    mousewheel: true,
  });
  
  autoWheelSwiper.on('slideChange', function () {
    if (autoWheelSwiper.isEnd) {
      autoWheelSwiper.mousewheel.disable();
    }
  });


  /*==================================
  10: PreventDefault
  ====================================*/
  $('.preventDefault').on('click', function (e) {
    e.preventDefault();
  })

  /*============================================
  11: Back to top button
  ==============================================*/		
  var progressPath = document.querySelector('.progress-wrap path');
  var pathLength = progressPath.getTotalLength();
  progressPath.style.transition = progressPath.style.WebkitTransition = 'none';
  progressPath.style.strokeDasharray = pathLength + ' ' + pathLength;
  progressPath.style.strokeDashoffset = pathLength;
  progressPath.getBoundingClientRect();
  progressPath.style.transition = progressPath.style.WebkitTransition = 'stroke-dashoffset 10ms linear';		
  var updateProgress = function () {
    var scroll = $(window).scrollTop();
    var height = $(document).height() - $(window).height();
    var progress = pathLength - (scroll * pathLength / height);
    progressPath.style.strokeDashoffset = progress;
  }
  updateProgress();
  $(window).scroll(updateProgress);	
  var offset = 100;
  var duration = 50;
  jQuery(window).on('scroll', function() {
    if (jQuery(this).scrollTop() > offset) {
      jQuery('.progress-wrap').addClass('active-progress');
    } else {
      jQuery('.progress-wrap').removeClass('active-progress');
    }
  });				
  jQuery('.progress-wrap').on('click', function(event) {
    event.preventDefault();
    jQuery('html, body').animate({scrollTop: 0}, duration);
    return false;
  })

  /*==================================
  12: Changing svg color 
  ====================================*/
  $("img.svg").each(function () {
    var $img = jQuery(this);
    var imgID = $img.attr("id");
    var imgClass = $img.attr("class");
    var imgURL = $img.attr("src");

    jQuery.get(
      imgURL,
      function (data) {
        // Get the SVG tag, ignore the rest
        var $svg = jQuery(data).find("svg");

        // Add replaced image's ID to the new SVG
        if (typeof imgID !== "undefined") {
          $svg = $svg.attr("id", imgID);
        }
        // Add replaced image's classes to the new SVG
        if (typeof imgClass !== "undefined") {
          $svg = $svg.attr("class", imgClass + " replaced-svg");
        }

        // Remove any invalid XML tags as per http://validator.w3.org
        $svg = $svg.removeAttr("xmlns:a");

        // Check if the viewport is set, else we gonna set it if we can.
        if (
          !$svg.attr("viewBox") &&
          $svg.attr("height") &&
          $svg.attr("width")
        ) {
          $svg.attr(
            "viewBox",
            "0 0 " + $svg.attr("height") + " " + $svg.attr("width")
          );
        }

        // Replace image with new SVG
        $img.replaceWith($svg);
      },
      "xml"
    );
  });

  /*==================================
  13: Secure Payment Cards
  ====================================*/
  let securePaymentCards = $('.secure-payment-cards');

  securePaymentCards.find('.secure-payment-card').on('mouseenter', function (e) {
    $('.secure-payment-card').removeClass('active');
    $(this).addClass('active');
  });

  /*==================================
  14: Viewport Detect
  ====================================*/
  AOS.init({
    once: true,
  });

  /*==================================
    15: OTP Verification
    ====================================*/
    $(document).ready(function () {
      $(".otp-form button[type=submit]").attr("disabled", true);
      $(".otp-form *:input[type!=hidden]:first").focus();
      let otp_fields = $(".otp-form .otp-field"),
          otp_value_field = $(".otp-form .otp-value");
      otp_fields
          .on("input", function (e) {
              $(this).val(
                  $(this)
                      .val()
                      .replace(/[^0-9]/g, "")
              );
              let otp_value = "";
              otp_fields.each(function () {
                  let field_value = $(this).val();
                  if (field_value != "") otp_value += field_value;
              });
              otp_value_field.val(otp_value);
              // Check if all input fields are filled
              if (otp_value.length === 4) {
                  $(".otp-form button[type=submit]").attr("disabled", false);
              } else {
                  $(".otp-form button[type=submit]").attr("disabled", true);
              }
          })
          .on("keyup", function (e) {
              let key = e.keyCode || e.charCode;
              if (key == 8 || key == 46 || key == 37 || key == 40) {
                  // Backspace or Delete or Left Arrow or Down Arrow
                  $(this).prev().focus();
              } else if (key == 38 || key == 39 || $(this).val() != "") {
                  // Right Arrow or Top Arrow or Value not empty
                  $(this).next().focus();
              }
          })
          .on("paste", function (e) {
              let paste_data = e.originalEvent.clipboardData.getData("text");
              let paste_data_splitted = paste_data.split("");
              $.each(paste_data_splitted, function (index, value) {
                  otp_fields.eq(index).val(value);
              });
          });
  });

  /*==================================
  16: Verify Counter
  ====================================*/
  function countdown() {
      var counter = $(".verifyCounter");
      var seconds = counter.data("second");
      function tick() {
          var m = Math.floor(seconds / 60);
          var s = seconds % 60;
          seconds--;
          counter.html(m + ":" + (s < 10 ? "0" : "") + String(s));
          if (seconds > 0) {
              setTimeout(tick, 1000);
              $(".resend-otp-button").attr("disabled", true);
              $(".resend_otp_custom").slideDown();
          } else {
              $(".resend-otp-button").removeAttr("disabled");
              $(".verifyCounter").html("0:00");
              $(".resend_otp_custom").slideUp();
          }
      }
      tick();
  }
  countdown();

  /*==================================
  17: Init Tooltip
  ====================================*/
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
  const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))


})(jQuery);
