<?php
include "./admin/lib/db.php";
include "./admin/lib/leaderboard_lib.php";
include "./admin/lib/lion_banner_lib.php";
$leaderboardObj = new Leaderboard();
$data = $leaderboardObj->all();
$bannerObj = new lion_banners();
$banners = $bannerObj->getlion_bannersByStatus();
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

if (
    !isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    header("Location: /");
    exit();
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
<div class="max-w-full rounded-lg">
    <?php foreach ($banners as $index => $banner): ?>
        <div class="carousel-item pt-6">
            <?php if (!empty($banner['image'])): ?>
                <a href="<?= htmlspecialchars($banner['link'] ?? '#') ?>" class="w-full">
                    <img src="/v2/admin/<?= htmlspecialchars($banner['image']) ?>" loading="lazy" alt="<?= $banner['title'] ?>"
                        class="w-full h-auto rounded-md" />
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
            Published on <?= banglaDate($data[0]['created_at'])?>
        </p>
    <?php else: ?>
        <h1 class="text-center font-bold text-red-700">
            NaN
        </h1>
    <?php endif; ?>
    <div class="grid grid-cols-3 gap-2 mb-6 items-end text-center">
        <!-- #2 (Left) -->
        <?php if (isset($topPlayers[1])): ?>
            <div class="bg-gradient-to-b from-gray-500 to-gray-700 rounded-xl shadow-xl px-2 py-5">
                <img src="./images/icon-2.png" alt="2nd" class="w-[90px] h-[90px] mx-auto mb-2">
                <h3 class="text-gray-300 font-bold text-lg">#2</h3>
                <p class="text-white font-medium"><?= htmlspecialchars($topPlayers[1]['username']) ?></p>
                <p class="text-sm text-gray-200">বেট মার্কেট: <?= htmlspecialchars($topPlayers[1]['bet_market']) ?></p>
                <p class="text-sm text-gray-200">পয়েন্ট: <?= htmlspecialchars($topPlayers[1]['point']) ?></p>
                <p class="text-gray-200 text-sm ">প্রাইজ: <?= htmlspecialchars($topPlayers[1]['price']) ?></p>
            </div>
        <?php endif; ?>

        <!-- #1 (Center, Bigger) -->
        <?php if (isset($topPlayers[0])): ?>
            <div class="bg-gradient-to-b from-yellow-500 to-yellow-700 rounded-xl shadow-xl px-2 py-10">
                <img src="./images/icon-1.png" alt="1st" class="w-[100px] h-[100px] mx-auto mb-2">
                <h3 class="text-yellow-400 font-bold text-xl">#1</h3>
                <p class="text-white font-semibold"><?= htmlspecialchars($topPlayers[0]['username']) ?></p>
                <p class="text-sm text-gray-200">বেট মার্কেট: <?= htmlspecialchars($topPlayers[0]['bet_market']) ?></p>
                <p class="text-sm text-gray-200">পয়েন্ট: <?= htmlspecialchars($topPlayers[0]['point']) ?></p>
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
                <p class="text-sm text-gray-200">পয়েন্ট: <?= htmlspecialchars($topPlayers[2]['point']) ?></p>
                <p class="text-gray-200 text-sm ">প্রাইজ: <?= htmlspecialchars($topPlayers[2]['price']) ?></p>
            </div>
        <?php endif; ?>
    </div>
    <div class="overflow-x-auto rounded-lg shadow-md">
        <table class="w-full text-center border-collapse">
            <thead>
                <tr class="bg-red-800 text-gray-200">
                    <th class="p-2 th-text">স্থান</th>
                    <th class="p-2 th-text">খেলোয়াড়ের ব্যবহারকারীর নাম</th>
                    <th class="p-2 th-text">বেট মার্কেট</th>
                    <th class="p-2 th-text">পয়েন্ট </th>
                    <th class="p-2 th-text">প্রাইজ</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-[#252525] dark:text-white text-gray-900 shadow-[0_0_5px_0_rgba(0,0,0,0.2)] ">
                <?php foreach ($pagedData as $index => $player): ?>
                    <?php
                    // Rank number = offset + index + 4 (since rank starts at 4)
                    $rank = $offset + $index + 4;
                    ?>
                    <tr class="border-t border-gray-600">
                        <td class="p-2 text-center"><?= $rank ?></td>
                        <td class="p-2 text-center"><?= htmlspecialchars($player['username']) ?></td>
                        <td class="p-2 text-center"><?= htmlspecialchars($player['bet_market']) ?></td>
                        <td class="p-2 text-center"><?= htmlspecialchars($player['point']) ?></td>
                        <td class="p-2 text-center"><?= htmlspecialchars($player['price']) ?></td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>

    </div>
    <p class="mt-4 mb-2 text-footer dark:text-white text-gray-900">শীর্ষ ৫০০ শর্ত: ন্যূনতম ২০ পয়েন্ট এবং ৫০ বেট মার্কেট</p>

    <div class="flex justify-center mt-4 flex-wrap gap-2 pb-8">

        <?php
        $lastPage = $total_pages;
        $target = "#container-lion";
        $file = $_SERVER['PHP_SELF'];
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