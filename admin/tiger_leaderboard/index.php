<?php
include "../lib/checkroles.php";
include "../lib/users_lib.php";
include "../lib/tiger_leaderboard_lib.php";

protectPathAccess();

$leaderboardObj = new TigerLeaderboard();

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

        $uid = $row[0] ?? '';
        $matches = $row[1] ?? '0';
        $t_o = $row[2] ?? '0';
        $price   = isset($row[3]) ? trim($row[3]) : '0';
        if ($uid === '') continue;

        $leaderboardObj->create($uid, $matches, $t_o, $price);
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

    $monthsEng = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $monthsBn = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];
    $formatted = str_replace($monthsEng, $monthsBn, $formatted);

    return $formatted;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiger Leaderboard</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="flex min-h-screen bg-gray-900 ">
    <?php include "../include/sidebar.php"; ?>

    <main class="flex-1 ml-64 p-6 transition-all duration-300 text-white" id="main-content">
        <!-- HEADER -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-white">🐯 Tiger Leaderboard</h1>
            <div class="flex gap-2">
                <button class="btn btn-error btn-sm"
                    onclick="if(confirm('Delete all leaderboard data?')) document.getElementById('deleteAllForm').submit()">
                    Delete All
                </button>
                <button class="btn btn-primary btn-sm" onclick="document.getElementById('uploadModal').showModal()">
                    Upload CSV
                </button>
            </div>
        </div>

        <!-- DATE DISPLAY -->
        <?php if (!empty($data)): ?>
            <div class="text-center mb-4">
                <p class="text-lg font-semibold text-indigo-400">
                    English Date: <?= date('d F Y', strtotime($data[0]['created_at'])) ?>
                </p>
                <p class="text-sm text-gray-400">
                    বাংলা তারিখ: <?= banglaDate($data[0]['created_at']) ?>
                </p>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-400 mb-4">No leaderboard data found.</p>
        <?php endif; ?>

        <!-- TABLE -->
        <div class="overflow-x-auto bg-gray-800 rounded-xl shadow-md">
            <table class="table w-full">
                <thead class="bg-gray-700 text-gray-200">
                    <tr>
                        <th>POS.</th>
                        <th>uID</th>
                        <th>Matches</th>
                        <th>T/O</th>
                        <th>Prize</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginatedData as $i => $row): ?>
                        <tr class="hover:bg-gray-700">
                            <td><?= $offset + $i + 1 ?></td>
                            <td class="font-semibold text-indigo-400"><?= htmlspecialchars($row['uid']) ?></td>
                            <td><?= htmlspecialchars($row['matches']) ?></td>
                            <td><?= htmlspecialchars($row['t_o']) ?></td>
                            <td class="text-green-400 font-bold"><?= htmlspecialchars($row['price']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-gray-400 py-6">No data found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center mt-6 gap-2">
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

        <!-- UPLOAD MODAL -->
        <dialog id="uploadModal" class="modal">
            <div class="modal-box bg-gray-900 text-gray-100">
                <h3 class="font-bold text-lg mb-4">Upload Leaderboard CSV</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="csv_file" accept=".csv"
                        class="file-input file-input-bordered w-full mb-4 bg-gray-800 text-gray-200" required />
                    <div class="modal-action">
                        <button type="submit" name="upload" class="btn btn-primary">Upload</button>
                        <button type="button" class="btn" onclick="document.getElementById('uploadModal').close()">Cancel</button>
                    </div>
                </form>
            </div>
        </dialog>

        <!-- DELETE FORM -->
        <form method="POST" id="deleteAllForm">
            <input type="hidden" name="delete_all" value="1">
        </form>
    </main>

</body>

</html>