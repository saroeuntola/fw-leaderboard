<?php
   include "./admin/lib/db.php";
?>
<!DOCTYPE html>
<html lang="en" class="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FancyWin Leaderboard</title>

    <link rel="stylesheet" href="./src/output.css">
    <link rel="stylesheet" href="./css/style.css">
    <script src="./js/jquery-3.7.1.min.js"></script>
    <link rel="icon" type="image/png" href="/v2/iamges/icons/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/v2/iamges/icons/favicon.svg" />
    <link rel="shortcut icon" href="/v2/images/icons/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/v2/images/icons/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="FancyWin" />
    <link rel="manifest" href="/v2/images/icons/site.webmanifest" />

</head>

<body class="bg-gray-200 text-gray-900 dark:bg-gray-900 dark:text-gray-100 min-h-screen">
    <?php 
      include "loading.php"
    ?>
    <header>
        <?php
        include "navbar.php"
        ?>
    </header>
    <main>
        <section class="px-4 max-w-7xl m-auto pt-[100px]">
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

    <?php
    $js = file_get_contents('./js/slideshow.js');
    $encoded = base64_encode($js);
    echo '<script src="data:text/javascript;base64,' . $encoded . '" defer></script>';
    ?>
</body>

</html>