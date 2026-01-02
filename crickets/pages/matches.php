<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/ApiService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/apiCache.php';
date_default_timezone_set('Asia/Dhaka');

$type = $_GET['type'] ?? 'upcoming';

$cacheDir = $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/cache';

// Fetch matches
if ($type === 'upcoming') {
    $matches = apiCache("$cacheDir/upcoming.json", 3600, fn() => ApiService::getUpComingMatch()['data'] ?? []);
} else {
    $matches = apiCache("$cacheDir/live.json", 30, fn() => ApiService::getLiveMatches()['data'] ?? []);
}

// Filter / sort as in your previous code
$now = new DateTime('now', new DateTimeZone('Asia/Dhaka'));
if ($type === 'upcoming') {
    $matches = array_filter($matches, function ($m) use ($now) {
        $dt = new DateTime($m['dateTimeGMT'], new DateTimeZone('UTC'));
        $dt->setTimezone(new DateTimeZone('Asia/Dhaka'));
        return $dt >= $now;
    });
    usort($matches, fn($a, $b) => strtotime($a['dateTimeGMT']) <=> strtotime($b['dateTimeGMT']));
} else {
    usort($matches, fn($a, $b) => (!empty($b['matchStarted']) && empty($b['matchEnded'])) <=> (!empty($a['matchStarted']) && empty($a['matchEnded'])));
}

// Render match cards
function renderMatchCard($m, $isUpcoming = true)
{
    $matchId = $m['id'];
    $title = htmlspecialchars($m['series'] ?? $m['name'] ?? 'Unknown Series');
    $dtUTC = new DateTime($m['dateTimeGMT'], new DateTimeZone('UTC'));
    $dtUTC->setTimezone(new DateTimeZone('Asia/Dhaka'));
    $matchDate = $dtUTC->format('d M Y');
    $matchTime = $dtUTC->format('g:i A');

    if ($isUpcoming) {
        $team1Img = $m['t1img'] ?? '';
        $team2Img = $m['t2img'] ?? '';
        $team1Name = htmlspecialchars($m['t1'] ?? '');
        $team2Name = htmlspecialchars($m['t2'] ?? '');
    } else {
        $team1 = $m['teamInfo'][0] ?? [];
        $team2 = $m['teamInfo'][1] ?? [];
        $team1Img = $team1['img'] ?? '';
        $team2Img = $team2['img'] ?? '';
        $team1Name = htmlspecialchars($team1['name'] ?? '');
        $team2Name = htmlspecialchars($team2['name'] ?? '');
    }
?>
    <a href="/crickets/match-detail?id=<?= urlencode($matchId) ?>" class="snap-start flex-shrink-0 min-w-[320px] max-w-[320px] bg-white dark:bg-[#252525] rounded-xl shadow hover:shadow-lg transition p-4">
        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-3 flex flex-wrap gap-2">
            <span><?= $matchDate ?></span> •
            <span><?= $matchTime ?> Local</span>
        </div>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <img src="<?= $team1Img ?>" class="w-8 h-8 rounded-full">
                    <span class="font-semibold text-sm text-gray-800 dark:text-gray-200"><?= $team1Name ?></span>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <img src="<?= $team2Img ?>" class="w-8 h-8 rounded-full">
                    <span class="font-semibold text-sm text-gray-800 dark:text-gray-200"><?= $team2Name ?></span>
                </div>
            </div>
        </div>
        <div class="text-sm text-gray-700 dark:text-gray-300 mt-3 line-clamp-2"><?= $title ?></div>
    </a>
<?php
}

foreach ($matches as $m) renderMatchCard($m, $type === 'upcoming');
?>