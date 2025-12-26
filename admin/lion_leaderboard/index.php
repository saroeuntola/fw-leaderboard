<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();
include "../lib/checkroles.php";       
include "../lib/users_lib.php";
include "../lib/leaderboard_lib.php";
protectRoute([1]);
$leaderboardObj = new Leaderboard();
// -------------------
// Handle CSV Upload
// -------------------
if (isset($_POST['upload'])) {
    $leaderboardObj->truncate();

    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, "r");

    $header = true;
    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($header) {
            $header = false;
            continue;
        }
        $row = array_map('trim', $row);
        if (empty(array_filter($row))) continue;

        $username = $row[0] ?? '';
        $bet_market = $row[1] ?? '0';
        $point    = $row[2] ?? '0';
        $price    = $row[3] ?? '0';
        if ($username === '') continue;

        $leaderboardObj->create($username, $point, $bet_market, $price);
    }
    fclose($handle);
    header("Location: ./");
    exit;
}

// -------------------
// Handle Delete All Leaderboard
// -------------------
if (isset($_POST['delete_all'])) {
    $leaderboardObj->truncate();
    header("Location: ./");
    exit;
}

// -------------------
// Fetch Data + Pagination
// -------------------
$data = $leaderboardObj->all();
$total = count($data);
$perPage = 20;
$totalPages = ceil($total / $perPage);

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

$paginatedData = array_slice($data, $offset, $perPage);

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
        function toBanglaNumber($number)
        {
            $engDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $bangDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

            // Convert only digits, keep commas and dots
            return str_replace($engDigits, $bangDigits, $number);
        }

        ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard Upload & Events</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="flex min-h-screen bg-gray-900 text-white">
    <?php include "../include/sidebar.php" ?>

    <main class="flex-1 ml-64 p-6 transition-all duration-300" id="main-content">
        <div class="flex justify-between mb-4">
            <h1 class="text-2xl font-bold">Lion Leaderboard</h1>
            <div class="flex gap-2">
                <button class="btn btn-error" onclick="if(confirm('Delete all leaderboard data?')) document.getElementById('deleteAllForm').submit()">Delete All</button>
                <button class="btn btn-primary" onclick="document.getElementById('uploadModal').showModal()">Upload CSV</button>
            </div>
        </div>

        <?php if (!empty($data)): ?>
            <h1 class="text-center font-bold text-red-700 mb-4">
                Lion Leaderboard for <?= date('d F Y', strtotime($data[0]['created_at'])) ?> (en) <br>
                জন্য লিডারবোর্ড <?= banglaDate($data[0]['created_at']) ?> (bn)
            </h1>
        <?php else: ?>
            <h1 class="text-center font-bold text-red-700">NaN</h1>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-gray-700 text-gray-200">
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Bet Market</th>
                        <th>T/O</th>
                        <th>Price</th>     
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginatedData as $i => $row): ?>
                        <tr>
                            <td><?= $offset + $i + 1 ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['bet_market']) ?></td>
                            <td><?= htmlspecialchars($row['point']) ?></td>
                            <td><?= toBanglaNumber($row['price']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan=" 5" class="text-center text-gray-500">No data found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center mt-4 gap-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="btn btn-sm">« Prev</a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="?page=<?= $p ?>" class="btn btn-sm <?= $p == $page ? 'btn-primary' : '' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="btn btn-sm">Next »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Upload Modal -->
        <dialog id="uploadModal" class="modal">
            <div class="modal-box bg-gray-800">
                <h3 class="font-bold text-lg mb-4">Upload Leaderboard CSV</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="csv_file" accept=".csv" class="file-input file-input-bordered w-full mb-4 bg-gray-800" required />
                    <div class="modal-action">
                        <button type="submit" name="upload" class="btn btn-primary">Upload</button>
                        <button type="button" class="btn" onclick="document.getElementById('uploadModal').close()">Cancel</button>
                    </div>
                </form>
            </div>
        </dialog> 

        <!-- Delete All Form -->
        <form method="POST" id="deleteAllForm">
            <input type="hidden" name="delete_all" value="1">
        </form>
    </main>
        <script>
            const toggleBtn = document.getElementById('toggleSidebar');
            const sidebar = document.getElementById('sidebar');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('-translate-x-full');
                });
            }
        </script> 
</body>
</html>