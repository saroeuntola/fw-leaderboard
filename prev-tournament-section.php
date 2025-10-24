<?php
include_once './admin/lib/lion_tournament_lib.php';
include_once './admin/lib/tiger_tourament_lib.php';

// Fetch latest Lion & Tiger tournament (1 each)
$lionTournament = new TournamentPost();
$lionLatest = $lionTournament->getLatest(10);
// ensure we have an array
if (!is_array($lionLatest)) {
    $lionLatest = [];
}
// add type without using foreach by reference
foreach ($lionLatest as $k => $v) {
    $lionLatest[$k]['type'] = 'lion';
}

$tigerTournament = new TigerTouramentPost();
$tigerLatest = $tigerTournament->getLatest(10);
if (!is_array($tigerLatest)) {
    $tigerLatest = [];
}
foreach ($tigerLatest as $k => $v) {
    $tigerLatest[$k]['type'] = 'tiger';
}
// Merge and sort both tournaments by created_at
// array_merge expects arrays; both are ensured to be arrays above
$allTournaments = array_merge($lionLatest, $tigerLatest);

// Sort by date descending (newest first)
usort($allTournaments, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
// Get only the latest one
$latestTournament = array_slice($allTournaments, 0, 10);
?>
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
                    alt="<?= htmlspecialchars($item['title']) ?>"
                    class="w-full md:w-32 h-48 md:h-24 object-cover rounded-lg flex-shrink-0">

                <!-- Center: Title & Date -->
                <div class="flex-1 text-center md:text-left px-4">
                    <h2 class="dark:text-white text-gray-900 text-lg font-semibold "><?= htmlspecialchars($item['title']) ?></h2>
                    <p class="text-gray-400 text-sm mt-1">
                        <?= htmlspecialchars(date('F-j-Y', strtotime($item['created_at']))) ?>
                    </p>
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