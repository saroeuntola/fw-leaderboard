<?php
require_once 'services/ApiService.php';
require_once 'services/apiCache.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/lib/db.php';

$matchId = $_GET['id'] ?? null;
if (!$matchId) die('Match ID missing');

$cacheDir = __DIR__ . '/cache';

/**
 * Quick fetch to detect status
 */
$temp = apiCache(
    "$cacheDir/matchDetail_$matchId.json",
    180,
    fn() => ApiService::getMatchInfo($matchId)
);

$data = $temp['data'] ?? [];
if (!$data) die('No match data');

// ================= MATCH STATUS =================
$matchStarted = !empty($data['matchStarted']);
$matchEnded   = !empty($data['matchEnded']);

if ($matchStarted && !$matchEnded) {
    // Live match → 3 minutes
    $ttl = 180;
} elseif ($matchStarted && $matchEnded) {
    // Finished match → 12 hours
    $ttl = 12 * 60 * 60;
} else {
    // Upcoming match → 1 hour
    $ttl = 60 * 60;
}


$matchResponse = apiCache(
    "$cacheDir/matchInfo_$matchId.json",
    $ttl,
    fn() => ApiService::getMatchInfo($matchId)
);

$data = $matchResponse['data'] ?? [];

$scorecards = $data['scorecard'] ?? [];
$score      = $data['score'] ?? [];

$teamA = $data['teamInfo'][0] ?? [];
$teamB = $data['teamInfo'][1] ?? [];
$seriesId = $data['series_id'] ?? null;

// ================= TEAM ↔ SCORE MAPPING =================
$teamScores = ['A' => null, 'B' => null];

$teamAName = $teamA['name'] ?? '';
$teamBName = $teamB['name'] ?? '';

foreach ($score as $sc) {
    $inningName = $sc['inning'] ?? '';
    if ($teamAName && stripos($inningName, $teamAName) !== false) {
        $teamScores['A'] = $sc;
    } elseif ($teamBName && stripos($inningName, $teamBName) !== false) {
        $teamScores['B'] = $sc;
    }
}

$teamAScore = $teamScores['A'];
$teamBScore = $teamScores['B'];

// ================= DATE =================
$dt = new DateTime($data['dateTimeGMT'], new DateTimeZone('UTC'));
$dt->setTimezone(new DateTimeZone('Asia/Dhaka'));
$matchDate = $dt->format('d M Y');
$matchTime = $dt->format('g:i A');

// ================= WIN PROBABILITY =================
function calcWinProb($data, $teamAScore, $teamBScore)
{
    $teamA_prob = 50;
    $teamB_prob = 50;

    $started = !empty($data['matchStarted']);
    $ended   = !empty($data['matchEnded']);

    // -------- MATCH ENDED --------
    if ($ended && $teamAScore && $teamBScore) {
        if ($teamAScore['r'] > $teamBScore['r']) {
            $margin = $teamAScore['r'] - $teamBScore['r'];
            $teamA_prob = min(75, 55 + round($margin / 3));
            $teamB_prob = 100 - $teamA_prob;
        } else {
            $margin = $teamBScore['r'] - $teamAScore['r'];
            $teamB_prob = min(75, 55 + round($margin / 3));
            $teamA_prob = 100 - $teamB_prob;
        }
        return [$teamA_prob, $teamB_prob];
    }

    // -------- LIVE CHASE --------
    if ($started && $teamAScore && $teamBScore) {

        $aOvers = $teamAScore['o'] ?? 0;
        $bOvers = $teamBScore['o'] ?? 0;

        $teamAChasing = $aOvers < $bOvers;

        if ($teamAChasing) {
            $target = $teamBScore['r'] + 1;
            $runs   = $teamAScore['r'];
            $w      = $teamAScore['w'];
            $overs  = $aOvers;
            $maxOvers = $teamAScore['max_overs'] ?? 20;
        } else {
            $target = $teamAScore['r'] + 1;
            $runs   = $teamBScore['r'];
            $w      = $teamBScore['w'];
            $overs  = $bOvers;
            $maxOvers = $teamBScore['max_overs'] ?? 20;
        }

        $ballsLeft  = max(1, ($maxOvers * 6) - ($overs * 6));
        $runsNeeded = max(0, $target - $runs);
        $rrr = ($runsNeeded / $ballsLeft) * 6;

        $pressure = ($rrr / 10) + ($w / 10);
        $chaseProb = max(10, min(90, round(100 - ($pressure * 40))));

        return $teamAChasing
            ? [$chaseProb, 100 - $chaseProb]
            : [100 - $chaseProb, $chaseProb];
    }

    // -------- FIRST INNINGS --------
    if ($teamAScore && !$teamBScore) {
        $rr = $teamAScore['r'] / max(0.1, $teamAScore['o']);
        $teamA_prob = max(45, min(65, round(40 + ($rr * 2.5))));
        $teamB_prob = 100 - $teamA_prob;
    }

    return [$teamA_prob, $teamB_prob];
}

list($teamA_prob, $teamB_prob) = calcWinProb($data, $teamAScore, $teamBScore);
$isLive = $matchStarted && !$matchEnded;




?>

<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($data['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/src/output.css?v=<?= time() ?>">
</head>

<body class="bg-gray-100 dark:bg-[#121212] text-white">
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/navbar.php';
    ?>
    <main class="px-4 pb-10">
        <!-- ================= MATCH HEADER ================= -->
        <!-- ================= HEADER ================= -->
        <div class="relative max-w-6xl mx-auto bg-white dark:bg-[#1f1f1f] rounded-xl shadow py-5 mt-[90px] mb-6 px-4">

            <button class="mb-4 px-4 py-1 bg-amber-700 rounded-md" onclick="history.back()">Back</button>

            <?php if ($isLive): ?>
                <div class="absolute top-3 right-3 bg-red-600 text-[11px] px-2 py-0.5 rounded-md flex gap-1 animate-pulse">
                LIVE
                </div>
            <?php endif; ?>

            <div class="text-sm text-sky-300 mb-3">
                <?= $matchDate ?> • <?= $matchTime ?>
                <span class="bg-red-500 text-white px-1 rounded"><?= $data['matchType'] ?></span>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">

                <div class="text-center p-4 bg-gray-100 dark:bg-[#2a2a2a] rounded-xl">
                    <img src="<?= $teamA['img'] ?>" class="w-12 h-12 mx-auto mb-1">
                    <div class="<?= $teamA_prob > $teamB_prob ? 'text-red-500' : '' ?> font-semibold"><?= $teamA['name'] ?></div>
                    <div class="text-lg font-bold">
                        <?= $teamAScore ? "{$teamAScore['r']}/{$teamAScore['w']} ({$teamAScore['o']} ov)" : 'Yet to bat' ?>
                    </div>
                </div>

                <div class="text-center p-4 bg-gray-100 dark:bg-[#2a2a2a] rounded-xl">
                    <img src="<?= $teamB['img'] ?>" class="w-12 h-12 mx-auto mb-1">
                    <div class="<?= $teamB_prob > $teamA_prob ? 'text-red-500' : '' ?> font-semibold"><?= $teamB['name'] ?></div>
                    <div class="text-lg font-bold">
                        <?= $teamBScore ? "{$teamBScore['r']}/{$teamBScore['w']} ({$teamBScore['o']} ov)" : 'Yet to bat' ?>
                    </div>
                </div>

            </div>

            <!-- Win Probability -->
            <div>
                <div class="text-sm text-red-600 mb-2 text-center"><?= htmlspecialchars($data['status']) ?></div>
                <div class="flex h-2 rounded-full overflow-hidden">
                    <div class="bg-green-600" style="width:<?= $teamA_prob ?>%"></div>
                    <div class="bg-red-600" style="width:<?= $teamB_prob ?>%"></div>
                </div>
                <div class="flex justify-between text-xs mt-1">
                    <span><?= $teamA['shortname'] ?> <?= $teamA_prob ?>%</span>
                    <span><?= $teamB['shortname'] ?> <?= $teamB_prob ?>%</span>
                </div>
            </div>

        </div>


        <!-- ================= TABS ================= -->
        <div class="max-w-6xl mx-auto">
            <div class="flex border-b border-gray-300 dark:border-gray-700 mb-4 overflow-x-auto">
                <button class="tab-btn px-4 py-2 font-semibold border-b-2 border-transparent flex-shrink-0" data-tab="live">Scorecard</button>
                <button class="tab-btn px-4 py-2 font-semibold border-b-2 border-transparent flex-shrink-0" data-tab="standings">Standings</button>
                <button class="tab-btn px-4 py-2 font-semibold border-b-2 border-transparent flex-shrink-0" data-tab="squad">Squad</button>
                <!-- <button class="tab-btn px-4 py-2 font-semibold border-b-2 border-transparent flex-shrink-0" data-tab="match-points">Match Points</button> -->

            </div>

            <div id="tab-content">
                <!-- SCORECARD -->
                <div id="live" class="tab-panel">
                    <?php foreach ($scorecards as $inning): ?>
                        <div class="bg-[#1e1e1e] rounded-xl mb-6 overflow-hidden" data-inning="<?= htmlspecialchars($inning['inning']) ?>">

                            <!-- INNING HEADER -->
                            <div class="px-4 py-3 text-fuchsia-400 font-semibold text-lg border-b border-gray-700">
                                <?= htmlspecialchars($inning['inning']) ?>
                            </div>

                            <!-- ================= BATSMAN TABLE ================= -->
                            <div class="overflow-x-auto">
                                <table class="min-w-[720px] w-full text-sm text-gray-200 bg-[#1f1f1f]">
                                    <thead>
                                        <tr class="text-gray-100 bg-gray-600">
                                            <th class="px-3 py-2 text-left">Batter</th>
                                            <th class="px-3 py-2 text-left">Dismissal</th>
                                            <th class="px-2 py-2 text-center">R</th>
                                            <th class="px-2 py-2 text-center">B</th>
                                            <th class="px-2 py-2 text-center">4s</th>
                                            <th class="px-2 py-2 text-center">6s</th>
                                            <th class="px-2 py-2 text-center">SR</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inning['batting'] ?? [] as $bat): ?>
                                            <tr class="border-b border-gray-800 hover:bg-[#262626]">
                                                <td class="px-3 py-2 font-semibold text-white">
                                                    <?= htmlspecialchars($bat['batsman']['name']) ?>
                                                </td>
                                                <td class="px-3 py-2 text-gray-400">
                                                    <?= htmlspecialchars($bat['dismissal-text'] ?? 'not out') ?>
                                                </td>
                                                <td class="px-2 py-2 text-center font-bold"><?= $bat['r'] ?></td>
                                                <td class="px-2 py-2 text-center"><?= $bat['b'] ?></td>
                                                <td class="px-2 py-2 text-center"><?= $bat['4s'] ?></td>
                                                <td class="px-2 py-2 text-center"><?= $bat['6s'] ?></td>
                                                <td class="px-2 py-2 text-center text-green-400"><?= $bat['sr'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- ================= EXTRAS + TOTAL ================= -->
                            <div class="px-4 py-3 text-sm text-gray-300 border-t border-gray-800">
                                <?php if (!empty($inning['extras'])): ?>
                                    Extras: <span class="text-white font-semibold"><?= $inning['extras']['total'] ?? 0 ?></span>
                                    <span class="text-gray-400">
                                        (b <?= $inning['extras']['b'] ?? 0 ?>,
                                        lb <?= $inning['extras']['lb'] ?? 0 ?>,
                                        w <?= $inning['extras']['w'] ?? 0 ?>,
                                        nb <?= $inning['extras']['nb'] ?? 0 ?>)
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($inning['total'])): ?>
                                    <div class="mt-1 text-white font-bold">
                                        Total: <?= $inning['total']['r'] ?>/<?= $inning['total']['w'] ?>
                                        <span class="text-gray-400">(<?= $inning['total']['o'] ?> ov)</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- ================= FALL OF WICKETS ================= -->
                            <?php if (!empty($inning['fow'])): ?>
                                <div class="px-4 py-3 text-xs text-gray-400 border-t border-gray-800">
                                    <strong class="text-gray-300">Fall of wickets:</strong>
                                    <?= implode(', ', array_map(
                                        fn($f) => "{$f['batsman']} {$f['score']} ({$f['over']})",
                                        $inning['fow']
                                    )) ?>
                                </div>
                            <?php endif; ?>

                            <!-- ================= BOWLING TABLE ================= -->
                            <div class="overflow-x-auto border-t border-gray-700">
                                <table class="min-w-[720px] w-full text-gray-100 bg-[#1f1f1f]">
                                    <thead>
                                        <tr class="text-gray-100 bg-gray-600">
                                            <th class="px-3 py-2 text-left">Bowler</th>
                                            <th class="px-2 py-2 text-center">O</th>
                                            <th class="px-2 py-2 text-center">M</th>
                                            <th class="px-2 py-2 text-center">R</th>
                                            <th class="px-2 py-2 text-center">W</th>
                                            <th class="px-2 py-2 text-center">NB</th>
                                            <th class="px-2 py-2 text-center">WD</th>
                                            <th class="px-2 py-2 text-center">ECO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inning['bowling'] ?? [] as $bowl): ?>
                                            <tr class="border-b border-gray-800 hover:bg-[#262626]">
                                                <td class="px-3 py-2 font-semibold"><?= htmlspecialchars($bowl['bowler']['name']) ?></td>
                                                <td class="px-2 py-2 text-center"><?= $bowl['o'] ?></td>
                                                <td class="px-2 py-2 text-center"><?= $bowl['m'] ?></td>
                                                <td class="px-2 py-2 text-center"><?= $bowl['r'] ?></td>
                                                <td class="px-2 py-2 text-center font-bold text-red-400"><?= $bowl['w'] ?></td>
                                                <td class="px-2 py-2 text-center"><?= $bowl['nb'] ?></td>
                                                <td class="px-2 py-2 text-center"><?= $bowl['wd'] ?></td>
                                                <td class="px-2 py-2 text-center text-yellow-400"><?= $bowl['eco'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    <?php endforeach; ?>

                    <!-- match info -->

                    <div class="max-w-6xl mx-auto bg-white dark:bg-[#1f1f1f] rounded-xl shadow p-5 mb-6">
                        <h1 class="text-2xl font-bold mb-4 border-b-2 border-gray-500">Match Detail</h1>
                        <h2 class="text-xl font-bold mb-2"><?= htmlspecialchars($data['name']) ?></h2>
                        <div class="text-sm text-sky-300 mb-3">Match Date: <?= $matchDate ?> • <?= $matchTime ?></div>
                        <div class="text-white mb-3">Match Type: <?= htmlspecialchars($data['matchType'] ?? '') ?></div>
                        <div class="text-sm text-green-300 mb-3">Venue: <?= htmlspecialchars($data['venue']) ?></div>
                        <div class="text-sm text-pink-300 mb-3">Toss: <?= htmlspecialchars($data['tossWinner'] ?? 'N/A') ?></div>
                        <div class="text-sm text-red-600 mb-3">Match Won: <?= htmlspecialchars($data['matchWinner'] ?? 'N/A') ?></div>

                    </div>

                </div>

                <!-- EMPTY PANELS FOR LAZY LOAD -->
                <div id="standings" class="tab-panel hidden" data-loaded="false"></div>
                <div id="match-points" class="tab-panel hidden" data-loaded="false"></div>
                <div id="squad" class="tab-panel hidden" data-loaded="false"></div>
            </div>
        </div>
    </main>

    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/footer.php';
    ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/scroll-to-top.php'; ?>
    <script>
        window.MATCH_DETAIL = {
            matchId: <?= json_encode($matchId) ?>,
            seriesId: <?= json_encode($seriesId) ?>
        };
    </script>
    <script src="/crickets/js/match-detail.js?v=<?= time() ?>" defer></script>

</body>

</html>