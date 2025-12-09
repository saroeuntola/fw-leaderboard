<link rel="stylesheet" href="./css/loader.css">
<script src="./js/jquery-3.7.1.min.js" defer></script>

<body class="bg-white dark:bg-gray-900 text-white">
    <!-- Loader -->
    <div id="pageLoader" class="fixed inset-0 z-[9999] bg-[#252525] flex items-center justify-center transition-opacity  duration-1000" aria-live="polite">
        <div class="loader"></div>
    </div>

    <!-- Loader Script -->
    <script>
        $(window).on("load", function() {
            const $loader = $("#pageLoader");
            $loader.removeClass("opacity-0").css("opacity", "1");
            $loader.addClass("opacity-0");
            setTimeout(function() {
                $loader.css("display", "none").attr("aria-hidden", "true");
            }, 1000);
        });
    </script>

</body>