<?php
include_once './admin/lib/post_lib.php';
include_once './admin/lib/lion_tournament_lib.php';
include_once './admin/lib/tiger_tourament_lib.php';

$listPost = new Post();
$posts = $listPost->getLastPosts(4, 'en');
// Fetch latest Lion & Tiger tournament (1 each)
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
$allTournaments = array_merge($lionLatest, $tigerLatest);

usort($allTournaments, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

$latestTournament = array_slice($allTournaments, 0, 2);

?>
<h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-gray-100 md:text-left text-center">Latest News</h1>

<div class="grid gap-3 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 text-gray-900 dark:text-gray-100 cursor-pointer">
    <?php foreach ($posts as $post): ?>
        <a href="views?slug=<?= urlencode($post['slug']); ?>" class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow">
            <!-- Image with hover zoom -->
            <div class="overflow-hidden rounded-t-xl">
                <img src="./admin/post/<?= htmlspecialchars($post['image']) ?>"
                    alt="<?= htmlspecialchars($post['name']) ?>"
                    class="w-full h-60 object-cover transition-transform duration-500 hover:scale-105">
            </div>
            <div class="p-4">
                <h2 class="text-lg font-semibold mb-2 mr-6 truncate"><?= htmlspecialchars($post['name']) ?></h2>
                <p class="text-gray-400 text-xs mt-2"><?= date('F-j-Y', strtotime($post['created_at'])) ?></p>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<section class="mt-10">
    <h1 class="text-gray-900 dark:text-gray-100 text-3xl font-bold mb-6 md:text-left text-center">Previous Tournaments</h1>
    <?php if (!empty($latestTournament)): ?>
        <?php foreach ($latestTournament as $item): ?>
            <?php
            // Determine the correct link based on type
            $link = $item['type'] === 'tiger'
                ? "/v2/views-tiger-result?id=" . urlencode($item['id'])
                : "/v2/views-lion-result?id=" . urlencode($item['id']);
            ?>
            <div class="dark:bg-gray-800 bg-white rounded-xl shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-4 overflow-hidden">
                <!-- Left: Image -->
                <img src="./admin/uploads/<?= htmlspecialchars($item['image']) ?>"
                    alt="<?= htmlspecialchars($item['title']) ?>"
                    class="w-full md:w-32 h-48 md:h-24 object-cover flex-shrink-0">

                <!-- Center: Title & Date -->
                <div class="flex-1 px-4 text-left w-full">
                    <h2 class="text-gray-900 dark:text-gray-100 text-lg font-semibold truncate">
                        <?= htmlspecialchars($item['title']) ?>
                    </h2>
                    <p class="text-gray-900 dark:text-gray-400 text-sm mt-1">
                        <?= htmlspecialchars(date('F j, Y', strtotime($item['created_at']))) ?>
                    </p>
                </div>

                <!-- Right: Button -->
                <div class="lg:px-6 px-2 lg:w-auto w-full lg:mb-0 mb-4 md:text-right text-center md:text-left">
                    <a href="<?= $link ?>"
                        class="flex justify-center md:justify-end items-center border border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-4 py-2 rounded-lg text-sm transition-colors w-full md:w-auto text-center">
                        See Result
                    </a>
                </div>
            </div>

        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-gray-400">No tournaments available.</p>
    <?php endif; ?>
</section>