
<?php
ob_start();
include('../lib/checkroles.php');
protectRoute([1]);
$auth = new Auth();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    $stmt = $auth->db->prepare("TRUNCATE TABLE login_logs");
    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']); 
    exit;
}

$logs = $auth->getLoginLogs(50);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.tailwindcss.com"></script>>
</head>

<body class="bg-gray-900">
    
    <div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-white">Login Logs</h1>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete all logs?');">
                <button type="submit" name="delete_all"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition-colors">
                    Delete All Logs
                </button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2 border">#</th>
                        <th class="px-4 py-2 border">Username</th>
                        <th class="px-4 py-2 border">IP Address</th>
                        <th class="px-4 py-2 border">User Agent</th>
                        <th class="px-4 py-2 border">Status</th>
                        <th class="px-4 py-2 border">Login Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr class="<?= $log['status'] === 'failure' ? 'bg-red-100' : 'bg-green-50' ?>">
                            <td class="px-4 py-2 border"><?= $log['id'] ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($log['username']) ?></td>
                            <td class="px-4 py-2 border"><?= $log['ip_address'] ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($log['user_agent']) ?></td>
                            <td class="px-4 py-2 border font-bold <?= $log['status'] === 'failure' ? 'text-red-600' : 'text-green-600' ?>">
                                <?= ucfirst($log['status']) ?>
                            </td>
                            <td class="px-4 py-2 border"><?= $log['login_time'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>