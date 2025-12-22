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

    <title>Fancybet টুর্নামেন্ট – বাংলাদেশে গেমিং টুর্নামেন্ট</title>

    <meta name="description" content="বাংলাদেশের চলমান ও আসন্ন গেমিং টুর্নামেন্টগুলো দেখুন। Fancybet ইস্পোর্টস ইভেন্টে অংশ নিন, শীর্ষ খেলোয়াড়দের সাথে প্রতিযোগিতা করুন এবং টুর্নামেন্টের অবস্থান অনুসরণ করুন।">

    <meta name="keywords" content="বাংলাদেশ গেমিং টুর্নামেন্ট, বিডি ইস্পোর্টস প্রতিযোগিতা, FancyWin টুর্নামেন্ট, অনলাইন গেমিং বাংলাদেশ, ইস্পোর্টস ইভেন্ট বিডি, বিডি গেমিং চ্যাম্পিয়নশিপ, বাংলাদেশ টুর্নামেন্ট সময়সূচি">

    <meta name="robots" content="index, follow">

    <link rel="canonical" href="https://fancybet-leaderboard.com/tournaments" />

    <link rel="stylesheet" href="./src/output.css">
    <link rel="stylesheet" href="./css/style.css">
    <script src="./js/jquery-3.7.1.min.js"></script>

    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <!-- Open Graph -->
    <meta property="og:title" content="FancyWin টুর্নামেন্ট – বাংলাদেশে গেমিং টুর্নামেন্ট">
    <meta property="og:description" content="বাংলাদেশে অনুষ্ঠিত সর্বশেষ গেমিং টুর্নামেন্টগুলো আবিষ্কার করুন। প্রতিযোগিতায় অংশ নিন এবং ফলাফল অনুসরণ করুন।">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://fancybet-leaderboard.com/tournaments">
    <meta property="og:image" content="https://fancybet-leaderboard.com/images/icons/og-image.png">
    <meta property="og:locale" content="bn_BD">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="FancyWin টুর্নামেন্ট – বাংলাদেশে গেমিং টুর্নামেন্ট">
    <meta name="twitter:description" content="বাংলাদেশের গেমিং টুর্নামেন্টে অংশ নিন এবং লিডারবোর্ড অনুসরণ করুন।">
    <meta name="twitter:image" content="https://fancybet-leaderboard.com/images/icons/og-image.png">

    <!-- Geo -->
    <meta name="geo.region" content="BD">
    <meta name="geo.placename" content="বাংলাদেশ">

    <!-- Schema: Organization -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "FancyWin",
            "url": "https://fancybet-leaderboard.com",
            "logo": "https://fancybet-leaderboard.com/images/icons/apple-touch-icon.png",
            "address": {
                "@type": "PostalAddress",
                "addressLocality": "বাংলাদেশ",
                "addressCountry": "BD"
            }
        }
    </script>

    <!-- Schema: LocalBusiness -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "LocalBusiness",
            "name": "FancyWin",
            "url": "https://fancybet-leaderboard.com",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "1205, ঢাকা",
                "addressLocality": "ঢাকা",
                "addressRegion": "ঢাকা বিভাগ",
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


<body class="min-h-screen dark:bg-[#181818] bg-[#f5f5f5] dark:text-white text-gray-900">
    <?php
    include "./loading.php";
    ?>
    <?php include "./navbar.php" ?>
    <main class="max-w-7xl m-auto px-4 pt-[90px] pb-10 min-h-screen">
        <h1 class="lg:text-xl text-lg font-bold mb-4 dark:text-white text-gray-900">সকল টুর্নামেন্ট</h1>
        <?php if (!empty($posts)): ?>
            <div class="grid gap-5 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 cursor-pointer">
                <?php foreach ($posts as $post): ?>
                    <?php
                    $isExternal = !empty($post['game_link']);
                    $postUrl = $isExternal
                        ? $post['game_link']
                        : "/views?slug=" . urlencode($post['slug']);
                    ?>
                    <a href="<?= htmlspecialchars($postUrl) ?>"
                        class="dark:bg-[#252525] bg-white shadow-[0_0_5px_0_rgba(0,0,0,0.2)] rounded-md overflow-hidden hover:text-red-600"
                        <?= $isExternal ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>

                        <div class="overflow-hidden rounded-t-md">
                            <img src="./admin/post/<?= htmlspecialchars($post['image']) ?>"
                                alt="<?= htmlspecialchars($post['name']) ?>"
                                loading="lazy"
                                class="w-full h-60 transition-transform duration-500 hover:scale-105 object-cover">
                        </div>

                        <div class="p-4">
                            <h2 class="text-lg font-semibold mb-2 line-clamp-2 transition-all duration-300">
                                <?= htmlspecialchars(limitText($post['name'], 70)); ?>
                            </h2>
                            <div class="flex items-center gap-2 mt-2">
                                <i class="fa-solid fa-earth-americas text-gray-400"></i>
                                <p class="text-gray-400 text-xs">
                                    <?= date('F j, Y', strtotime($post['created_at'])) ?>
                                </p>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <!-- No Tournament Message -->
            <div class="flex items-center justify-center min-h-[70vh]">
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-500 dark:text-gray-400 mb-2">
                        No Tournament Available
                    </h2>
                    <p class="text-sm text-gray-400">
                        Please check back later for upcoming tournaments.
                    </p>
                </div>
            </div>
        <?php endif; ?>

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