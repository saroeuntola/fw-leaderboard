<style>
    /* HTML: <div class="loader"></div> */
    .loader {
        width: 40px;
        height: 40px;
        color: #f03355;
        background:
            conic-gradient(from -45deg at top 20px left 50%, #0000, currentColor 1deg 90deg, #0000 91deg),
            conic-gradient(from 45deg at right 20px top 50%, #0000, currentColor 1deg 90deg, #0000 91deg),
            conic-gradient(from 135deg at bottom 20px left 50%, #0000, currentColor 1deg 90deg, #0000 91deg),
            conic-gradient(from -135deg at left 20px top 50%, #0000, currentColor 1deg 90deg, #0000 91deg);
        animation: l4 1.5s infinite cubic-bezier(0.3, 1, 0, 1);
    }

    @keyframes l4 {
        50% {
            width: 60px;
            height: 60px;
            transform: rotate(180deg)
        }

        100% {
            transform: rotate(360deg)
        }
    }
</style>

<body class="bg-gray-900 text-white">
    <!-- Loader -->
    <div id="pageLoader" class="fixed inset-0 z-[9999] bg-gray-900 flex items-center justify-center transition-opacity  duration-1000" aria-live="polite">

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