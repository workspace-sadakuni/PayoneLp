
//変数
const hamburger = $(".js-hamburger");
const nav = $(".js-nav");
const navitem = $(".js-nav-item");
const toTop = $('.js-to-top');
const question = $(".js-question");
const arrow = $(".js-arrow");
const floating = $(".js-floating");
const hidearea = $(".js-hidearea");

/*ハンバーガーメニューとナビ*/
$(function () {
  hamburger.on("click", function () {
    hamburger.toggleClass("active");
    nav.toggleClass("active");
    $("body").toggleClass("noscroll");
  });
});

$(function () {
  navitem.on("click", function () {
    hamburger.removeClass("active");
    nav.removeClass("active");
    $("body").removeClass("noscroll");
  });
});


//TO TOPボタン　mvからスクロールするとふわっと出てくる
$(function () {
  var pagetop = toTop;
  pagetop.hide();
  $(window).scroll(function () {
    if ($(this).scrollTop() > 300) {
      pagetop.fadeIn();
    } else {
      pagetop.fadeOut();
    }
  });
  pagetop.click(function () {
    $('body, html').animate({ scrollTop: 0 }, 500);
    return false;
  });
});

/*FAQ　アコーディオン*/
$(function () {
  question.on("click", function () {
    $(this).next().slideToggle();
    $(this).children().toggleClass("down");
  });
});

/*追従ボタン（ご購入はこちらから）フッターで消える*/
$(function () {
  $(window).scroll(function () {
    hidearea.each(function () {
      var position = $(this).offset().top;
      var scroll = $(window).scrollTop();
      var windowHeight = $(window).height();
      if (scroll > position - windowHeight + 140) {
        //TOTOPの文字がかぶったくらいで色を変えたい
        floating.addClass('hide');
      } else {
        floating.removeClass('hide');
      }
    });
  });
});

