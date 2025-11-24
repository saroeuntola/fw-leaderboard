<?php
include './admin/lib/db.php';
include './admin/lib/prev_tournament_lib.php';

$tournament = new TournamentPost();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$data = $tournament->getTournamentById($id);
?>
<!DOCTYPE html>
<html lang="bn-BD" class="bg-gray-900">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> <?= htmlspecialchars($data['title'] ?? 'Tournament Result') ?></title>
    <link rel="icon" href="/v2/admin/uploads/<?= htmlspecialchars($data['image']) ?>" type="image/png">
    <link rel="stylesheet" href="./src/output.css">
    <script src="./js/jquery-3.7.1.min.js"></script>
    <style>
        .card-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .card-table th,
        .card-table td {
            border: 1px solid #374151;
            padding: 0.75rem 1rem;
        }

        .card-table th {
            background-color: #1f2937;
            color: #f9fafb;
            text-align: center;
        }

        .card-table tbody tr:nth-child(even) {
            background-color: #111827;
        }

        .card-table tbody tr:hover {
            background-color: #1f2937;
        }

        .card-table td:first-child {
            text-align: center;
            font-weight: bold;
        }

        .card-table td:nth-child(2),
        .card-table td:nth-child(3) {
            text-align: right;
        }
    </style>
</head>

<body class="bg-[#f5f5f5] dark:bg-gray-900 dark:text-white text-gray-900">

    <?php
    include "./navbar.php"
    ?>
    <div class="container max-w-5xl mx-auto px-4 py-10 pt-28">
        <?php if (!empty($data)): ?>
            <!-- Image -->
            <?php if (!empty($data['image'])): ?>
                <img src="./admin/uploads/<?= htmlspecialchars($data['image']) ?>"
                    alt="<?= htmlspecialchars($data['title']) ?>"
                    loading="lazy"
                    class="w-full rounded-lg shadow-lg mb-6 lg:h-[400px] h-[225px]">
            <?php endif; ?>

            <!-- Title -->
            <h1 class="lg:text-3xl text-xl font-bold mb-4 text-red-700"><?= htmlspecialchars($data['title']) ?></h1>

            <!-- Date -->
            <?php if (!empty($data['created_at'])): ?>
                <p class="dark:text-gray-400 text-gray-600 text-sm mb-4">
                    Published on <?= htmlspecialchars(date('F j, Y', strtotime($data['created_at']))) ?>
                </p>
            <?php endif; ?>

            <?php
            // Extract table and text
            $description = $data['description'] ?? '';
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML('<?xml encoding="utf-8" ?>' . $description);
            libxml_clear_errors();

            $tables = $doc->getElementsByTagName('table');
            $players = [];

            if ($tables->length > 0) {
                $table = $tables->item(0);
                $rows = $table->getElementsByTagName('tr');

                foreach ($rows as $i => $row) {
                    $cells = $row->getElementsByTagName('td');
                    if ($cells->length >= 4) {
                        $players[] = [
                            'username'   => trim($cells->item(0)->nodeValue),
                            'bet_market' => trim($cells->item(1)->nodeValue),
                            'points'     => trim($cells->item(2)->nodeValue),
                            'price'      => trim($cells->item(3)->nodeValue),
                        ];
                    }
                }
            }
            $table->parentNode->removeChild($table);
            $remainingHTML = $doc->saveHTML($doc->getElementsByTagName('body')->item(0));
            ?>
            <?php

            $topPlayers = array_slice($players, 0, 3);
            $pagedData = array_slice($players, 3);
            $offset = 0;
            ?>
            <!-- Description -->
            <div class="prose prose-invert max-w-none mb-10">
                <?= $remainingHTML ?>
            </div>
            <!-- Leaderboard -->
            <?php if (!empty($topPlayers)): ?>
                <div class=" dark:text-white">
                    <!-- <h2 class="text-3xl font-bold mb-6 text-center text-yellow-400">🏆 Tournament Leaderboard</h2> -->

                    <!-- Top 3 Section -->
                    <div class="grid grid-cols-3 gap-2 mb-6 items-end text-center">
                        <!-- #2 (Left) -->
                        <?php if (isset($topPlayers[1])): ?>
                            <div class="bg-gradient-to-b from-gray-500 to-gray-700 rounded-xl shadow-xl px-2 py-5">
                                <img src="./images/icon-2.png" alt="2nd" class="w-[90px] h-[90px] mx-auto mb-2">
                                <h3 class="text-gray-300 font-bold text-lg">#2</h3>
                                <p class="text-white font-medium"><?= htmlspecialchars($topPlayers[1]['username']) ?></p>
                                <p class="text-sm text-gray-200">বেট মার্কেট: <?= htmlspecialchars($topPlayers[1]['bet_market']) ?></p>
                                <p class="text-sm text-gray-200">পয়েন্ট: <?= htmlspecialchars($topPlayers[1]['points']) ?></p>
                                <p class="text-gray-200 text-sm ">প্রাইজ: <?= htmlspecialchars($topPlayers[1]['price']) ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- #1 (Center, Bigger) -->
                        <?php if (isset($topPlayers[0])): ?>
                            <div class="bg-gradient-to-b from-yellow-600 to-yellow-800 rounded-xl shadow-xl px-2 py-10">
                                <img src="./images/icon-1.png" alt="1st" class="w-[100px] h-[100px] mx-auto mb-2">
                                <h3 class="text-yellow-400 font-bold text-xl">#1</h3>
                                <p class="text-white font-semibold"><?= htmlspecialchars($topPlayers[0]['username']) ?></p>
                                <p class="text-sm text-gray-200">বেট মার্কেট: <?= htmlspecialchars($topPlayers[0]['bet_market']) ?></p>
                                <p class="text-sm text-gray-200">পয়েন্ট: <?= htmlspecialchars($topPlayers[0]['points']) ?></p>
                                <p class="text-gray-200 text-sm">প্রাইজ: <?= htmlspecialchars($topPlayers[0]['price']) ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- #3 (Right) -->
                        <?php if (isset($topPlayers[2])): ?>
                            <div class="bg-gradient-to-b from-red-700 to-red-900 rounded-xl shadow-xl px-2 py-5">
                                <img src="./images/icon-3.png" alt="3rd" class="w-[90px] h-[90px] mx-auto mb-2">
                                <h3 class="text-orange-400 font-bold text-lg">#3</h3>
                                <p class="text-white font-medium"><?= htmlspecialchars($topPlayers[2]['username']) ?></p>
                                <p class="text-sm text-gray-200">বেট মার্কেট: <?= htmlspecialchars($topPlayers[2]['bet_market']) ?></p>
                                <p class="text-sm text-gray-200">পয়েন্ট: <?= htmlspecialchars($topPlayers[2]['points']) ?></p>
                                <p class="text-gray-200 text-sm ">প্রাইজ: <?= htmlspecialchars($topPlayers[2]['price']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Remaining Players Table -->
                    <?php if (!empty($pagedData)): ?>
                        <div class="overflow-x-auto rounded-lg shadow-md">
                            <table class="w-full text-center border-collapse text-sm md:text-base">
                                <thead>
                                    <tr class="bg-red-700 text-gray-100 tracking-wider">
                                        <th class="py-2">No.</th>
                                        <th class="py-2">Username</th>
                                        <th class="py-2">Bet Market</th>
                                        <th class="py-2">Points</th>
                                        <th class="py-2">Price</th>
                                    </tr>
                                </thead>
                                <tbody class="dark:text-white text-gray-800 bg-white dark:bg-[#252525]
            shadow-[0_0_5px_0_rgba(0,0,0,0.2)]">
                                    <?php foreach ($pagedData as $index => $player): ?>
                                        <?php $rank = $offset + $index + 4; ?>
                                        <tr class="text-gray-900 dark:text-white border-t border-gray-700">
                                            <td class="py-2"><?= $rank ?></td>
                                            <td class="py-2"><?= htmlspecialchars($player['username']) ?></td>
                                            <td class="py-2"><?= htmlspecialchars($player['bet_market']) ?></td>
                                            <td class="py-2"><?= htmlspecialchars($player['points']) ?></td>
                                            <td class="py-2"><?= htmlspecialchars($player['price']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>



        <?php else: ?>
            <div class="text-center mt-20">
                <h2 class="text-xl font-semibold text-red-400">Tournament not found!</h2>
            </div>
        <?php endif; ?>

    </div>
    <?php
    include "footer.php"
    ?>
    <?php include 'scroll-to-top.php'; ?>
</body>

</html>