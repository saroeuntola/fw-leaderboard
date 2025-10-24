<?php
include './admin/lib/post_lib.php';
include './admin/lib/db.php';
include './admin/lib/lion_tournament_lib.php';
include './admin/lib/tiger_tourament_lib.php';
$slug = $_GET['slug'] ?? '';
$postLib = new Post();
$post = $postLib->getPostBySlug($slug);
$currentSlug = $_GET['slug'] ?? '';
$relatedPosts = $postLib->getRelatedpost($post['id'] ?? 0, $post['category_id'] ?? 0, 5);

$lionTournament = new TournamentPost();
$lionLatest = $lionTournament->getLatest(1);
if (!empty($lionLatest)) {
    foreach ($lionLatest as &$item) {
        $item['type'] = 'lion';
    }
}

$tigerTournament = new TigerTouramentPost();
$tigerLatest = $tigerTournament->getLatest(1);
if (!empty($tigerLatest)) {
    foreach ($tigerLatest as &$item) {
        $item['type'] = 'tiger';
    }
}
// Merge and sort both tournaments by created_at
$allTournaments = array_merge($lionLatest, $tigerLatest);

// Sort by date descending (newest first)
usort($allTournaments, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Get only the latest one
$latestTournament = array_slice($allTournaments, 0, 1);
?>
<!DOCTYPE html>
<html lang="en" class="bg-gray-800">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($post['name'] ?? '') ?></title>
    <link rel="shortcut icon" href="/v2/admin/post/<?= htmlspecialchars($post['image'] ?? '') ?>" type="image">
    <meta name="description" content="<?= htmlspecialchars(($post['meta_desc'] ?? '')) ?>" />
    <meta name="keywords" content="<?= htmlspecialchars($post['meta_keyword'] ?? '') ?>" />
    <link rel="stylesheet" href="./src/output.css">
</head>

<style>
    .desc-editor table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
        font-size: 15px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }

    .desc-editor th,
    .desc-editor td {
        padding: 12px 16px;
        text-align: left;
        border: 1px solid #e2e8f0;
    }

    .desc-editor th {
        font-weight: 600;
        text-align: center;
        background-color: brown;
        color: white;
    }


    .desc-editor h2 {
        font-size: 22pt;
        color: rgb(220 38 38);
        font-weight: bold;
    }

    .desc-editor h3 {
        font-size: 16pt;
        font-weight: bold;
    }

    @media (max-width: 768px) {
        .desc-editor h2 {
            font-size: 16pt;
        }

        .desc-editor h3 {
            font-size: 14pt;
        }
    }
</style>

<body class="bg-gray-200 dark:bg-gray-900 dark:text-white text-gray-900">
    <?php include "./navbar.php" ?>
    <div class="container max-w-screen-xl mx-auto px-4 py-8 pt-20 pb-20 flex flex-col lg:flex-row gap-6 mt-10">

        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col gap-6">
            <div class="rounded-lg">
                <?php if (!empty($post['image'])): ?>
                    <img src="/v2/admin/post/<?= htmlspecialchars($post['image']) ?>" class="w-full md:h-[380px] h-[220px] lg:h-[400px] mb-4 rounded">
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
                            <a href="./views.php?slug=<?= urlencode($rPost['slug']) ?>"
                                class="flex items-center gap-4 bg-white dark:bg-gray-900 rounded-lg hover:bg-gray-700 transition-all duration-300 p-2 group">

                                <!-- Thumbnail -->
                                <?php if (!empty($rPost['image'])): ?>
                                    <img src="/v2/admin/post/<?= htmlspecialchars($rPost['image']) ?>"
                                        alt="<?= htmlspecialchars($rPost['name']) ?>"
                                        class="w-[80px] h-[80px] rounded-md object-cover flex-shrink-0 shadow-sm group-hover:scale-105 transition-transform duration-300">
                                <?php else: ?>
                                    <div class="w-[80px] h-[80px] bg-gray-700 rounded-md flex items-center justify-center text-gray-400 text-sm">
                                        No Image
                                    </div>
                                <?php endif; ?>

                                <!-- Info -->
                                <div class="flex-1">
                                    <h3 class="dark:text-white text-gray-900 text-sm sm:text-base font-semibold leading-tight line-clamp-2 group-hover:text-red-400 transition-colors duration-300">
                                        <?= htmlspecialchars($rPost['name']) ?>
                                    </h3>
                                    <p class="text-xs text-gray-400 mt-1"><?= htmlspecialchars(date('F d, Y', strtotime($rPost['created_at']))); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>



            <!-- LATEST TOURNAMENT RESULTS -->
            <?php if (!empty($latestTournament)): ?>
                <div class="dark:bg-gray-800 bg-gray-100 rounded-xl p-4">
                    <h2 class="text-xl font-bold mb-3 border-b border-gray-700 pb-2 dark:text-white text-gray-900">
                        Latest Tournament Results
                    </h2>
                    <div class="space-y-3">
                        <?php foreach ($latestTournament as $t): ?>
                            <?php
                            // Dynamic link based on tournament type
                            $link = ($t['type'] === 'lion')
                                ? "views-lion-result?id=" . urlencode($t['id'])
                                : "views-tiger-result?id=" . urlencode($t['id']);
                            ?>
                            <a href="<?= htmlspecialchars($link) ?>" class="block rounded-lg hover:scale-105 transition-transform">
                                <!-- Tournament Image -->
                                <?php if (!empty($t['image'])): ?>
                                    <img src="./admin/uploads/<?= htmlspecialchars($t['image']); ?>"
                                        alt="<?= htmlspecialchars($t['title']); ?>"
                                        class="w-full h-40 object-cover rounded-md shadow-md">
                                <?php endif; ?>

                                <!-- Tournament Info -->
                                <div class="py-2">
                                    <h3 class="text-md sm:text-base font-semibold leading-tight line-clamp dark:text-white text-gray-900 mb-2"><?= htmlspecialchars($t['title']); ?></h3>
                                    <p class="text-sm sm:text-base text-gray-400 leading-tight line-clamp mt-1"><?= htmlspecialchars(date('F d, Y', strtotime($t['created_at']))); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </aside>

    </div>
    <?php
    include "./footer.php"
    ?>
</body>

</html>