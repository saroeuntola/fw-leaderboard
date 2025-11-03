<?php
session_start();
include('../library/users_lib.php');
include('../library/db.php');
$userLib = new User();
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: login.php");
    exit;
}

$user = $userLib->getUser($userId);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>User Profile</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>

<body class="bg-gray-900 flex items-center justify-center min-h-screen">

    <div class="bg-gray-800 rounded-3xl shadow-xl p-8 w-full max-w-xl">
        <div class="flex flex-col items-center text-center">
            <!-- Profile Image -->
            <?php if (!empty($user['profile'])): ?>
                <img src="./user_image/<?= htmlspecialchars($user['profile']) ?>" alt="Profile Image"
                    class="w-32 h-32 rounded-full object-cover shadow-md border-4 border-blue-200">
            <?php else: ?>
                <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center text-white text-lg">
                    No Image
                </div>
            <?php endif; ?>

            <!-- User Info -->
            <h2 class="mt-4 text-2xl font-bold text-white"><?= htmlspecialchars($user['username']) ?></h2>
        </div>

        <!-- Profile Details -->
        <div class="mt-8 space-y-4">
            <div class="flex justify-between text-white">
                <span class="font-semibold">Username:</span>
                <span><?= htmlspecialchars($user['username']) ?></span>
            </div>
            <div class="flex justify-between text-white">
                <span class="font-semibold">Email:</span>
                <span><?= htmlspecialchars($user['email']) ?></span>
            </div>
      
        </div>

        <!-- Edit Button -->
        <div class="mt-8 text-center">
            <a href="edit_profile.php"
                class="inline-block px-6 py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-full font-semibold shadow">
                Edit Profile
            </a>
        </div>
    </div>

</body>

</html>