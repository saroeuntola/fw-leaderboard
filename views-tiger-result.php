<?php
include './admin/lib/db.php';
include './admin/lib/prev_tournament_lib.php';

$tournament = new  TournamentPost();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$data = $tournament->getTournamentById($id);
?>
<!DOCTYPE html>
<html lang="bn-BD" class="bg-gray-900">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title'] ?? 'Tournament Result') ?></title>
    <link rel="stylesheet" href="./src/output.css">
    <link rel="shortcut icon" href="/v2/admin/uploads/<?= htmlspecialchars($data['image']) ?>" type="image/png">
    <script src="./js/jquery-3.7.1.min.js" defer></script>
</head>

<body class="dark:bg-gray-900 dark:text-white bg-gray-200 text-gray-900">
    <?php include 'navbar.php'; ?>
    <div class="container max-w-screen-lg mx-auto px-4 py-10 pt-28">
        <?php if (!empty($data)): ?>

            <?php if (!empty($data['image'])): ?>
                <img src="/v2/admin/uploads/<?= htmlspecialchars($data['image']) ?>"
                    class="w-full rounded-lg shadow-lg mb-6 h-auto" loading="lazy">
            <?php endif; ?>
            <h1 class="lg:text-3xl text-xl font-bold mb-4 text-red-700"><?= htmlspecialchars($data['title']) ?></h1>
            <?php if (!empty($data['created_at'])): ?>
                <p class="text-gray-400 text-sm mb-4"> Published on <?= date('F j, Y', strtotime($data['created_at'])) ?></p>
            <?php endif; ?>

            <?php
            // Parse HTML table inside description
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
                            'uid' => trim($cells->item(0)->nodeValue),
                            'matches' => trim($cells->item(1)->nodeValue),
                            'to' => trim($cells->item(2)->nodeValue),
                            'prize' => trim($cells->item(3)->nodeValue),
                        ];
                    }
                }
            }

            // Remove table for normal text
            if (isset($table)) $table->parentNode->removeChild($table);
            $remainingHTML = $doc->saveHTML($doc->getElementsByTagName('body')->item(0));
            ?>

            <div class="prose prose-invert max-w-none mb-10"><?= $remainingHTML ?></div>

            <?php if (!empty($players)): ?>
                <div class="flex flex-col justify-center items-center gap-6 mb-10">

                    <!-- 1st Place -->
                    <?php if (isset($players[0])): ?>
                        <div class="w-full max-w-sm bg-gradient-to-b from-yellow-400 to-yellow-600 rounded-2xl shadow-2xl p-6">
                            <div class="flex items-center justify-center gap-4">
                                <img src="./images/1st.png" alt="1st" class="w-16 h-16">
                                <div class="">
                                    <h3 class="text-yellow-800 font-extrabold text-2xl mb-1">1st</h3>
                                    <p class="text-white font-bold text-lg"><?= htmlspecialchars($players[0]['uid']) ?></p>
                                    <p class="text-sm text-white mt-1">Matches: <?= htmlspecialchars($players[0]['matches']) ?></p>
                                    <p class="text-sm text-white">T/O: <?= htmlspecialchars($players[0]['to']) ?></p>
                                    <p class="text-white text-sm font-semibold mt-1">Prize: <?= htmlspecialchars($players[0]['prize']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- 2nd Place -->
                    <?php if (isset($players[1])): ?>
                        <div class="w-full max-w-sm  bg-gradient-to-b from-gray-400 to-gray-600 rounded-xl shadow-lg p-5">
                            <div class="flex items-center justify-center gap-4">
                                <img src="./images/2nd.png" alt="2nd" class="w-14">
                                <div class="text-left">
                                    <h3 class="text-gray-300 font-bold text-lg mb-1">2nd</h3>
                                    <p class="text-white font-semibold"><?= htmlspecialchars($players[1]['uid']) ?></p>
                                    <p class="text-sm text-gray-400 mt-1">Matches: <?= htmlspecialchars($players[1]['matches']) ?></p>
                                    <p class="text-sm text-gray-400">T/O: <?= htmlspecialchars($players[1]['to']) ?></p>
                                    <p class="text-white text-sm font-semibold mt-1">Prize: <?= htmlspecialchars($players[1]['prize']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- 3rd Place -->
                    <?php if (isset($players[2])): ?>
                        <div class="w-full max-w-sm bg-gradient-to-b from-yellow-700 to-yellow-900 rounded-xl shadow-lg p-5">
                            <div class="flex items-center justify-center gap-4">
                                <img src="./images/3rd.png" alt="3rd" class="w-14">
                                <div class="text-left">
                                    <h3 class="text-orange-200 font-bold text-lg mb-1">3rd</h3>
                                    <p class="text-white font-semibold"><?= htmlspecialchars($players[2]['uid']) ?></p>
                                    <p class="text-sm text-gray-200 mt-1">Matches: <?= htmlspecialchars($players[2]['matches']) ?></p>
                                    <p class="text-sm text-gray-200">T/O: <?= htmlspecialchars($players[2]['to']) ?></p>
                                    <p class="text-white text-sm font-semibold mt-1">Prize: <?= htmlspecialchars($players[2]['prize']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
                <div class="dark:text-white text-gray-800 bg-white dark:bg-[#252525]
            shadow-[0_0_5px_0_rgba(0,0,0,0.2)] rounded-md  mb-10">
                    <!-- Remaining -->
                    <?php if (count($players) > 3): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-700 rounded-lg overflow-hidden">
                                <thead class="bg-yellow-500 text-gray-900">
                                    <tr>
                                        <th class="py-2">POS.</th>
                                        <th class="py-2">UID</th>
                                        <th class="py-2">Matches</th>
                                        <th class="py-2">T/O</th>
                                        <th class="py-2">Prize</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for ($i = 3; $i < count($players); $i++): ?>
                                        <tr class="text-center border-t border-gray-700">
                                            <td class="py-2 text-gray-800 dark:text-white"><?= $i + 1 ?></td>
                                            <td class="py-2 text-gray-800 dark:text-white"><?= htmlspecialchars($players[$i]['uid']) ?></td>
                                            <td class="py-2 text-gray-800 dark:text-white"><?= htmlspecialchars($players[$i]['matches']) ?></td>
                                            <td class="py-2 text-gray-800 dark:text-white"><?= htmlspecialchars($players[$i]['to']) ?></td>
                                            <td class="py-2 text-gray-800 dark:text-white"><?= htmlspecialchars($players[$i]['prize']) ?></td>
                                        </tr>
                                    <?php endfor; ?>
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
    <?php include 'footer.php'; ?>
    <?php include 'scroll-to-top.php'; ?>
</body>

</html>