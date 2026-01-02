<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/ApiService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/apiCache.php';

$seriesId = $_GET['series_id'] ?? null;
if (!$seriesId) {
    echo '<div class="text-gray-500 dark:text-gray-400 text-sm">Standings not available.</div>';
    exit;
}
$cacheDir = $_SERVER['DOCUMENT_ROOT'] . '/crickets/cache';
$seriesPoints = apiCache(
    $cacheDir . "/series_{$seriesId}_points.json",
    3600,
    fn() => ApiService::getSeriesPoints($seriesId)
)['data'] ?? [];

if (empty($seriesPoints)) {
    echo '<div class="text-gray-500 dark:text-gray-400 text-sm">Standings not available.</div>';
    exit;
}
?>
<div class="overflow-x-auto bg-[#1f1f1f] rounded-xl shadow p-4">
    <h3 class="text-lg font-bold mb-3 text-white">Standings</h3>
    <table class="w-full text-sm border border-gray-700 rounded-lg overflow-hidden text-white">
        <thead class="bg-[#2a2a2a] text-gray-300">
            <tr>
                <th class="p-3 text-center">#</th>
                <th class="p-3 text-left">Team</th>
                <th class="p-3 text-center">M</th>
                <th class="p-3 text-center text-green-500">W</th>
                <th class="p-3 text-center text-red-500">L</th>
                <th class="p-3 text-center text-gray-400">NRR</th>
                <th class="p-3 text-center text-yellow-500">Pts</th>
                <th class="p-3 text-center">Last 5</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($seriesPoints as $i => $team):
                $teamName = htmlspecialchars($team['teamname'] ?? $team['name'] ?? 'N/A');
                $matches  = htmlspecialchars($team['matches'] ?? $team['played'] ?? '0');
                $wins     = htmlspecialchars($team['wins'] ?? '0');
                $loss     = htmlspecialchars($team['loss'] ?? '0');
                $nr       = htmlspecialchars($team['nr'] ?? '0');
                $nrr      = htmlspecialchars($team['nrr'] ?? '+0.000');
                $pts      = htmlspecialchars($team['pts'] ?? 0);
                $img      = htmlspecialchars($team['img'] ?? '');
                $last5    = $team['last5'] ?? [];
            ?>
                <tr class="border-b border-gray-700 hover:bg-[#2a2a2a] transition">
                    <td class="p-3 text-center"><?= $i + 1 ?></td>
                    <td class="p-3 flex items-center gap-2">
                        <?php if ($img): ?>
                            <img src="<?= $img ?>" alt="<?= $teamName ?>" class="w-6 h-6 rounded-full border border-gray-600">
                        <?php endif; ?>
                        <span class="font-semibold"><?= $teamName ?></span>
                    </td>
                    <td class="p-3 text-center"><?= $matches ?></td>
                    <td class="p-3 text-center font-semibold text-green-500"><?= $wins ?></td>
                    <td class="p-3 text-center font-semibold text-red-500"><?= $loss ?></td>
                    <td class="p-3 text-center font-semibold text-gray-400"><?= $nrr ?></td>
                    <td class="p-3 text-center font-semibold text-yellow-500"><?= $pts ?></td>
                    <td class="p-3 text-center flex justify-center gap-1">
                        <?php for ($j = 0; $j < 5; $j++):
                            $result = $last5[$j] ?? null;
                            if ($result === 'W'): ?>
                                <span class="w-4 h-4 bg-green-500 rounded-full"></span>
                            <?php elseif ($result === 'L'): ?>
                                <span class="w-4 h-4 bg-red-500 rounded-full"></span>
                            <?php elseif ($result === 'NR'): ?>
                                <span class="w-4 h-4 bg-gray-500 rounded-full"></span>
                            <?php else: ?>
                                <span class="w-4 h-4 border border-gray-500 rounded-full"></span>
                        <?php endif;
                        endfor; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>