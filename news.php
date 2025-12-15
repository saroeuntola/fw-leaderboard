<?php
include_once './admin/lib/db.php';
include_once './admin/lib/post_lib.php';
include_once './services/textLimit.php';

$listPost = new Post();

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 12; // items per page

$totalPosts = (int)$listPost->getPostCountByCategory(3);
$totalPages = $limit > 0 ? (int)ceil($totalPosts / $limit) : 1;
if ($totalPages < 1) $totalPages = 1;

$posts = $listPost->getPostByCategory(3, 'en', $limit, $page);
?>
<!DOCTYPE html>
<html lang="en-BD">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>News - FancyWin</title>

    <meta name="description" content="Read the latest gaming news and updates from Bangladesh. Stay informed about esports trends, tournament highlights, player stories, and FancyWin announcements.">

    <meta name="keywords" content="Bangladesh gaming news, BD esports news, FancyWin news, gaming updates Bangladesh, esports articles BD, Bangladesh gamer stories, gaming updates 2025 BD">

    <meta name="robots" content="index, follow">

    <link rel="canonical" href="https://fancybet-leaderboard.com/v2/news" />

    <!-- CSS -->
    <link rel="stylesheet" href="./src/output.css">
    <link rel="stylesheet" href="./css/style.css">
    <script src="./js/jquery-3.7.1.min.js"></script>
    <!-- Favicons -->
    <link rel="icon" href="/v2/favicon.ico" type="image/x-icon">

    <!-- Open Graph -->
    <meta property="og:title" content="FancyWin Gaming News - Bangladesh Updates">
    <meta property="og:description" content="Latest gaming and esports news from Bangladesh. Updates on tournaments, players, and FancyWin announcements.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://fancybet-leaderboard.com/v2/news">
    <meta property="og:image" content="https://fancybet-leaderboard.com/v2/images/icons/og-image.png">
    <meta property="og:locale" content="en_BD">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="FancyWin Gaming News - Bangladesh">
    <meta name="twitter:description" content="Read the latest gaming and esports updates from Bangladesh.">
    <meta name="twitter:image" content="https://fancybet-leaderboard.com/v2/images/icons/og-image.png">

    <!-- Local SEO -->
    <meta name="geo.region" content="BD">
    <meta name="geo.placename" content="Bangladesh">

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

    <!-- Schema: Local Business (Dhaka) -->
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


<body class="dark:bg-[#181818] dark:text-white text-gray-900 bg-[#f5f5f5]">
    <?php
    include "./loading.php";
    ?>
    <?php include "./navbar.php" ?>
    <main class="max-w-7xl m-auto px-4 pt-[90px] pb-10">
        <h1 class="lg:text-xl text-lg font-bold mb-4 dark:text-white text-gray-900">All News</h1>
        <div class="grid gap-5 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 cursor-pointer">
            <?php foreach ($posts as $post): ?>
                <a href="views-news?slug=<?= urlencode($post['slug']); ?>" class="dark:bg-[#252525] bg-white shadow-[0_0_5px_0_rgba(0,0,0,0.2)] rounded-md overflow-hidden hover:text-red-600">
                    <!-- Image with hover zoom -->
                    <div class="overflow-hidden rounded-t-md">
                        <img src="./admin/post/<?= htmlspecialchars($post['image']) ?>"
                            alt="<?= htmlspecialchars($post['name']) ?>" loading="lazy"
                            class="w-full h-60 transition-transform duration-500 hover:scale-105 object-cover">
                    </div>
                    <div class="p-4">
                        <h2 class="text-lg font-semibold mb-2 line-clamp-2 transition-all duration-300"> <?= htmlspecialchars(limitText($post['name'], 70)); ?></h2>
                        <div class="flex items-center gap-2 mt-2">
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
                    <!-- Page numbers (show a limited window) -->
                    <?php
                    $window = 5; // how many page links to show
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
    </main>
    <?php include "./footer.php" ?>
    <?php include 'scroll-to-top.php'; ?>
</body>

</html>