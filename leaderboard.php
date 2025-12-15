<?php
include "./admin/lib/db.php";
?>
<!DOCTYPE html>
<html lang="en-BD">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Leaderboard - FancyWin</title>
    <meta name="description" content="View the latest FancyWin leaderboard in Bangladesh. Check player rankings, scores, and real-time updates." />
    <meta name="keywords" content="FancyWin, leaderboard Bangladesh, gaming leaderboard BD, player ranking Bangladesh, real-time scores BD, top players Bangladesh" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://fancybet-leaderboard.com/v2/leaderboard" />
    <meta property="og:title" content="FancyWin Leaderboard - Bangladesh" />
    <meta property="og:description" content="Check the latest leaderboard and rankings of FancyWin players in Bangladesh with real-time updates." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://fancybet-leaderboard.com/v2/leaderboard" />
    <meta property="og:image" content="https://fancybet-leaderboard.com/v2/images/og-image.png" />
    <meta property="og:locale" content="en_BD" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="FancyWin Leaderboard - Bangladesh" />
    <meta name="twitter:description" content="Real-time FancyWin leaderboard for players in Bangladesh." />
    <meta name="twitter:image" content="https://fancybet-leaderboard.com/v2/images/og-image.png" />
    <meta name="geo.region" content="BD" />
    <meta name="geo.placename" content="Bangladesh" />
    <meta name="geo.position" content="23.6850;90.3563" />
    <meta name="ICBM" content="23.6850,90.3563" />
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebPage",
            "name": "FancyWin Leaderboard",
            "url": "https://fancybet-leaderboard.com/v2/leaderboard",
            "description": "View the FancyWin leaderboard with real-time player rankings and scores in Bangladesh.",
            "publisher": {
                "@type": "Organization",
                "name": "FancyWin",
                "logo": "https://fancybet-leaderboard.com/v2/images/logo.png"
            }
        }
    </script>
    <link rel="icon" type="image/x-icon" href="/v2/images/favicon.ico" />
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdn.jsdelivr.net/npm">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="./src/output.css" />
    <link rel="stylesheet" href="./css/style.css" />
    <script src="./js/jquery-3.7.1.min.js"></script>
</head>
<body class=" dark:bg-[#181818] bg-[#f5f5f5] dark:dark:text-white text-gray-900 min-h-screen">
    <?php
    include "./loading.php";
    ?>
    <?php
    include "./navbar.php"
    ?>
    <main class="pt-[90px] m-auto max-w-7xl px-4 pb-10">
        <h1 class="lg:text-3xl text-2xl font-extrabold text-center mb-6 text-red-700">
            FancyWin Leaderboard
        </h1>
        <div class="text-center space-x-4 mt-4 flex justify-center gap-4">
            <!-- Lion Button -->
            <button class="btn bg-red-700 border-none shadow-nonetext-white text-white px-6 py-2 flex items-center gap-2 hover:opacity-80 transition-all"
                data-file="/v2/lion-leaderboard-section"
                data-target="#container-lion"
                data-hide="#container-tiger">
                <img src="./images/lion-logo.png" class="w-6" alt="lion logo" loading="lazy">
                Lion
                <svg class="w-4 h-4 transition-transform duration-300 transform" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <!-- Tiger Button -->
            <button class="btn bg-yellow-600 border-none shadow-none text-white px-6 py-2 flex items-center gap-2 hover:opacity-80 transition-all"
                data-file="/v2/tiger-leaderboard-section"
                data-target="#container-tiger"
                data-hide="#container-lion">
                <img src="./images/tiger-logo.png" class="w-8" alt="tiger logo" loading="lazy">
                Tiger
                <svg class="w-4 h-4 transition-transform duration-300 transform" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>
        <div id="container-lion"
            class="overflow-hidden max-h-0 transition-all duration-500 ease-in-out text-white mt-5 mx-auto max-w-5xl">
        </div>
        <div id="container-tiger"
            class="overflow-hidden max-h-0 transition-all duration-500 ease-in-out mt-5 text-white mx-auto max-w-5xl">
        </div>
        <?php
        include "./touraments-section.php"
        ?>
        <?php
        include "./prev-tournament-section.php"
        ?>
    </main>
    <?php

    include "./footer.php"
    ?>
    <?php include 'scroll-to-top.php'; ?>
    <?php
    $js = file_get_contents('./js/leaderboard.js');
    $encoded = base64_encode($js);
    echo '<script src="data:text/javascript;base64,' . $encoded . '" defer></script>';
    ?>
</body>

</html>