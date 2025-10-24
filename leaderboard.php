<?php
include "./admin/lib/db.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="./src/output.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="icon" type="image/x-icon" href="/v2/images/favicon.ico">
</head>

<body class=" dark:bg-gray-900 bg-gray-200 dark:dark:text-white text-gray-900 min-h-screen">

    <?php
    include "./navbar.php"
    ?>
    <main class="pt-32 m-auto max-w-7xl px-4 pb-32">

        <h1 class="text-red-600 text-3xl text-center font-bold mb-4">Leaderboard</h1>

        <div class="text-center space-x-4 mt-4 flex justify-center gap-4">
            <!-- Lion Button -->
            <button class="btn bg-red-700 border-none shadow-nonetext-white text-white px-6 py-2 flex items-center gap-2 hover:opacity-80 transition-all"
                data-file="/v2/lion-leaderboard-section"
                data-target="#container-lion"
                data-hide="#container-tiger">
                <img src="./images/lion-logo.png" class="w-6" alt="">
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
                <img src="./images/tiger-logo.png" class="w-8" alt="">
                Tiger
                <svg class="w-4 h-4 transition-transform duration-300 transform" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>

        <!-- Containers -->
        <div id="container-lion"
            class="overflow-hidden max-h-0 transition-all duration-500 ease-in-out text-white mt-5 mx-auto max-w-5xl">
        </div>
        <div id="container-tiger"
            class="overflow-hidden max-h-0 transition-all duration-500 ease-in-out mt-5 text-white mx-auto max-w-5xl">
        </div>

        <!-- tourament -->
        <?php
        include "./touraments-section.php"
        ?>
        <!-- 
        prev tourament -->
        <?php
        include "./prev-tournament-section.php"
        ?>
    </main>
    <?php

    include "./footer.php"
    ?>
    <script src="./js/leaderboard.js"></script>
</body>

</html>