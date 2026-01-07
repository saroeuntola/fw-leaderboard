<?php
require_once 'services/ApiService.php';
require_once 'services/apiCache.php';

$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

date_default_timezone_set('Asia/Dhaka');

/* ==================== HELPERS ==================== */
function toDhakaDate($date, $time = '00:00', $format = 'd M Y • g:i A')
{
    $dt = new DateTime("$date $time", new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Asia/Dhaka'));
    $dt->modify('-1 hour'); // subtract 1 hour
    return $dt->format($format);
}

function toDhakaTimestamp($date, $time = '00:00')
{
    $dt = new DateTime("$date $time", new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Asia/Dhaka'));
    $dt->modify('-1 hour'); // subtract 1 hour
    return $dt->getTimestamp();
}

function matchDayLabel($date, $time)
{
    $dt = new DateTime("$date $time", new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Asia/Dhaka'));

    $today = new DateTime('today', new DateTimeZone('Asia/Dhaka'));
    $tomorrow = (clone $today)->modify('+1 day');

    if ($dt->format('Y-m-d') === $today->format('Y-m-d')) return 'Today';
    if ($dt->format('Y-m-d') === $tomorrow->format('Y-m-d')) return 'Tomorrow';

    return $dt->format('d M Y');
}


function getTeamLogo($teamName, $apiLogo = null)
{
    if (!empty($apiLogo)) {
        return $apiLogo; // use API logo if available
    }

    // Replace spaces with underscores
    $safeName = str_replace(' ', '_', $teamName);

    // Local filesystem path
    $localPath = __DIR__ . "/img/team-logo/{$safeName}.png";

    // URL path to use in HTML
    $webPath = "/crickets/img/team-logo/{$safeName}.png";

    // Check if file exists, case-insensitive
    $files = glob(__DIR__ . "/img/team-logo/*.{png,PNG}", GLOB_BRACE);
    foreach ($files as $f) {
        if (strcasecmp(basename($f), "{$safeName}.png") === 0) {
            return "/crickets/img/team-logo/" . basename($f);
        }
    }

    // Default fallback
    return "/crickets/img/no-club.png";
}
/* ==================== TIME RANGES ==================== */
$tz = new DateTimeZone('Asia/Dhaka');
$today = new DateTime('today', $tz);
$sevenDaysAgo = (clone $today)->modify('-7 days');
$sevenDaysLater = (clone $today)->modify('+7 days');

/* ==================== FETCH MATCHES ==================== */
$upcomingResponse = apiCache(
    "$cacheDir/upcm.json",
    360,
    fn() => ApiService::getMatchEvent([
        'date_start' => $sevenDaysAgo->format('Y-m-d'),
        'date_stop'  => $sevenDaysLater->format('Y-m-d'),
    ])
);

$upcomingMatches = [];
$resultMatches   = [];

function safeLeague($league)
{
    if (!isset($league) || trim($league) === '') {
        return 'Other';
    }
    return trim($league);
}
foreach (($upcomingResponse['result'] ?? []) as $e) {
    $status = strtolower($e['event_status'] ?? '');
    $matchDate = $e['event_date_start'] ?? '';
    if (!$matchDate) continue;
    $matchDateObj = new DateTime($matchDate, $tz);
    // Handle placeholder in status info
    $statusInfo = $e['event_status_info'] ?? '';

    if (str_contains($statusInfo, '{{MATCH_START_HOURS}}') || str_contains($statusInfo, '{{MATCH_START_MINS}}')) {
        // Match start in UTC
        $matchStart = new DateTime(
            ($e['event_date_start'] ?? '') . ' ' . ($e['event_time'] ?? '00:00'),
            new DateTimeZone('UTC')
        );

        // Convert to Dhaka
        $matchStart->setTimezone(new DateTimeZone('Asia/Dhaka'));

        // Subtract 1 hour adjustment
        $matchStart->modify('-1 hour');

        $now = new DateTime('now', new DateTimeZone('Asia/Dhaka'));

        // Calculate difference
        $diff = $now->diff($matchStart);

        $hours = $diff->h + ($diff->days * 24);
        $minutes = $diff->i;

        $statusInfo = "Match starts in {$hours}h {$minutes}m";
    }

    $homeLogo = getTeamLogo($e['event_home_team'], $e['event_home_team_logo']);
    $awayLogo = getTeamLogo($e['event_away_team'], $e['event_away_team_logo']);
    $match = [
        'id'        => $e['event_key'],
        'date'      => $matchDate,
        'time'      => $e['event_time'] ?: '00:00',
        'dhaka_time' => toDhakaDate($matchDate, $e['event_time'], 'g:i A'), // for display
        'dhaka_ts'   => toDhakaTimestamp($matchDate, $e['event_time']),     // for sorting
        'home'      => $e['event_home_team'],
        'away'      => $e['event_away_team'],
        'homeLogo'  => getTeamLogo($e['event_home_team'], $e['event_home_team_logo']),
        'awayLogo'  => getTeamLogo($e['event_away_team'], $e['event_away_team_logo']),
        'league'    => safeLeague($e['league_name'] ?? null),
        'status'    => $statusInfo ?: $e['event_status'] ?: 'Match yet to begin',
        'homeScore' => $e['event_home_final_result'] ?? '',
        'awayScore' => $e['event_away_final_result'] ?? '',
        'rrHome'    => $e['event_home_rr'] ?? '',
        'rrAway'    => $e['event_away_rr'] ?? '',
        'stadium'   => $e['event_stadium'] ?? '',
        'event_live' => $e['event_live'] ?? '0',
        'event_status' => $e['event_status'] ?? '',
    ];

    // RESULTS: finished/cancelled last 7 days
    if ($status === 'finished' || $status === 'cancelled' && $e['event_live'] !== "1") {
        if ($matchDateObj >= $sevenDaysAgo && $matchDateObj <= $today) {
            $resultMatches[] = $match; // just append
        }
    }
    // UPCOMING: today or future AND not live yet
    else {
        if ($matchDateObj >= $today && $e['event_live'] !== "1" && $status !== 'finished') {
            $upcomingMatches[] = $match;
        }
    }
}
/* ==================== LIVE MATCHES ==================== */
$livescoreResponse = apiCache(
    "$cacheDir/livescore.json",
    180,
    fn() => ApiService::getLivescores()
);


$liveMatches = [];
foreach (($livescoreResponse['result'] ?? []) as $e) {
    $homeLogo = getTeamLogo($e['event_home_team'], $e['event_home_team_logo']);
    $awayLogo = getTeamLogo($e['event_away_team'], $e['event_away_team_logo']);
    $status = strtolower($e['event_status'] ?? '');
    $match = [
        'id'        => $e['event_key'],
        'date'      => $e['event_date_start'],
        'time'      => $e['event_time'] ?: '00:00',
        'dhaka_time' => toDhakaDate($e['event_date_start'], $e['event_time'], 'g:i A'), // for display
        'dhaka_ts'   => toDhakaTimestamp($e['event_date_start'], $e['event_time']),     // for sorting
        'home'      => $e['event_home_team'],
        'away'      => $e['event_away_team'],
        'homeLogo'  => getTeamLogo($e['event_home_team'], $e['event_home_team_logo']),
        'awayLogo'  => getTeamLogo($e['event_away_team'], $e['event_away_team_logo']),
        'league'    => safeLeague($e['league_name'] ?? null),
        'status'    => $e['event_status_info'] ?: $e['event_status'] ?: 'Match yet to begin',
        'homeScore' => $e['event_home_final_result'] ?? '',
        'awayScore' => $e['event_away_final_result'] ?? '',
        'rrHome'    => $e['event_home_rr'] ?? '',
        'rrAway'    => $e['event_away_rr'] ?? '',
        'stadium'   => $e['event_stadium'] ?? '',
        'event_live' => $e['event_live'],
        'event_status' => $e['event_status'] ?? '',
    ];
    if ($e['event_live'] === '1' && $e['event_status'] !== 'Match yet to begin' && $e['event_status'] !== 'Cancelled' && $e['event_status'] !== 'Finished') $liveMatches[] = $match;
}

/* ==================== SORT MATCHES ==================== */
// Upcoming: ascending
usort($upcomingMatches, fn($a, $b) => strtotime($a['date'] . ' ' . $a['time']) <=> strtotime($b['date'] . ' ' . $b['time']));
// Results: descending (recent first)
usort($resultMatches, fn($a, $b) => strtotime($b['date'] . ' ' . $b['time']) <=> strtotime($a['date'] . ' ' . $a['time']));
// Live: descending (latest match first)
usort($liveMatches, fn($a, $b) => strtotime($b['date'] . ' ' . $b['time']) <=> strtotime($a['date'] . ' ' . $a['time']));
/* ==================== MATCH CARD ==================== */
function matchCard($m, $type)
{
    $url = $type === 'live'
        ? "/crickets/match-livescore?id={$m['id']}"
        : "/crickets/match-info?id={$m['id']}";
?>
    <a href="<?= $url ?>" class="block flex-shrink-0 snap-start min-w-[320px] lg:min-w-[370px]">
        <div class="bg-white dark:bg-[#1f1f1f] rounded-xl shadow p-4 match-card"
            data-league="<?= htmlspecialchars($m['league']) ?>">

            <?php if ($type === 'live'): ?>
                <span class="absolute top-3 right-3 bg-red-600 text-white text-xs px-2 py-1 rounded animate-pulse">LIVE</span>
            <?php endif; ?>

            <div class="text-xs text-gray-400 mb-2">
                <?= matchDayLabel($m['date'], $m['time']) ?> • <?= toDhakaDate($m['date'], $m['time'], 'g:i A') ?>
            </div>
            <div class="text-xs text-green-400 mb-2">
                <?= $m['event_status'] ?>
            </div>

            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <img src="<?= $m['homeLogo'] ?>" class="w-6 h-6">
                    <span class="font-semibold"><?= htmlspecialchars($m['home']) ?></span>
                    <span class="ml-auto font-bold"><?= $m['homeScore'] ?></span>
                </div>

                <div class="flex items-center gap-2">
                    <img src="<?= $m['awayLogo'] ?>" class="w-6 h-6">
                    <span class="font-semibold"><?= htmlspecialchars($m['away']) ?></span>
                    <span class="ml-auto font-bold"><?= $m['awayScore'] ?></span>
                </div>
            </div>

            <div class="text-xs text-red-500 mt-3"><?= htmlspecialchars($m['status']) ?></div>
            <div class="text-[11px] text-yellow-500"><?= htmlspecialchars($m['league']) ?></div>

        </div>
    </a>
<?php } ?>

<div class="max-w-7xl mx-auto">
    <!-- PAGE TITLE -->
    <div class="w-full mb-4">
        <h1 class="inline-block bg-sky-600 text-white px-3 py-1 lg:text-xl text-lg font-bold">
            Cricket Live Scores
        </h1>
        <div class="h-[2px] bg-sky-600"></div>
    </div>

    <!-- TABS -->
    <div class="flex gap-8 border-b border-gray-700 overflow-x-auto no-scrollbar mb-4">
        <button class="tab-btn tab-active" data-tab="upcoming">Upcoming</button>
        <button class="tab-btn" data-tab="live">Live</button>
        <button class="tab-btn" data-tab="results">Results</button>
    </div>
    <!-- ================= UPCOMING ================= -->
    <div id="upcoming" class="tab-panel">
        <?php if (!empty($upcomingMatches)): ?>
            <?php $leagues = array_unique(array_column($upcomingMatches, 'league')); ?>

            <div class="flex gap-3 mb-3 overflow-x-auto no-scrollbar text-sm font-semibold series-scroll snap-x snap-mandatory">
                <button class="series-tab series-active" data-series="all">All</button>
                <?php foreach ($leagues as $lg): ?>
                    <button class="series-tab" data-series="<?= htmlspecialchars($lg) ?? '' ?>">
                        <?= htmlspecialchars($lg) ?? '' ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="relative">
                <button class="scroll-arrow left" onclick="scrollContainer('upcoming-scroll',-1)">&#10094;</button>
                <button class="scroll-arrow right" onclick="scrollContainer('upcoming-scroll',1)">&#10095;</button>

                <div id="upcoming-scroll"
                    class="flex gap-4 overflow-x-auto pb-3 snap-x snap-mandatory no-scrollbar scroll-smooth">
                    <?php foreach ($upcomingMatches as $m): ?>
                        <?php matchCard($m, 'upcoming'); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <p class="text-gray-400">No upcoming matches.</p>
        <?php endif; ?>
    </div>
    <!-- ================= LIVE ================= -->
    <div id="live" class="tab-panel hidden">
        <?php if (!empty($liveMatches)): ?>
            <?php $leagues = array_unique(array_column($liveMatches, 'league')); ?>

            <div class="flex gap-3 mb-3 overflow-x-auto no-scrollbar text-sm font-semibold series-scroll">
                <button class="series-tab series-active" data-series="all">All</button>
                <?php foreach ($leagues as $lg): ?>
                    <button class="series-tab" data-series="<?= htmlspecialchars($lg) ?>">
                        <?= htmlspecialchars($lg) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="relative">
                <button class="scroll-arrow left" onclick="scrollContainer('livescore-scroll',-1)">&#10094;</button>
                <button class="scroll-arrow right" onclick="scrollContainer('livescore-scroll',1)">&#10095;</button>

                <div id="livescore-scroll"
                    class="flex gap-4 overflow-x-auto pb-3 snap-x snap-mandatory no-scrollbar scroll-smooth">
                    <?php foreach ($liveMatches as $m): ?>
                        <?php matchCard($m, 'live'); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <p class="text-gray-400">No live matches.</p>
        <?php endif; ?>
    </div>

    <!-- ================= RESULTS ================= -->
    <div id="results" class="tab-panel hidden">
        <?php if (!empty($resultMatches)): ?>
            <?php $leagues = array_unique(array_column($resultMatches, 'league')); ?>

            <div class="flex gap-3 mb-3 overflow-x-auto no-scrollbar text-sm font-semibold series-scroll">
                <button class="series-tab series-active" data-series="all">All</button>
                <?php foreach ($leagues as $lg): ?>
                    <button class="series-tab" data-series="<?= htmlspecialchars($lg) ?>">
                        <?= htmlspecialchars($lg) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="relative">
                <button class="scroll-arrow left" onclick="scrollContainer('result-scroll',-1)">&#10094;</button>
                <button class="scroll-arrow right" onclick="scrollContainer('result-scroll',1)">&#10095;</button>

                <div id="result-scroll"
                    class="flex gap-4 overflow-x-auto pb-3 snap-x snap-mandatory no-scrollbar scroll-smooth">
                    <?php foreach ($resultMatches as $m): ?>
                        <?php matchCard($m, 'result'); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <p class="text-gray-400">No results available.</p>
        <?php endif; ?>
    </div>
</div>
<script src="/crickets/js/livescore.js?v=<?= time() ?>"></script>