<?php
include './admin/lib/post_lib.php';
include './admin/lib/db.php';
include './admin/lib/prev_tournament_lib.php';
$slug = $_GET['slug'] ?? '';
$postLib = new Post();
$post = $postLib->getPostBySlug($slug);
$currentSlug = $_GET['slug'] ?? '';
$relatedPosts = $postLib->getRelatedpost($post['id'] ?? 0, $post['category_id'] ?? 0, 5);

$tournament = new TournamentPost();
$latestTournament = $tournament->getLatest(1);
?>
<!DOCTYPE html>
<html lang="en" class="bg-gray-800">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Dynamic Title -->
    <title><?= htmlspecialchars($post['name'] ?? '') ?> - FancyWin Tournament Bangladesh</title>

    <!-- Description & Keywords -->
    <meta name="description" content="<?= htmlspecialchars($post['meta_desc'] ?? '') ?>" />
    <meta name="keywords" content="<?= htmlspecialchars($post['meta_keyword'] ?? '') ?>" />

    <!-- Canonical URL -->
    <link rel="canonical" href="https://fancybet-leaderboard.com/v2/views/<?= htmlspecialchars($post['slug'] ?? '') ?>" />

    <!-- Favicon (post image preview) -->
    <link rel="shortcut icon" href="/v2/admin/post/<?= htmlspecialchars($post['image'] ?? '') ?>" type="image/png" />

    <!-- Styles -->
    <link rel="stylesheet" href="./src/output.css" />
    <link rel="stylesheet" href="./css/style.css" />

    <!-- Open Graph (Facebook / WhatsApp preview) -->
    <meta property="og:title" content="<?= htmlspecialchars($post['name'] ?? '') ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($post['meta_desc'] ?? '') ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="https://fancybet-leaderboard.com/v2/views/<?= htmlspecialchars($currentSlug['slug'] ?? '') ?>" />
    <meta property="og:image" content="https://fancybet-leaderboard.com/v2/admin/post/<?= htmlspecialchars($post['image'] ?? '') ?>" />
    <meta property="og:locale" content="en_BD" />
    <meta property="article:section" content="Gaming News" />
    <meta property="article:published_time" content="<?= htmlspecialchars($post['created_at'] ?? '') ?>" />
    <meta property="article:modified_time" content="<?= htmlspecialchars($post['updated_at'] ?? '') ?>" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= htmlspecialchars($post['name'] ?? '') ?>" />
    <meta name="twitter:description" content="<?= htmlspecialchars($post['meta_desc'] ?? '') ?>" />
    <meta name="twitter:image" content="https://fancybet-leaderboard.com/v2/admin/post/<?= htmlspecialchars($post['image'] ?? '') ?>" />
    <meta name="twitter:site" content="@FancyWin" />

    <!-- Local SEO -->
    <meta name="geo.region" content="BD" />
    <meta name="geo.placename" content="Bangladesh" />

    <!-- Article Schema (Google News) -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "NewsArticle",
            "headline": <?= json_encode($post['name'] ?? '') ?>,
            "description": <?= json_encode($post['meta_desc'] ?? '') ?>,
            "image": [
                "https://fancybet-leaderboard.com/v2/admin/post/<?= htmlspecialchars($post['image'] ?? '') ?>"
            ],
            "author": {
                "@type": "Organization",
                "name": "FancyWin"
            },
            "publisher": {
                "@type": "Organization",
                "name": "FancyWin",
                "logo": {
                    "@type": "ImageObject",
                    "url": "https://fancybet-leaderboard.com/v2/images/icons/apple-touch-icon.png"
                }
            },
            "datePublished": "<?= htmlspecialchars($post['created_at'] ?? '') ?>",
            "dateModified": "<?= htmlspecialchars($post['updated_at'] ?? '') ?>",
            "mainEntityOfPage": {
                "@type": "WebPage",
                "@id": "https://fancybet-leaderboard.com/v2/views/<?= htmlspecialchars($post['slug'] ?? '') ?>"
            }
        }
    </script>
</head>

<body class="bg-gray-200 dark:bg-gray-900 dark:text-white text-gray-900">
    <?php include "./navbar.php" ?>
    <div class="container max-w-screen-xl mx-auto px-4 py-8 pt-20 pb-20 flex flex-col lg:flex-row gap-6 mt-10">

        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col gap-6">
            <div class="rounded-lg">
                <?php if (!empty($post['image'])): ?>
                    <img src="/v2/admin/post/<?= htmlspecialchars($post['image']) ?>" class="w-full md:h-[380px] h-[220px] lg:h-[400px] mb-4 rounded" loading="lazy">
                <?php endif; ?>

                <h1 class="lg:text-3xl text-xl font-bold mb-2 text-red-600"><?= htmlspecialchars($post['name'] ?? '') ?></h1>

                <?php if (!empty($post['created_at'])): ?>
                    <?php
                    date_default_timezone_set('Asia/Phnom_Penh');
                    $createdAt = new DateTime($post['created_at']);
                    ?>
                    <p class="text-gray-400 text-sm mb-4">Published on <?= $createdAt->format('F j, Y') ?></p>
                <?php endif; ?>

                <div class="break-words desc-editor leading-relaxed dark:text-white text-gray-900">
                    <?php
                    $description = $post['description'] ?? '';
                    $description = str_replace('../api/content_image/', '/v2/admin/api/content_image/', $description);
                    echo $description;
                    ?>
                </div>
            </div>
        </div>

        <!-- SIDEBAR -->
        <aside class="lg:w-80 w-full flex flex-col gap-4 lg:sticky lg:top-24 h-fit">
            <!-- RELATED POSTS -->
            <?php if (!empty($relatedPosts)): ?>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-4 shadow-md">
                    <h2 class="text-xl font-bold mb-2 border-b border-gray-700 pb-2 dark:text-white text-gray-900">More News</h2>

                    <div class="flex flex-col gap-2">
                        <?php foreach ($relatedPosts as $rPost): ?>
                            <?php
                            // Normalize the category name
                            $categoryName = strtolower(trim($rPost['category_name'] ?? ''));

                            // Select link based on category
                            if ($categoryName === 'news') {
                                $link = "./views-news?slug=" . urlencode($rPost['slug']);
                            } elseif ($categoryName === 'tournaments') {
                                $link = "./views?slug=" . urlencode($rPost['slug']);
                            } else {
                                // Default fallback
                                $link = "./views?slug=" . urlencode($rPost['slug']);
                            }
                            ?>
                            <a href="<?= htmlspecialchars($link) ?>"
                                class="flex items-center gap-4 bg-white dark:bg-gray-900 rounded-lg hover:bg-gray-700 transition-all duration-300 p-2 group">
                                <!-- Thumbnail -->
                                <?php if (!empty($rPost['image'])): ?>
                                    <img src="/v2/admin/post/<?= htmlspecialchars($rPost['image']) ?>"
                                        alt="<?= htmlspecialchars($rPost['name']) ?>"
                                        class="w-[80px] h-[80px] rounded-m flex-shrink-0 shadow-sm group-hover:scale-105 transition-transform duration-300">
                                <?php else: ?>
                                    <div class="w-[80px] h-[80px] bg-gray-700 rounded-md flex items-center justify-center text-gray-400 text-sm">
                                        No Image
                                    </div>
                                <?php endif; ?>

                                <!-- Info -->
                                <div class="flex-1">
                                    <h3 class="dark:text-white text-gray-900 text-sm sm:text-base font-semibold leading-tight line-clamp-2 group-hover:text-red-400 transition-colors mb-2 duration-300">
                                        <?= htmlspecialchars($rPost['name']) ?>
                                    </h3>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-earth-americas text-gray-400"></i>
                                        <p class="text-gray-400 text-xs"><?= date('F-j-Y', strtotime($rPost['created_at'])) ?></p>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- LATEST TOURNAMENT RESULTS -->
            <?php if (!empty($latestTournament)): ?>
                <?php foreach ($latestTournament as $t): ?>
                    <?php
                    $link = $t['type'] === 'lion'
                        ? "views-lion-result?id=" . urlencode($t['id'])
                        : "views-tiger-result?id=" . urlencode($t['id']);
                    ?>
                    <div class="dark:bg-gray-800 p-4 rounded-2xl bg-white">
                        <h3 class="mb-4 text-xl font-bold border-b border-gray-700 pb-2 dark:text-white text-gray-900">Latest Tournament Result</h3>
                        <a href="<?= htmlspecialchars($link) ?>" class="rounded-lg hover:scale-105 transition-transform">
                            <img src="<?= htmlspecialchars($t['image'] ? '/v2/admin/uploads/' . $t['image'] : './images/img-card.png') ?>"
                                alt="<?= htmlspecialchars($t['title']); ?>"
                                class="w-full image-card rounded-md shadow-md"
                                loading="lazy">

                            <div class="py-2">
                                <h3 class="text-md sm:text-base font-semibold"><?= htmlspecialchars($t['title']); ?></h3>
                                <div class="mt-4">

                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-earth-americas text-gray-400"></i>
                                        <p class="text-gray-400 text-xs"><?= date('F d, Y', strtotime($t['created_at'])) ?></p>
                                    </div>

                                </div>
                            </div>
                        </a>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <p>No tournaments available.</p>
            <?php endif; ?>
        </aside>

    </div>
    <?php
    include "./footer.php"
    ?>
</body>

</html>