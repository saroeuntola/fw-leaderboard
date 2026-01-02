<?php
// live-match.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/ApiService.php';
require_once  $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/apiCache.php';

date_default_timezone_set('Asia/Dhaka');
$now = new DateTime('now', new DateTimeZone('Asia/Dhaka'));
$cacheDir = $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/cache';

// Fetch live matches (cached 30s)
$liveMatches = apiCache(
    "$cacheDir/live.json",
    120,
    function () {
        $resp = ApiService::getLiveMatches();
        return $resp['data'] ?? [];
    }
);

// Sort live matches: LIVE first
usort($liveMatches, fn($a, $b) => (!empty($b['matchStarted']) && empty($b['matchEnded'])) <=> (!empty($a['matchStarted']) && empty($a['matchEnded'])));

// Function to render match card (same as main page)
function renderMatchCard($m)
{
    $matchId   = $m['id'];
    $title     = htmlspecialchars($m['series'] ?? $m['name'] ?? 'Unknown Series');
    $venue     = htmlspecialchars($m['venue'] ?? '');
    $dtUTC     = new DateTime($m['dateTimeGMT'], new DateTimeZone('UTC'));
    $dtUTC->setTimezone(new DateTimeZone('Asia/Dhaka'));
    $matchDate = $dtUTC->format('d M Y');
    $matchTime = $dtUTC->format('g:i A');

    $isLive = !empty($m['matchStarted']) && empty($m['matchEnded']);
    $timeLabel = $isLive ? 'LIVE' : (!empty($m['matchEnded']) ? 'FINAL' : 'Starts ' . $matchDate);

    $team1 = $m['teamInfo'][0] ?? [];
    $team2 = $m['teamInfo'][1] ?? [];
    $team1Img = $team1['img'] ?? '';
    $team2Img = $team2['img'] ?? '';
    $team1Name = htmlspecialchars($team1['name'] ?? '');
    $team2Name = htmlspecialchars($team2['name'] ?? '');

    $score1 = $m['score'][0] ?? null;
    $score2 = $m['score'][1] ?? null;
    $status = htmlspecialchars($m['status'] ?? '');
?>
    <a href="/crickets/match-detail?id=<?= urlencode($matchId) ?>" class="snap-start flex-shrink-0 min-w-[320px] max-w-[320px] bg-white dark:bg-[#252525] rounded-xl shadow hover:shadow-lg transition p-4">
        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-3 flex flex-wrap gap-2">
            <span><?= $matchDate ?></span> •
            <?php if ($isLive): ?>
                <span class="flex items-center gap-1 text-red-600 font-bold">
                    <span class="w-2 h-2 bg-red-600 rounded-full animate-pulse"></span> LIVE
                </span>
            <?php else: ?>
                <span><?= $timeLabel ?></span>
            <?php endif; ?>
            • <span><?= $matchTime ?></span>
        </div>

        <div class="text-xs text-gray-400 mb-3"><?= $venue ?></div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <img src="<?= $team1Img ?>" class="w-8 h-8 rounded-full">
                    <span class="font-semibold text-sm text-gray-800 dark:text-gray-200"><?= $team1Name ?></span>
                </div>
                <?php if ($score1): ?><span class="text-sm font-bold"><?= $score1['r'] ?>/<?= $score1['w'] ?></span><?php endif; ?>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <img src="<?= $team2Img ?>" class="w-8 h-8 rounded-full">
                    <span class="font-semibold text-sm text-gray-800 dark:text-gray-200"><?= $team2Name ?></span>
                </div>
                <?php if ($score2): ?><span class="text-sm font-bold"><?= $score2['r'] ?>/<?= $score2['w'] ?></span><?php endif; ?>
            </div>
        </div>

        <div class="text-sm text-gray-700 dark:text-gray-300 mt-3 line-clamp-2"><?= $title ?></div>
     
    </a>
<?php
}

// Render all live matches
foreach ($liveMatches as $m) renderMatchCard($m);
?>