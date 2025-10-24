$(document).ready(function () {
  function scrollToTop(target) {
    $("html, body").animate(
      {
        scrollTop: target.offset().top - 80,
      },
      500
    );
  }

  $("button[data-file]").click(function () {
    const file = $(this).data("file");
    const target = $($(this).data("target"));
    const hideContainer = $($(this).data("hide"));
    const arrow = $(this).find("svg");

    hideContainer.removeClass("open").css({
      "max-height": "0px",
      overflow: "hidden",
      transition: "all 0.5s ease-in-out",
    });
    hideContainer.find("svg").removeClass("rotate-180");

    if (target.hasClass("open")) {
      target.removeClass("open").css({
        "max-height": "0px",
        overflow: "hidden",
        transition: "all 0.5s ease-in-out",
      });
      arrow.removeClass("rotate-180");
    } else {
      target.addClass("open");
      arrow.addClass("rotate-180");

      if ($.trim(target.html()) === "") {
        $.get(file, function (html) {
          target.html(html);
          target.css({
            "max-height": "none",
            overflow: "visible",
            transition: "none",
          });
          scrollToTop(target);
        }).fail(function () {
          target.html("<p class='text-red-500'>Error loading content</p>");
          target.css({
            "max-height": "none",
            overflow: "visible",
            transition: "none",
          });
          scrollToTop(target);
        });
      } else {
        target.css({
          "max-height": "none",
          overflow: "visible",
          transition: "none",
        });
        scrollToTop(target);
      }
    }
  });

  // Pagination
  $(document).on("click", ".pagination-link", function (e) {
    e.preventDefault();
    const page = $(this).data("page");
    const file = $(this).data("file");
    const target = $($(this).data("target"));

    if (!file || !target.length) return;

    $.get(file + "?page=" + page, function (html) {
      target.html(html);
      target.addClass("open").css({
        "max-height": "none",
        overflow: "visible",
        transition: "none",
      });
      scrollToTop(target);
    }).fail(function () {
      target.html("<p class='text-red-500'>Error loading leaderboard</p>");
    });
  });
});
