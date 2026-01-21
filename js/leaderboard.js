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

  // Live Search for Tiger & Lion Leaderboards
  $(document).on("keyup", ".leaderboard-search", function () {
    const query = $(this).val().toLowerCase();
    const target = $($(this).data("target"));

    if (!target.length) return;

    let visibleCount = 0;

    target.find("tr").each(function () {
      const rowText = $(this).text().toLowerCase();
      const match = rowText.includes(query);
      $(this).toggle(match);
      if (match) visibleCount++;
    });

    // Show "No result" message
    if (visibleCount === 0) {
      if (!target.find(".no-result").length) {
        target.append(`
          <tr class="no-result">
            <td colspan="4" class="text-center text-red-500 p-4">
              কোনো ফলাফল পাওয়া যায়নি
            </td>
          </tr>
        `);
      }
    } else {
      target.find(".no-result").remove();
    }
  });

  // Show / hide clear icon based on input value

$(document).on("input", ".search-input", function () {
  const hasValue = $(this).val().length > 0;
  const clearBtn = $(this).siblings(".clear-btn");

  if (hasValue) {
    clearBtn.addClass("active");
  } else {
    clearBtn.removeClass("active");
  }
});

$(document).on("input", ".search-input", function () {
  const hasValue = $(this).val().length > 0;
  const clearBtn = $(this).siblings(".clear-btn");

  // Toggle clear button
  clearBtn.toggleClass("active", hasValue);

  // Live search filter
  const query = $(this).val().toLowerCase();
  const target = $($(this).data("target"));

  if (!target.length) return;

  target.find("tr").each(function () {
    const text = $(this).text().toLowerCase();
    $(this).toggle(text.includes(query));
  });
});

// Clear button click
$(document).on("click", ".clear-btn", function () {
  const input = $(this).siblings(".search-input");

  // Clear input
  input.val("");

  // Trigger search again (this shows all rows)
  input.trigger("input").focus();
});

