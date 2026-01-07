<?php
require_once 'services/ApiService.php';
require_once 'services/apiCache.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/lib/db.php';

// ---------------- SET TIMEZONE ----------------
date_default_timezone_set('Asia/Dhaka');

// ---------------- CACHE ----------------
$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

// ---------------- INPUT ----------------
$matchId = $_GET['id'] ?? null;
if (!$matchId) die('Match ID missing');

// ---------------- HELPERS ----------------
function esc($v)
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

function toDhakaDate($date, $time = '00:00', $format = 'd M Y • g:i A')
{
    $dt = new DateTime("$date $time", new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Asia/Dhaka'));
    $dt->modify('-1 hour');
    return $dt->format($format);
}

// ---------------- FETCH MATCH DATA ----------------
$cacheTTL = 1 * 60 * 60;
$response = apiCache(
    "$cacheDir/mD_{$matchId}.json",
    $cacheTTL,
    fn() => ApiService::getMatchEvent(['event_key' => $matchId])
);

$match = $response['result'][0] ?? null;
if (!$match) die('Match not found');

// ---------------- TEAM LOGOS ----------------
function getTeamLogo($teamName, $apiLogo = null)
{
    if (!empty($apiLogo)) return $apiLogo;

    $safeName = str_replace(' ', '_', $teamName);
    $files = glob(__DIR__ . "/img/team-logo/*.{png,PNG}", GLOB_BRACE);

    foreach ($files as $f) {
        if (strcasecmp(basename($f), "{$safeName}.png") === 0) {
            return "/crickets/img/team-logo/" . basename($f);
        }
    }
    return "/crickets/img/no-club.png";
}

// ---------------- TEAMS ----------------
$home = $match['event_home_team'] ?? '';
$away = $match['event_away_team'] ?? '';

$homeLogo = getTeamLogo($home, $match['event_home_team_logo'] ?? null);
$awayLogo = getTeamLogo($away, $match['event_away_team_logo'] ?? null);

$homeScore = $match['event_service_home']
    ?: $match['event_home_final_result']
    ?: 'Yet to bat';

$awayScore = $match['event_service_away']
    ?: $match['event_away_final_result']
    ?: 'Yet to bat';

// ---------------- EVENT TIME (UTC → DHAKA → -1H) ----------------
$eventTime = new DateTime(
    ($match['event_date_start'] ?? '') . ' ' . ($match['event_time'] ?? '00:00'),
    new DateTimeZone('UTC')
);

$eventTime->setTimezone(new DateTimeZone('Asia/Dhaka'));
$eventTime->modify('-1 hour');



// ---------------- STATUS INFO (PLACEHOLDER SAFE) ----------------
$statusInfo = $match['event_status_info'] ?? '';

// Match start time (UTC → Dhaka → -1h API fix)
$matchStart = new DateTime(
    ($match['event_date_start'] ?? '') . ' ' . ($match['event_time'] ?? '00:00'),
    new DateTimeZone('UTC')
);
$matchStart->setTimezone(new DateTimeZone('Asia/Dhaka'));
$matchStart->modify('-1 hour');

// Timestamp for JS
$matchStartTs = $matchStart->getTimestamp();

// Determine status
$eventStatus = strtolower($match['event_status'] ?? '');

if ($eventStatus === 'finished' && $match['event_live'] !== '1') {
    $statusInfo = "Match Ended";
} else if ($matchStartTs > time()) {
    // Match hasn't started yet
    $now = new DateTime('now', new DateTimeZone('Asia/Dhaka'));
    $diff = $now->diff($matchStart);

    $hours = ($diff->days * 24) + $diff->h;
    $minutes = $diff->i;

    $statusInfo = "Match starts in {$hours}h {$minutes}m";
} elseif ($eventStatus === 'in progress') {
    // Match started but not finished
    $statusInfo = "Match Started";
}

// ---------------- LIVE STATUS ----------------
$isLive =
    ($match['event_live'] ?? '0') === '1'
    && strtolower($match['event_status'] ?? '') === 'in progress';

$IsEnd =  ($match['event_live'] ?? '0') === '0'
    && strtolower($match['event_status']) === 'finished';
   
// ---------------- WIN PROBABILITY ----------------
function calculateWinProbability($match)
{
    $homeProb = 50;
    $awayProb = 50;

    $isLive = ($match['event_live'] ?? '0') === '1';
    $ended  = strtolower($match['event_status'] ?? '') === 'finished';

    $homeFinal = $match['event_home_final_result'] ?? '';
    $awayFinal = $match['event_away_final_result'] ?? '';

    [$homeRuns] = array_map('intval', explode('/', $homeFinal . '/0'));
    [$awayRuns] = array_map('intval', explode('/', $awayFinal . '/0'));

    if ($ended && $homeRuns && $awayRuns) {
        if ($homeRuns > $awayRuns) {
            $homeProb = min(100, 55 + round(($homeRuns - $awayRuns) / 3));
        } else {
            $homeProb = max(0, 45 - round(($awayRuns - $homeRuns) / 3));
        }
        return ['home' => $homeProb, 'away' => 100 - $homeProb];
    }

    if ($isLive) {
        $homeRR = floatval($match['event_home_rr'] ?? 0);
        $awayRR = floatval($match['event_away_rr'] ?? 0);

        $prob = 50 + (($homeRR - $awayRR) * 5);
        $homeProb = min(90, max(10, round($prob)));

        return ['home' => $homeProb, 'away' => 100 - $homeProb];
    }

    return ['home' => $homeProb, 'away' => $awayProb];
}

$winProb = calculateWinProbability($match);
$homePercent = $winProb['home'];
$awayPercent = $winProb['away'];
?>




<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <title><?= esc($home) ?> vs <?= esc($away) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/src/output.css?v=<?= time() ?>">
</head>

<body class="bg-gray-100 dark:bg-black text-gray-900 dark:text-gray-200">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/navbar.php'; ?>
    <header class="px-4 mt-20">
        <div class="bg-white dark:bg-[#1f1f1f] max-w-6xl mx-auto p-6 rounded-xl shadow-md space-y-4">

            <!-- LIVE / COUNTDOWN -->
            <div class="text-center space-y-2">

                <?php if ($isLive): ?>
                    <div class="inline-block bg-red-600 text-white text-xs px-3 py-1 rounded-full animate-pulse">
                        LIVE
                    </div>
                <?php endif; ?>

                <div class="text-lg font-bold text-black bg-green-500 inline-block px-4 py-2 rounded-lg"

                    id="match-status"
                    data-start="<?= esc($matchStartTs) ?>"
                    data-live="<?= $isLive ? '1' : '0' ?>"
                    data-status="<?= esc(strtolower($match['event_status'] ?? '')) ?>"
                    data-api="<?= esc($match['event_status_info'] ?? '') ?>">
                    <?= esc($statusInfo) ?>


                </div>

            </div>

            <!-- DATE / LEAGUE -->
            <div class="flex justify-center items-center text-sm text-gray-600 dark:text-gray-400 text-center">
                <div class="space-y-1">

                    <div>
                        <?= toDhakaDate(
                            $match['event_date_start'] ?? '',
                            $match['event_time'] ?? '00:00',
                            'M d, Y \a\t g:i A'
                        ) ?>
                    </div>

                    <div class="font-semibold text-yellow-500">
                        <?= esc($match['league_name'] ?? '') ?>
                    </div>
                </div>
            </div>

            <!-- TEAMS -->
            <div class="grid grid-cols-3 gap-4">

                <!-- HOME -->
                <div class="text-center">
                    <img src="<?= esc($homeLogo) ?>" class="w-[100px] mx-auto mb-2 border-2 border-green-500">
                    <div class="font-semibold text-lg"><?= esc($home) ?></div>
                    <div class="text-xl font-bold mt-1"><?= esc($homeScore) ?></div>
                </div>

                <div class="text-center font-bold text-gray-500 dark:text-gray-400 text-xl mt-10">
                    VS
                </div>

                <!-- AWAY -->
                <div class="text-center">
                    <img src="<?= esc($awayLogo) ?>" class="w-[100px] mx-auto mb-2 border-2 border-red-500">
                    <div class="font-semibold text-lg"><?= esc($away) ?></div>
                    <div class="text-xl font-bold mt-1"><?= esc($awayScore) ?></div>
                </div>

            </div>

            <!-- WIN PROBABILITY -->

            <?php if ($IsEnd): ?>
                <div class="mt-4 max-w-6xl mx-auto">
                    <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden flex">
                        <div class="h-full bg-green-500 transition-all duration-500" style="width: <?= $homePercent ?>%;"></div>
                        <div class="h-full bg-red-500 transition-all duration-500" style="width: <?= $awayPercent ?>%;"></div>
                    </div>

                    <div class="flex justify-between text-xs font-semibold mt-1">
                        <span class="text-green-700">
                            <?= esc($match['event_home_team']) ?> (<?= $homePercent ?>%)
                        </span>
                        <span class="text-red-500">
                            <?= esc($match['event_away_team']) ?> (<?= $awayPercent ?>%)
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </header>



    <!-- ================= MATCH DETAILS ================= -->
    <div class="max-w-6xl mx-auto mt-6 space-y-4">

        <!-- ================= TABS ================= -->
        <div class="max-w-6xl mx-auto px-4 mt-6">
            <div class="flex gap-2 border-b border-gray-300 dark:border-gray-700 mb-4 overflow-x-auto">
                <button class="tab px-4 py-2 font-semibold border-b-2" data-tab="overview">Overviews</button>
                <button class="tab px-4 py-2 font-semibold border-b-2" data-tab="scorecard">Scorecard</button>
                <button class="tab px-4 py-2 font-semibold border-b-2" data-tab="lineups">Lineups</button>
                <button class="tab px-4 py-2 font-semibold border-b-2" data-tab="standings">Standings</button>
            </div>

            <div id="overview" class="tab-panel">
                <div class="bg-white dark:bg-[#1f1f1f] rounded shadow p-4">
                    <h2 class="font-bold text-lg mb-2">Match Info</h2>
                    <p><b>Toss:</b> <?= esc($match['event_toss']) ?? 'N/A' ?></p>
                    <p><b>Venue:</b> <?= esc($match['event_stadium']) ?? 'N/A' ?></p>
                    <p><b>Type:</b> <?= esc($match['event_type']) ?? 'N/A' ?></p>
                    <p><b>Man of the Match:</b> <?= esc($match['event_man_of_match']) ?? 'N/A' ?></p>
                    <p><b>League:</b> <?= esc($match['league_name']) ?? 'N/A' ?></p>
                    <p><b>Round:</b> <?= $match['league_round'] ?? 'N/A' ?></p>
                    <p><b>Season:</b> <?= esc($match['league_season'] ?? 'N/A') ?></p>
                </div>

            </div>
            <!-- ================= SCORECARD ================= -->
            <div id="scorecard" class="tab-panel">
                <?php if (!empty($match['scorecard'])): ?>
                    <?php foreach (($match['scorecard'] ?? []) as $inning => $rows): ?>
                        <div class="bg-white dark:bg-[#1f1f1f] rounded shadow mb    -6 p-4">

                            <h2 class="font-bold text-lg mb-3 text-blue-600"><?= esc($inning) ?></h2>

                            <?php
                            // Get unique types in this inning
                            $types = array_unique(array_map(fn($r) => $r['type'] ?? 'Unknown', $rows));
                            ?>

                            <?php foreach ($types as $type): ?>
                                <h3 class="font-semibold text-sm mb-2 text-gray-700 dark:text-gray-300"><?= esc($type) ?></h3>

                                <table class="w-full text-sm text-gray-900 dark:text-gray-200 mb-3">
                                    <thead>
                                        <tr class="border-b border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-400">
                                            <th class="text-left py-1">Player</th>
                                            <?php if ($type === 'Batsman' || $type === 'Wicketkeeper'): ?>
                                                <th>R</th>
                                                <th>B</th>
                                                <th>SR</th>
                                            <?php elseif ($type === 'Bowler'): ?>
                                                <th>O</th>
                                                <th>M</th>
                                                <th>W</th>
                                                <th>ER</th>
                                            <?php else: ?>
                                                <th>Info</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $r): ?>
                                            <?php if (($r['type'] ?? 'Unknown') === $type): ?>
                                                <tr class="border-b border-gray-300 dark:border-gray-700">
                                                    <td class="py-1"><?= esc($r['player'] ?? '-') ?>
                                                        <?php if (!empty($r['status'])): ?>
                                                            <span class="text-xs text-gray-500">(<?= esc($r['status']) ?>)</span>
                                                        <?php endif; ?>
                                                    </td>

                                                    <?php if ($type === 'Batsman' || $type === 'Wicketkeeper'): ?>
                                                        <td class="text-center"><?= esc($r['R'] ?? '-') ?></td>
                                                        <td class="text-center"><?= esc($r['B'] ?? '-') ?></td>
                                                        <td class="text-center"><?= esc($r['SR'] ?? '-') ?></td>
                                                    <?php elseif ($type === 'Bowler'): ?>
                                                        <td class="text-center"><?= esc($r['O'] ?? '-') ?></td>
                                                        <td class="text-center"><?= esc($r['M'] ?? '-') ?></td>
                                                        <td class="text-center"><?= esc($r['W'] ?? '-') ?></td>
                                                        <td class="text-center"><?= esc($r['ER'] ?? '-') ?></td>
                                                    <?php else: ?>
                                                        <td class="text-center">-</td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endforeach; ?>

                            <!-- EXTRAS -->
                            <?php if (!empty($match['extra'][$inning])): ?>
                                <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                                    <b>Extras:</b>
                                    <?php foreach ($match['extra'][$inning] as $ex): ?>
                                        <?= esc($ex['total']) ?> <?= esc($ex['text']) ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- FALL OF WICKETS -->
                            <?php if (!empty($match['wickets'][$inning])): ?>
                                <div class="mt-3">
                                    <b class="text-sm text-gray-600 dark:text-gray-400">Fall of Wickets</b>
                                    <ul class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        <?php foreach ($match['wickets'][$inning] as $w): ?>
                                            <li><?= esc($w['score']) ?> (<?= esc($w['fall']) ?>) – <?= esc($w['batsman']) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-gray-500 dark:text-gray-400 py-6">
                        Scorecard not available.
                    </div>
                <?php endif; ?>
            </div>
            <!-- ================= LINEUPS ================= -->
            <div id="lineups" class="tab-panel hidden">
                <?php if (!empty($match['lineups'])): ?>
                    <div class="grid md:grid-cols-2 gap-4">
                        <?php foreach ($match['lineups'] as $team => $data): ?>
                            <div class="bg-white dark:bg-[#1f1f1f] p-4 rounded shadow">
                                <h3 class="font-bold mb-2 text-blue-600"><?= ucfirst(str_replace('_', ' ', $team)) ?></h3>
                                <ul class="text-sm text-gray-900 dark:text-gray-200">
                                    <?php if (!empty($data['starting_lineups'])): ?>
                                        <?php foreach ($data['starting_lineups'] as $p): ?>
                                            <li><?= esc($p['player']) ?></li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="text-gray-400 italic">No players listed</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-400 italic">No Lineup data right now.</p>
                <?php endif; ?>
            </div>

            <!-- ================= STANDINGS ================= -->
            <div id="standings" class="tab-panel hidden">
                <div id="standings-container" class="bg-white dark:bg-[#1f1f1f] p-4 rounded shadow text-gray-600 dark:text-gray-400">
                    Loading standings…
                </div>
            </div>

        </div>
    </div>

    <!-- ================= JS SCRIPT ================= -->
    <script>
        window.MATCH_INFO = {
            leagueKey: <?= json_decode($match['league_key']) ?>,
        };
    </script>
    <?php
    $jsPath = $_SERVER['DOCUMENT_ROOT'] . '/crickets/js/match-info.js';

    if (file_exists($jsPath)) {
        $js = file_get_contents($jsPath);
        $encoded = base64_encode($js);
        echo '<script src="data:text/javascript;base64,' . $encoded . '" defer></script>';
    }
    ?>
    <script src="/crickets/js/timer.js?v=<?= time() ?>"></script>
</body>

</html>