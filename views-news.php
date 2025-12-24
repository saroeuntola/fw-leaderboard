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
<html lang="bn-BD" class="">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Dynamic Title -->
    <title><?= htmlspecialchars($post['meta_title'] ?? '') ?></title>

    <!-- Description & Keywords -->
    <meta name="description" content="<?= htmlspecialchars($post['meta_desc'] ?? '') ?>" />
    <meta name="keywords" content="<?= htmlspecialchars($post['meta_keyword'] ?? '') ?>" />

    <!-- Canonical URL -->
    <link rel="canonical" href="https://fancybet-leaderboard.com/views-news?slug=<?= htmlspecialchars($post['slug'] ?? '') ?>" />

    <!-- Favicon (post image preview) -->
    <link rel="shortcut icon" href="/admin/post/<?= htmlspecialchars($post['image'] ?? '') ?>" type="image/png" />

    <!-- Styles -->
    <link rel="stylesheet" href="./src/output.css" />
    <link rel="stylesheet" href="./css/style.css" />

    <!-- Open Graph (Facebook / WhatsApp preview) -->
    <meta property="og:title" content="<?= htmlspecialchars($post['meta_title'] ?? '') ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($post['meta_desc'] ?? '') ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="https://fancybet-leaderboard.com/views-news?slug=<?= htmlspecialchars($post['slug'] ?? '') ?>" />
    <meta property="og:image" content="https://fancybet-leaderboard.com/admin/post/<?= htmlspecialchars($post['image'] ?? '') ?>" />
    <meta property="og:locale" content="en_BD" />
    <meta property="article:section" content="Gaming News" />
    <meta property="article:published_time" content="<?= htmlspecialchars($post['created_at'] ?? '') ?>" />
    <meta property="article:modified_time" content="<?= htmlspecialchars($post['updated_at'] ?? '') ?>" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= htmlspecialchars($post['meta_title'] ?? '') ?>" />
    <meta name="twitter:description" content="<?= htmlspecialchars($post['meta_desc'] ?? '') ?>" />
    <meta name="twitter:image" content="https://fancybet-leaderboard.com/admin/post/<?= htmlspecialchars($post['image'] ?? '') ?>" />
    <meta name="twitter:site" content="@FancyWin" />

    <!-- Local SEO -->
    <meta name="geo.region" content="BD" />
    <meta name="geo.placename" content="Bangladesh" />

    <!-- Article Schema (Google News) -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "NewsArticle",
            "headline": <?= json_encode($post['meta_title'] ?? '') ?>,
            "description": <?= json_encode($post['meta_desc'] ?? '') ?>,
            "image": [
                "https://fancybet-leaderboard.com/admin/post/<?= htmlspecialchars($post['image'] ?? '') ?>"
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
                    "url": "https://fancybet-leaderboard.com/images/icons/apple-touch-icon.png"
                }
            },
            "datePublished": "<?= htmlspecialchars($post['created_at'] ?? '') ?>",
            "dateModified": "<?= htmlspecialchars($post['updated_at'] ?? '') ?>",
            "mainEntityOfPage": {
                "@type": "WebPage",
                "@id": "https://fancybet-leaderboard.com/views-news?slug=<?= htmlspecialchars($post['slug'] ?? '') ?>"
            }
        }
    </script>
</head>



<body class="bg-[#f5f5f5] dark:bg-[#181818] dark:text-white text-gray-900">
    <?php include "./navbar.php" ?>
    <div class="container max-w-screen-xl mx-auto px-4 py-8 pt-[50px] flex flex-col lg:flex-row gap-6 mt-10 ">

        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col gap-4 bg-white shadow-[0_0_5px_0_rgba(0,0,0,0.2)] dark:bg-[#252525] p-4 rounded-md">
            <p class="w-20 border border-red-500 font-bold text-red-500 px-2 py-1 rounded-lg text-sm transition-colors text-center">
                খবর
            </p>

            <div class="rounded-lg">
                <?php if (!empty($post['image_mb'])): ?>
                    <picture>
                        <?php if (!empty($post['image_mb'])): ?>
                            <source
                                media="(max-width: 640px)"
                                srcset="/admin/post/<?= htmlspecialchars($post['image_mb']) ?>">
                        <?php endif; ?>
                        <source
                            media="(min-width: 641px)"
                            srcset="/admin/post/<?= htmlspecialchars($post['image']) ?>">
                        <img
                            src="/admin/post/<?= htmlspecialchars($post['image']) ?>"
                            class="w-full h-auto mb-4 rounded"
                            loading="lazy"
                            alt="<?= html_entity_decode($post['title'] ?? '') ?>">
                    </picture>
                <?php endif; ?>

                <h1 class="lg:text-3xl text-xl font-bold mb-2 text-red-600"><?= htmlspecialchars($post['name'] ?? '') ?></h1>

                <?php if (!empty($post['created_at'])): ?>
                    <?php
                    date_default_timezone_set('Asia/Phnom_Penh');
                    $createdAt = new DateTime($post['created_at']);
                    ?>
                    <p class="text-gray-400 text-sm mb-4">Published on <?= $createdAt->format('F j, Y') ?></p>
                <?php endif; ?>

                <div class="break-words desc-editor leading-relaxed dark:text-white text-gray-900" style="white-space: pre-line;">
                    <?php
                    $description = $post['description'] ?? '';
                    $description = str_replace('../api/content_image/', '/admin/api/content_image/', $description);
                    echo $description;
                    ?>
                </div>
            </div>
        </div>

        <!-- SIDEBAR -->
        <aside class="lg:w-80 w-full flex flex-col gap-4 lg:sticky lg:top-20 h-fit">

            <!-- RELATED POSTS -->
            <?php if (!empty($relatedPosts)): ?>
                <div class="bg-white shadow-[0_0_5px_0_rgba(0,0,0,0.2)] dark:bg-[#252525] rounded-md p-4">
                    <h2 class="text-xl font-bold mb-2 border-b border-gray-700 pb-2 dark:text-white text-gray-900">আরও খবর</h2>

                    <div class="flex flex-col gap-2">
                        <?php foreach ($relatedPosts as $rPost): ?>
                            <?php
                            $categoryName = strtolower(trim($rPost['category_name'] ?? ''));
                            $isExternal = !empty($rPost['game_link']); // check for external link
                            $link = $isExternal
                                ? $rPost['game_link'] // use external link
                                : ($categoryName === 'news'
                                    ? "./views-news?slug=" . urlencode($rPost['slug'])
                                    : ($categoryName === 'tournaments'
                                        ? "./views?slug=" . urlencode($rPost['slug'])
                                        : "./views?slug=" . urlencode($rPost['slug']))
                                );
                            ?>
                            <a href="<?= htmlspecialchars($link) ?>"
                                class="flex items-center gap-4 bg-white shadow-[0_0_5px_0_rgba(0,0,0,0.2)] dark:bg-[#252525] rounded-lg hover:bg-gray-700 transition-all duration-300 p-2 group"
                                <?= $isExternal ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>

                                <!-- Thumbnail -->
                                <?php if (!empty($rPost['image_mb'])): ?>
                                    <img src="/admin/post/<?= htmlspecialchars($rPost['image_mb']) ?>"
                                        alt="<?= html_entity_decode($rPost['name'] ?? '') ?>"
                                        class="image-more h-[100px] rounded-md flex-shrink-0 shadow-sm group-hover:scale-105 transition-transform duration-300">
                                <?php else: ?>
                                    <div class="image-more h-[100px] bg-gray-700 rounded-md flex items-center justify-center text-gray-400 text-sm">
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
                                        <p class="text-gray-400 text-xs"><?= date('F j, Y', strtotime($rPost['created_at'])) ?></p>
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
                    <div class="bg-white shadow-[0_0_5px_0_rgba(0,0,0,0.2)] dark:bg-[#252525] p-4 rounded-md">
                        <h3 class="mb-4 text-xl font-bold border-b border-gray-700 pb-2 dark:text-white text-gray-900">সর্বশেষ টুর্নামেন্ট ফলাফল</h3>
                        <a href="<?= htmlspecialchars($link) ?>" class="rounded-lg hover:scale-105 transition-transform">
                            <img src="<?= htmlspecialchars($t['image'] ? '/admin/uploads/' . $t['image'] : './images/img-card.png') ?>"
                                alt="<?= htmlspecialchars($t['title']); ?>"
                                class="w-full image-card rounded-md shadow-md"
                                loading="lazy">

                            <div class="py-2">
                                <h3 class="text-md sm:text-base font-semibold"><?= htmlspecialchars($t['title']); ?></h3>
                                <div class="mt-4">

                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-earth-americas text-gray-400"></i>
                                        <p class="text-gray-400 text-xs"><?= date('F j, Y', strtotime($t['created_at'])) ?></p>
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
    <?php include 'scroll-to-top.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editorTables = document.querySelectorAll('.desc-editor table');

            editorTables.forEach(table => {
                // Remove all inline styles and width/height attributes
                table.removeAttribute('style');
                table.removeAttribute('width');
                table.removeAttribute('height');
                table.removeAttribute('border');

                // Remove inline styles from col and colgroup
                table.querySelectorAll('col, colgroup').forEach(col => col.removeAttribute('style'));

                // Remove inline styles from tbody, thead, tfoot
                table.querySelectorAll('thead, tbody, tfoot').forEach(section => section.removeAttribute('style'));

                // Remove inline styles from all rows and cells
                table.querySelectorAll('tr, th, td').forEach(el => el.removeAttribute('style'));

                // Wrap table in a responsive wrapper if not already wrapped
                if (!table.parentElement.classList.contains('table-wrapper')) {
                    const wrapper = document.createElement('div');
                    wrapper.classList.add('table-wrapper');
                    table.parentNode.insertBefore(wrapper, table);
                    wrapper.appendChild(table);
                }
            });
        });
    </script>

</body>

</html>