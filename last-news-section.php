<?php
include_once './admin/lib/post_lib.php';
include_once './admin/lib/prev_tournament_lib.php';
$tournament = new TournamentPost();
$lionLatest = $tournament->getLatestByType('lion', 1);
$tigerLatest = $tournament->getLatestByType('tiger', 1);
$latestTournament = array_merge($lionLatest, $tigerLatest);
usort($latestTournament, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$listPost = new Post();
$posts = $listPost->getLastPosts(4, 'en');
?>
<h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-gray-100 md:text-left text-center">Latest News</h1>

<div class="grid gap-3 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 text-gray-900 dark:text-gray-100 cursor-pointer">
    <?php foreach ($posts as $post): ?>
        <?php
        // Normalize the category name
        $categoryName = strtolower(trim($post['category_name'] ?? ''));

        // Choose the correct link based on category
        if ($categoryName === 'news') {
            $link = "views-news?slug=" . urlencode($post['slug']);
        } elseif ($categoryName === 'tournaments') {
            $link = "views?slug=" . urlencode($post['slug']);
        } else {
            // Default link if category doesn't match
            $link = "views?slug=" . urlencode($post['slug']);
        }
        ?>
        <a href="<?= htmlspecialchars($link); ?>" class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow">
            <!-- Image with hover zoom -->
            <div class="overflow-hidden rounded-t-xl">
                <img src="./admin/post/<?= htmlspecialchars($post['image']); ?>"
                    alt="<?= htmlspecialchars($post['name']); ?>" loading="lazy"
                    class="w-full h-60 transition-transform duration-500 hover:scale-105">
            </div>
            <div class="p-4">
                <h2 class="text-lg font-semibold mb-2 mr-6 truncate"><?= htmlspecialchars($post['name']); ?></h2>
                <div class="items-center flex mt-2 gap-2">
                    <i class="fa-solid fa-earth-americas text-gray-400"></i>
                    <p class="text-gray-400 text-xs"><?= date('F j, Y', strtotime($post['created_at'])); ?></p>
                </div>

            </div>
        </a>
    <?php endforeach; ?>
</div>


<section class="mt-10">
    <h1 class="dark:text-white text-gray-900 lg:text-3xl text-xl font-bold mb-4">Previous Tournaments</h1>
    <?php if (!empty($latestTournament)): ?>
        <?php foreach ($latestTournament as $item): ?>
            <?php
            // Determine the correct link based on type
            $link = $item['type'] === 'tiger'
                ? "/v2/views-tiger-result?id=" . urlencode($item['id'])
                : "/v2/views-lion-result?id=" . urlencode($item['id']);
            ?>
            <div class="dark:bg-gray-800 bg-white rounded-xl shadow-md flex flex-col md:flex-row items-center justify-between gap-4 overflow-hidden mb-4">

                <!-- Left: Image -->
                <img src="./admin/uploads/<?= htmlspecialchars($item['image']) ?>"
                    alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy"
                    class="w-full md:w-32 h-48 md:h-24 rounded-lg flex-shrink-0">

                <!-- Center: Title & Date -->
                <div class="flex-1 text-center md:text-left px-4">
                    <h2 class="dark:text-white text-gray-900 text-lg font-semibold "><?= htmlspecialchars($item['title']) ?></h2>
                    <div class="flex items-center mt-1 gap-2">
                        <i class="fa-solid fa-earth-americas text-gray-400"></i>
                        <p class="text-gray-400 text-sm">
                            <?= htmlspecialchars(date('F-j-Y', strtotime($item['created_at']))) ?>
                        </p>
                    </div>
                </div>

                <!-- Right: Button -->
                <div class="px-2 lg:w-auto w-full lg:mb-0 mb-4">
                    <a href="<?= $link ?>"
                        class="block border border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-4 py-2 rounded-lg text-sm transition-colors w-full md:w-auto text-center">
                        See Result
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-gray-400">No tournaments available.</p>
    <?php endif; ?>
</section>