<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/ApiService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/apiCache.php';

$seriesId = $_GET['series_id'] ?? null;
if (!$seriesId) die('No series info');

$cacheDir = $_SERVER['DOCUMENT_ROOT'] . '/crickets/cache';
$TEN_HOURS = 4 * 60 * 60;
$seriesPointsResponse = apiCache(
    "$cacheDir/seriesPoints_$seriesId.json",
    $TEN_HOURS,
    fn() => ApiService::getSeriesPoints($seriesId)
);

$seriesPoints = $seriesPointsResponse['data'] ?? [];

// calculate points if needed
foreach ($seriesPoints as &$team) {
    $team['points'] = ($team['wins'] ?? 0) * 2 + ($team['ties'] ?? 0);
    $team['nrr'] = $team['nrr'] ?? 0;
}
unset($team);

// sort by points desc, nrr as tiebreaker
usort($seriesPoints, function ($a, $b) {
    if (($b['points'] ?? 0) === ($a['points'] ?? 0)) return ($b['nrr'] ?? 0) <=> ($a['nrr'] ?? 0);
    return ($b['points'] ?? 0) <=> ($a['points'] ?? 0);
});
?>

<div class="bg-white dark:bg-[#1f1f1f] rounded-xl shadow p-4 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 dark:bg-[#2a2a2a]">
            <tr>
                <th class="text-center">#</th>
                <th>Team</th>
                <th class="text-center">M</th>
                <th class="text-center">W</th>
                <th class="text-center">L</th>
                <th class="text-center">T</th>
                <th class="text-center">NR</th>
                <th class="text-center">Points</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($seriesPoints as $i => $team): ?>
                <tr>
                    <td class="text-center font-medium"><?= $i + 1 ?></td>
                    <td class="flex items-center gap-2">
                        <img src="<?= ($team['img'] && $team['img'] !== 'https://h.cricapi.com/img/icon512.png') ? $team['img'] : '/crickets/img/no-club.png' ?>" class="w-6 h-6 rounded-full">
                        <span><?= $team['teamname'] ?></span>
                    </td>
                    <td class="text-center"><?= $team['matches'] ?? 0 ?></td>
                    <td class="text-center"><?= $team['wins'] ?? 0 ?></td>
                    <td class="text-center"><?= $team['loss'] ?? 0 ?></td>
                    <td class="text-center"><?= $team['ties'] ?? 0 ?></td>
                    <td class="text-center"><?= $team['nr'] ?? 0 ?></td>
                    <td class="text-center"><?= $team['points'] ?? 0 ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>