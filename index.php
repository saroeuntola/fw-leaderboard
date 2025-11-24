<?php
include "./admin/lib/db.php";
?>
<!DOCTYPE html>
<html lang="bn-BD">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO Title -->
    <title>FancyWin - Tournaments, News & Leaderboard in Bangladesh</title>

    <!-- SEO Description -->
    <meta name="description" content="FancyWin Bangladesh – Explore the latest tournaments, gaming news, and real-time leaderboard updates. Stay informed with competitive rankings, breaking updates, and player statistics.">

    <!-- SEO Keywords -->
    <meta name="keywords" content="Bangladesh gaming tournaments, BD gaming news, Bangladesh leaderboard, FancyWin Bangladesh, BD online games ranking, gaming tournaments BD, esports Bangladesh, gaming news BD, top players Bangladesh, real-time leaderboard Bangladesh">

    <meta name="author" content="FancyWin">
    <meta name="robots" content="index, follow">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://fancybet-leaderboard.com/" />

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="./src/output.css">
    <link rel="stylesheet" href="./css/style.css">

    <!-- JS -->
    <script src="./js/jquery-3.7.1.min.js"></script>

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="57x57" href="/v2/icons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/v2/icons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/v2/icons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/v2/icons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/v2/icons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/v2/icons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/v2/icons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/v2/icons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/v2/icons/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/v2/icons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/v2/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/v2/icons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/v2/icons/favicon-16x16.png">
    <link rel="icon" href="/v2/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/v2/icons/favicon.ico" type="image/x-icon">

    <link rel="manifest" href="/v2/icons/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/v2/icons/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">


    <!-- Open Graph -->
    <meta property="og:title" content="FancyWin - Tournaments, News & Leaderboard in Bangladesh">
    <meta property="og:description" content="Discover tournaments, gaming news, and leaderboard rankings updated daily for Bangladeshi players.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://fancybet-leaderboard.com/">
    <meta property="og:image" content="https://fancybet-leaderboard.com/v2/images/icons/og-image.png">
    <meta property="og:locale" content="en_BD">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="FancyWin - Tournaments, News & Leaderboard in Bangladesh">
    <meta name="twitter:description" content="FancyWin Bangladesh – Latest tournaments, gaming news, and leaderboard updates.">
    <meta name="twitter:image" content="https://fancybet-leaderboard.com/v2/images/icons/og-image.png">
    <meta name="twitter:site" content="@FancyWin">

    <!-- Local SEO -->
    <meta name="geo.region" content="BD">
    <meta name="geo.placename" content="Bangladesh">
    <meta name="geo.position" content="23.6850;90.3563">
    <meta name="ICBM" content="23.6850,90.3563">

    <!-- Schema: Organization -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "FancyWin",
            "url": "https://fancybet-leaderboard.com",
            "logo": "https://fancybet-leaderboard.com/v2/images/icons/apple-touch-icon.png",
            "address": {
                "@type": "PostalAddress",
                "addressLocality": "Bangladesh",
                "addressCountry": "BD"
            }
        }
    </script>

    <!-- Schema: LocalBusiness Dhaka -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "LocalBusiness",
            "name": "FancyWin",
            "image": "https://fancybet-leaderboard.com/v2/images/icons/apple-touch-icon.png",
            "@id": "https://fancybet-leaderboard.com",
            "url": "https://fancybet-leaderboard.com",
            "telephone": "+8801645787953",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "1205, Dhaka",
                "addressLocality": "Dhaka",
                "addressRegion": "Dhaka Division",
                "postalCode": "1207",
                "addressCountry": "BD"
            },
            "geo": {
                "@type": "GeoCoordinates",
                "latitude": 23.8103,
                "longitude": 90.4125
            },
            "openingHoursSpecification": {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": [
                    "Monday", "Tuesday", "Wednesday",
                    "Thursday", "Friday", "Saturday"
                ],
                "opens": "08:00",
                "closes": "22:00"
            }
        }
    </script>

</head>

<body class="bg-[#f5f5f5] dark:bg-gray-900 text-gray-900  dark:text-gray-100 min-h-screen">


    <?php
    include "loading.php"
    ?>
    <header>
        <?php
        include "navbar.php"
        ?>
    </header>
    <main>
        <section class="px-4 max-w-7xl m-auto pt-[90px]">
            <?php
            include 'slideshow.php';
            ?>
        </section>

        <section class="px-4 max-w-7xl m-auto pt-[50px]">
            <?php
            include 'upcoming-section.php';
            ?>
        </section>

        <section class="px-4 max-w-7xl m-auto pt-[60px] pb-[50px]">
            <?php
            include 'last-news-section.php';
            ?>
        </section>
    </main>

    <?php
    include "footer.php"
    ?>
    <?php include 'scroll-to-top.php'; ?>
    <?php
    $js = file_get_contents('./js/slideshow.js');
    $encoded = base64_encode($js);
    echo '<script src="data:text/javascript;base64,' . $encoded . '" defer></script>';
    ?>

</body>

</html>