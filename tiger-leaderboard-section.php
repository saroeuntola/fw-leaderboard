<?php
require_once "./admin/lib/db.php";
include "./admin/lib/tiger_leaderboard_lib.php";
include "./admin/lib/tiger_banner_lib.php";
$bannerObj = new Tiger_banners();
$banners = $bannerObj->gettiger_bannersByStatus();

$leaderboardObj = new TigerLeaderboard();
$data = $leaderboardObj->all();
function banglaDate($date)
{
    $timestamp = strtotime($date);
    $formatted = date('d F Y', $timestamp);

    $engDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $bangDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    $formatted = str_replace($engDigits, $bangDigits, $formatted);

    $monthsEng = [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'
    ];
    $monthsBn = [
        'জানুয়ারি',
        'ফেব্রুয়ারি',
        'মার্চ',
        'এপ্রিল',
        'মে',
        'জুন',
        'জুলাই',
        'আগস্ট',
        'সেপ্টেম্বর',
        'অক্টোবর',
        'নভেম্বর',
        'ডিসেম্বর'
    ];
    $formatted = str_replace($monthsEng, $monthsBn, $formatted);

    return $formatted;
}

$topPlayers = array_slice($data, 0, 3);
$tableData = array_slice($data, 3);

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$firstPageLimit = 17;
$otherPageLimit = 20;

if ($page == 1) {
    $limit = $firstPageLimit;
    $offset = 0;
} else {
    $limit = $otherPageLimit;
    $offset = $firstPageLimit + ($page - 2) * $otherPageLimit;
}

$pagedData = array_slice($tableData, $offset, $limit);

$total_records = count($tableData);
$total_pages = 1 + ceil(($total_records - $firstPageLimit) / $otherPageLimit);


function toBanglaNumber($number)
{
    $engDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $bangDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

    // Convert only digits, keep commas and dots
    return str_replace($engDigits, $bangDigits, $number);
}

?>

<style>
    .text-footer {
        font-size: 11px;
    }

    .th-text {
        font-size: 11px;
    }
</style>
<div class="">
    <?php foreach ($banners as $index => $banner): ?>
        <div class="carousel-item pt-6">
            <?php if (!empty($banner['image'])): ?>
                <a href="<?= htmlspecialchars($banner['link'] ?? '#') ?>" class="w-full">
                    <img src="/admin/<?= htmlspecialchars($banner['image']) ?>" loading="lazy"
                        class="w-full h-auto object-fill rounded-md" />
                </a>
            <?php else: ?>

            <?php endif; ?>
        </div>
        <h1 class="text-center font-bold lg:text-3xl text-lg text-red-600 mb-4 mt-4">
            <?= $banner['title'] ?>
        </h1>
    <?php endforeach; ?>

    <?php if (!empty($data)): ?>
        <p class="text-center font-bold dark:text-white text-gray-900 mb-4">
            প্রকাশিত তারিখ <?= banglaDate($data[0]['created_at']) ?>
        </p>
    <?php else: ?>
        <h1 class="text-center font-bold text-red-700">
            NaN
        </h1>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <!-- Top 3 Podium -->
        <div class="flex flex-col justify-center items-center gap-6 mb-10">

            <!-- 1st Place -->
            <?php if (isset($topPlayers[0])): ?>
                <div class="w-full max-w-sm bg-gradient-to-b from-yellow-400 to-yellow-600 rounded-2xl shadow-2xl p-6">
                    <div class="flex items-center justify-center gap-4">
                        <img src="./images/1st.png" alt="1st" class="w-16 h-16">
                        <div class="">
                            <h3 class="text-yellow-800 font-extrabold text-2xl mb-1">1st</h3>
                            <p class="text-white font-bold text-lg"><?= htmlspecialchars($topPlayers[0]['uid']) ?></p>
                       
                            <p class="text-sm text-white">পয়েন্টস: <?= htmlspecialchars($topPlayers[0]['t_o']) ?></p>
                            <p class="text-white text-sm font-semibold mt-1">প্রাইজ: <?= toBanglaNumber($topPlayers[0]['price']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 2nd Place -->
            <?php if (isset($topPlayers[1])): ?>
                <div class="w-full max-w-sm  bg-gradient-to-b from-gray-400 to-gray-600 rounded-xl shadow-lg p-5">
                    <div class="flex items-center justify-center gap-4">
                        <img src="./images/2nd.png" alt="2nd" class="w-14">
                        <div class="text-left">
                            <h3 class="text-gray-300 font-bold text-lg mb-1">2nd</h3>
                            <p class="text-white font-semibold"><?= htmlspecialchars($topPlayers[1]['uid']) ?></p>
   
                            <p class="text-sm text-gray-400">পয়েন্টস: <?= htmlspecialchars($topPlayers[1]['t_o']) ?></p>
                            <p class="text-white text-sm font-semibold mt-1">প্রাইজ: <?= toBanglaNumber($topPlayers[1]['price']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 3rd Place -->
            <?php if (isset($topPlayers[2])): ?>
                <div class="w-full max-w-sm bg-gradient-to-b from-yellow-700 to-yellow-900 rounded-xl shadow-lg p-5">
                    <div class="flex items-center justify-center gap-4">
                        <img src="./images/3rd.png" alt="3rd" class="w-14">
                        <div class="text-left">
                            <h3 class="text-orange-200 font-bold text-lg mb-1">3rd</h3>
                            <p class="text-white font-semibold"><?= htmlspecialchars($topPlayers[2]['uid']) ?></p>

                            <p class="text-sm text-gray-200">পয়েন্টস:<?= htmlspecialchars($topPlayers[2]['t_o']) ?></p>
                            <p class="text-white text-sm font-semibold mt-1">প্রাইজ: <?= toBanglaNumber($topPlayers[2]['price']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>


        <!-- Remaining Players Table -->
        <div class="overflow-x-auto shadow-md rounded-lg">
            <table class="w-full text-center border-collapse text-sm md:text-base">
                <thead class="bg-yellow-500 text-gray-900 uppercase tracking-wider">
                    <tr>
                        <th class="p-2 th-text">POS.</th>
                        <th class="p-2 th-text">UID</th>
                        <th class="p-2 th-text">পয়েন্টস</th>
                        <th class="p-2 th-text">প্রাইজ</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-[#252525] shadow-[0_0_5px_0_rgba(0,0,0,0.2)] rounded-md">
                    <?php foreach ($pagedData as $index => $player): ?>
                        <?php $rank = $offset + $index + 4; ?>
                        <tr class="text-center border-t border-gray-600 dark:text-white text-gray-900">
                            <td class="p-2 "><?= $rank ?></td>
                            <td class="p-2"><?= htmlspecialchars($player['uid']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($player['t_o']) ?></td>
                            <td class="p-2"><?= toBanglaNumber($player['price']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>


    <div class="flex justify-center mt-4 flex-wrap gap-2 pb-8">

        <?php
        $lastPage = $total_pages;
        $target = "#container-tiger";  // container for Tiger leaderboard
        $file = "/tiger-leaderboard-section";   // Tiger leaderboard file
        ?>

        <!-- First page button -->
        <a href="#" class="pagination-link px-3 py-1 rounded <?= $page == 1 ? 'bg-amber-600 text-white cursor-not-allowed' : 'bg-amber-600 text-white' ?>"
            data-page="1" data-file="<?= $file ?>" data-target="<?= $target ?>">
            <i class="fa-solid fa-angles-left"></i>
        </a>

        <!-- Prev arrow -->
        <a href="#" class="pagination-link px-3 py-1 rounded <?= $page == 1 ? 'bg-amber-600 text-white cursor-not-allowed' : 'bg-amber-600 text-white' ?>"
            data-page="<?= max(1, $page - 1) ?>" data-file="<?= $file ?>" data-target="<?= $target ?>">
            <i class="fa-solid fa-arrow-left"></i>
        </a>

        <!-- Desktop first page -->
        <a href="#" class="pagination-link px-3 py-1 <?= $page == 1 ? 'bg-red-700 text-white' : 'bg-gray-300 text-black hover:bg-red-700 hover:text-white' ?> rounded"
            data-page="1" data-file="<?= $file ?>" data-target="<?= $target ?>">1</a>

        <?php
        // Desktop sliding window
        $startDesktop = max(2, $page - 2);
        $endDesktop = min($lastPage - 1, $page + 2);
        if ($startDesktop > 2) echo '<span class="px-2 hidden md:inline">...</span>';
        for ($i = $startDesktop; $i <= $endDesktop; $i++):
        ?>
            <a href="#" class="pagination-link px-3 py-1 <?= $i == $page ? 'bg-red-700 text-white' : 'bg-gray-300 text-black hover:bg-red-700 hover:text-white' ?> rounded hidden md:inline"
                data-page="<?= $i ?>" data-file="<?= $file ?>" data-target="<?= $target ?>"><?= $i ?></a>
        <?php endfor; ?>

        <!-- Mobile sliding window -->
        <?php
        $mobileLimit = 4;
        $startMobile = max(2, $page - 1);
        $endMobile = min($lastPage - 1, $page + 2);
        if ($endMobile - $startMobile + 1 > $mobileLimit) $endMobile = $startMobile + $mobileLimit - 1;
        if ($startMobile > 2) echo '<span class="px-2 inline md:hidden">...</span>';
        for ($i = $startMobile; $i <= $endMobile; $i++):
        ?>
            <a href="#" class="pagination-link px-3 py-1 <?= $i == $page ? 'bg-red-700 text-white' : 'bg-gray-300 text-black hover:bg-red-700 hover:text-white' ?> rounded inline md:hidden"
                data-page="<?= $i ?>" data-file="<?= $file ?>" data-target="<?= $target ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($endDesktop < $lastPage - 1) echo '<span class="px-2 hidden md:inline">...</span>'; ?>
        <?php if ($endMobile < $lastPage - 1) echo '<span class="px-2 inline md:hidden">...</span>'; ?>

        <!-- Last page -->
        <?php if ($lastPage > 1): ?>
            <a href="#" class="pagination-link px-3 py-1 <?= $page == $lastPage ? 'bg-red-700 text-white' : 'bg-gray-300 text-black hover:bg-red-700 hover:text-white' ?> rounded"
                data-page="<?= $lastPage ?>" data-file="<?= $file ?>" data-target="<?= $target ?>"><?= $lastPage ?></a>
        <?php endif; ?>

        <!-- Next arrow -->
        <a href="#" class="pagination-link px-3 py-1 rounded <?= $page == $lastPage ? 'bg-amber-600 text-white cursor-not-allowed' : 'bg-amber-600 text-white' ?>"
            data-page="<?= min($lastPage, $page + 1) ?>" data-file="<?= $file ?>" data-target="<?= $target ?>">
            <i class="fa-solid fa-arrow-right"></i>
        </a>

        <!-- Last page button -->
        <a href="#" class="pagination-link px-3 py-1 rounded <?= $page == $lastPage ? 'bg-amber-600 text-white cursor-not-allowed' : 'bg-amber-600 text-white' ?>"
            data-page="<?= $lastPage ?>" data-file="<?= $file ?>" data-target="<?= $target ?>">
            <i class="fa-solid fa-angles-right"></i>
        </a>

    </div>
</div>