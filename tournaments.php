<?php
include_once './admin/lib/db.php';
include_once './admin/lib/post_lib.php';
include_once './services/textLimit.php';
$listPost = new Post();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 12;

$totalPosts = (int)$listPost->getPostCountByCategory(3);
$totalPages = $limit > 0 ? (int)ceil($totalPosts / $limit) : 1;
if ($totalPages < 1) $totalPages = 1;

$posts = $listPost->getPostByCategory(2, 'en', $limit, $page);
?>
<!DOCTYPE html>
<html lang="en-BD">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FancyWin Fancybet Tournaments - Gaming Tournament in Bangladesh</title>
    <meta name="description" content="Explore active and upcoming gaming tournaments in Bangladesh. Join FancyWin esports events, compete with top players, and track tournament standings.">
    <meta name="keywords" content="Bangladesh gaming tournaments, BD esports competitions, FancyWin tournaments, online gaming Bangladesh, esports events BD, BD gaming championship, Bangladesh tournament schedule">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://fancybet-leaderboard.com/tournaments" />
    <link rel="stylesheet" href="./src/output.css">
    <link rel="stylesheet" href="./css/style.css">
    <script src="./js/jquery-3.7.1.min.js"></script>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta property="og:title" content="FancyWin Tournaments - Gaming Tournament in Bangladesh">
    <meta property="og:description" content="Discover the latest gaming tournaments happening in Bangladesh. Join competitions and follow standings.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://fancybet-leaderboard.com/tournaments">
    <meta property="og:image" content="https://fancybet-leaderboard.com/images/icons/og-image.png">
    <meta property="og:locale" content="en_BD">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="FancyWin Tournaments - Gaming Tournament in Bangladesh">
    <meta name="twitter:description" content="Join Bangladesh gaming tournaments and track standings.">
    <meta name="twitter:image" content="https://fancybet-leaderboard.com/images/icons/og-image.png">
    <meta name="geo.region" content="BD">
    <meta name="geo.placename" content="Bangladesh">
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "FancyWin",
            "url": "https://fancybet-leaderboard.com",
            "logo": "https://fancybet-leaderboard.com/images/icons/apple-touch-icon.png",
            "address": {
                "@type": "PostalAddress",
                "addressLocality": "Bangladesh",
                "addressCountry": "BD"
            }
        }
    </script>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "LocalBusiness",
            "name": "FancyWin",
            "url": "https://fancybet-leaderboard.com",
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
            }
        }
    </script>
</head>
<body class="dark:bg-[#181818] bg-[#f5f5f5] dark:text-white text-gray-900">
    <?php
    include "./loading.php";
    ?>
    <?php include "./navbar.php" ?>
    <main class="max-w-7xl m-auto px-4 pt-[90px] pb-10">
        <h1 class="lg:text-xl text-lg font-bold mb-4 dark:text-white text-gray-900">All Tournaments</h1>
        <div class="grid gap-5 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 cursor-pointer">
            <?php foreach ($posts as $post): ?>
                <a href="views?slug=<?= urlencode($post['slug']); ?>" class="bg-white dark:bg-[#252525]
            shadow-[0_0_5px_0_rgba(0,0,0,0.2)] rounded-md overflow-hidden hover:text-red-600">
                    <!-- Image with hover zoom -->
                    <div class="overflow-hidden rounded-t-md">
                        <img src="./admin/post/<?= htmlspecialchars($post['image']) ?>"
                            alt="<?= htmlspecialchars($post['name']) ?>"
                            loading="lazy"
                            class="w-full h-60 transition-transform duration-500 hover:scale-105 object-cover">

                    </div>
                    <div class="p-4">
                        <h2 class="text-lg font-semibold mb-2 line-clamp-2 transition-all duration-300"><?= htmlspecialchars(limitText($post['name'], 70)); ?></h2>

                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-earth-americas text-gray-400"></i>
                            <p class="text-gray-400 text-xs"><?= date('F-j-Y', strtotime($post['created_at'])) ?></p>
                        </div>

                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-6 flex items-center justify-center" aria-label="Pagination">
                <ul class="inline-flex items-center -space-x-px">
                    <!-- Previous -->
                    <li>
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>" class="px-3 py-1.5 rounded-l-md bg-gray-800 text-white hover:bg-gray-700">Prev</a>
                        <?php else: ?>
                            <span class="px-3 py-1.5 rounded-l-md bg-gray-700 text-gray-400">Prev</span>
                        <?php endif; ?>
                    </li>
                    <?php
                    $window = 5;
                    $start = max(1, $page - floor($window / 2));
                    $end = min($totalPages, $start + $window - 1);
                    if ($end - $start + 1 < $window) {
                        $start = max(1, $end - $window + 1);
                    }
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <li>
                            <?php if ($i == $page): ?>
                                <span class="px-3 py-1.5 bg-indigo-600 text-white font-medium"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?>" class="px-3 py-1.5 bg-gray-800 text-white hover:bg-gray-700"><?= $i ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>

                    <!-- Next -->
                    <li>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>" class="px-3 py-1.5 rounded-r-md bg-gray-800 text-white hover:bg-gray-700">Next</a>
                        <?php else: ?>
                            <span class="px-3 py-1.5 rounded-r-md bg-gray-700 text-gray-400">Next</span>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

        <?php include 'scroll-to-top.php'; ?>

    </main>

    <?php include "./footer.php" ?>
</body>

</html>