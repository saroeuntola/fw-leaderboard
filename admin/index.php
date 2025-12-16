<?php
ob_start();
include "../admin/lib/checkroles.php";
include "../admin/lib/total_count_lib.php";
include "../admin/lib/users_lib.php";
protectRoute([1, 3]);
$count = new Count();
$userCount = $count->getUserCount();
$postCount = $count->getPostCount();
$eventCount = $count->getUpcomingEventCount();
?>


<!DOCTYPE html>
<html lang="en" >
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" />

</head>

<body class=" bg-gray-900 text-gray-100 min-h-screen flex transition-colors duration-300">
    <?php include "../admin/include/sidebar.php" ?>
    <!-- Main Content -->
    <main class="flex-1 ml-64 p-6 transition-all duration-300" id="main-content">
        <h2 class="text-2xl font-semibold mb-6">Dashboard Overview</h2>

        <div class="grid gap-6 md:grid-cols-3">
            <!-- Users -->
            <div class="bg-gray-800 rounded-2xl shadow-md p-6 flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Total Users</p>
                    <h3 class="text-3xl font-bold"><?= htmlspecialchars($userCount) ?></h3>
                </div>
                <div class="text-blue-500 text-4xl">👤</div>
            </div>

            <!-- Posts -->
            <div class="bg-gray-800 rounded-2xl shadow-md p-6 flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Total Posts</p>
                    <h3 class="text-3xl font-bold"><?= htmlspecialchars($postCount) ?></h3>
                </div>
                <div class="text-green-500 text-4xl">📰</div>
            </div>

            <!-- Events -->
            <div class="bg-gray-800 rounded-2xl shadow-md p-6 flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Upcoming Events</p>
                    <h3 class="text-3xl font-bold"><?= htmlspecialchars($eventCount) ?></h3>
                </div>
                <div class="text-purple-500 text-4xl">📅</div>
            </div>
        </div>
    </main>
</body>

</html>