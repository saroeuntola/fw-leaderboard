<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/ApiService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/apiCache.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/cors.php';

validateRequest();

$leagueKey = $_GET['league_key'] ?? null;
if (!$leagueKey) die('No league key provided');

$cacheDir = $_SERVER['DOCUMENT_ROOT'] . '/crickets/cache';
$HOURS = 8 * 60 * 60;

$standingsResponse = apiCache(
    "$cacheDir/standings_$leagueKey.json",
    $HOURS,
    fn() => ApiService::getStanding(['league_key' => $leagueKey])
);

$standings = $standingsResponse['result']['total'] ?? [];
?>

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
                <th class="px-3 py-3 text-center font-bold text-yellow-400">Pts</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-500">
            <?php foreach ($standings as $i => $team): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-[#2a2a2a] transition">
                    <td class="px-3 py-3 text-center"><?= htmlspecialchars($team['standing_place'] ?? $i + 1) ?></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
              
                            <span class="font-medium whitespace-nowrap"><?= htmlspecialchars($team['standing_team'] ?? '—') ?></span>
                        </div>
                    </td>
                    <td class="px-3 py-3 text-center"><?= $team['standing_MP'] ?? 0 ?></td>
                    <td class="px-3 py-3 text-center text-green-400"><?= $team['standing_W'] ?? 0 ?></td>
                    <td class="px-3 py-3 text-center text-red-400"><?= $team['standing_L'] ?? 0 ?></td>
                    <td class="px-3 py-3 text-center"><?= $team['standing_T'] ?? 0 ?></td>
                    <td class="px-3 py-3 text-center"><?= $team['standing_NR'] ?? 0 ?></td>
                    <td class="px-3 py-3 text-center font-bold text-yellow-400"><?= $team['standing_Pts'] ?? 0 ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>