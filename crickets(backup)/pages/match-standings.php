<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/ApiService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/apiCache.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/cors.php';
validateRequest();
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
<div class="bg-white dark:bg-[#1f1f1f] rounded-xl shadow">
    <!-- Horizontal scroll ONLY when needed -->
    <div class="overflow-x-auto">
        <table class="min-w-[720px] w-full text-sm border-collapse">
            <thead class="bg-gray-100 dark:bg-[#2a2a2a] sticky top-0 z-10">
                <tr class="text-gray-700 dark:text-gray-300">
                    <th class="px-3 py-3 text-center font-semibold">#</th>
                    <th class="px-4 py-3 text-left font-semibold">Team</th>
                    <th class="px-3 py-3 text-center font-semibold">M</th>
                    <th class="px-3 py-3 text-center font-semibold text-green-400">W</th>
                    <th class="px-3 py-3 text-center font-semibold">L</th>
                    <th class="px-3 py-3 text-center font-semibold">T</th>
                    <th class="px-3 py-3 text-center font-semibold">NR</th>
                    <th class="px-3 py-3 text-center font-semibold text-yellow-400">Pts</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-gray-500">
                <?php foreach ($seriesPoints as $i => $team): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#2a2a2a] transition">
                        <td class="px-3 py-3 text-center font-medium">
                            <?= $i + 1 ?>
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <img
                                    src="<?= ($team['img'] && $team['img'] !== 'https://h.cricapi.com/img/icon512.png')
                                                ? $team['img']
                                                : '/crickets/img/no-club.png' ?>"
                                    class="w-6 h-6 rounded-full flex-shrink-0">
                                <span class="font-medium whitespace-nowrap">
                                    <?= htmlspecialchars($team['teamname']) ?>
                                </span>
                            </div>
                        </td>

                        <td class="px-3 py-3 text-center"><?= $team['matches'] ?? 0 ?></td>
                        <td class="px-3 py-3 text-center text-green-400"><?= $team['wins'] ?? 0 ?></td>
                        <td class="px-3 py-3 text-center text-red-400"><?= $team['loss'] ?? 0 ?></td>
                        <td class="px-3 py-3 text-center"><?= $team['ties'] ?? 0 ?></td>
                        <td class="px-3 py-3 text-center"><?= $team['nr'] ?? 0 ?></td>

                        <td class="px-3 py-3 text-center font-bold text-yellow-400">
                            <?= $team['points'] ?? 0 ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>