<?php
include_once './admin/lib/lion_tournament_lib.php';
include_once './admin/lib/tiger_tournament_lib.php';

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

// Merge and sort both tournaments by created_at
$allTournaments = array_merge($lionLatest, $tigerLatest);

// Sort by date descending (newest first)
usort($allTournaments, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Get only the latest one
$latestTournament = array_slice($allTournaments, 0, 1);
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
            <div class="dark:bg-gray-800 bg-white rounded-xl shadow-md flex flex-col md:flex-row items-center justify-between gap-4 overflow-hidden">

                <!-- Left: Image -->
                <img src="<?= htmlspecialchars($item['image'] ? '/uploads/' . $item['image'] : './images/img-card.png') ?>"
                    alt="<?= htmlspecialchars($item['title']) ?>"
                    class="w-full md:w-32 h-48 md:h-24 object-cover rounded-lg flex-shrink-0">

                <!-- Center: Title & Date -->
                <div class="flex-1 text-center md:text-left px-4">
                    <h2 class="text-white text-lg font-semibold"><?= htmlspecialchars($item['title']) ?></h2>
                    <p class="text-gray-400 text-sm mt-1">
                        Date: <?= htmlspecialchars(date('F-j-Y', strtotime($item['created_at']))) ?>
                    </p>
                </div>

                <!-- Right: Button -->
                <div class="px-2 lg:w-auto w-full">
                    <a href="<?= $link ?>"
                        class="block bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg text-sm transition-colors w-full md:w-auto text-center">
                        See Result
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-gray-400">No tournaments available.</p>
    <?php endif; ?>
</section>