<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();
include "../lib/checkroles.php";
include '../lib/users_lib.php';
include '../lib/prev_tournament_lib.php';
protectRoute([1, 3]);
$tournament = new TournamentPost;
// Fetch all tournaments
$allTournaments = $tournament->getAllTournaments();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournament List</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
</head>

<body class="bg-gray-900 min-h-screen text-gray-100">

    <?php include "../include/sidebar.php"; ?>

    <main class="flex-1 ml-64 p-6 transition-all duration-300" id="main-content">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-indigo-400">Prev Tournament List</h1>
            <a href="create"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-medium transition">
                + Add Prev Tournament
            </a>
        </div>

        <?php if (!empty($allTournaments)): ?>
            <div class="overflow-x-auto bg-gray-800 rounded-lg p-4 shadow-lg">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead>
                        <tr class="text-left">
                            <th class="px-4 py-2 text-sm font-semibold text-gray-300">ID</th>
                            <th class="px-4 py-2 text-sm font-semibold text-gray-300">Image</th>
                            <th class="px-4 py-2 text-sm font-semibold text-gray-300">Title</th>
                            <th class="px-4 py-2 text-sm font-semibold text-gray-300">Type</th>
                            <th class="px-4 py-2 text-sm font-semibold text-gray-300">Post by</th>
                            <th class="px-4 py-2 text-sm font-semibold text-gray-300">created_at</th>
                            <th class="px-4 py-2 text-sm font-semibold text-gray-300 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php foreach ($allTournaments as $item): ?>
                            <?php
                            // Securely prepare image URL and fallback
                            $imagePath = '../uploads/' . htmlspecialchars($item['image']);
                            $fallback = '/images/no-image.png'; // <-- fallback image
                            $imageUrl = (file_exists($imagePath) && !empty($item['image'])) ? $imagePath : $fallback;
                            ?>
                            <tr class="hover:bg-gray-700 transition">
                                <td class="px-4 py-3 text-sm text-gray-200"><?= htmlspecialchars($item['id']) ?></td>

                                <td class="px-4 py-3">
                                    <img src="<?= $imageUrl ?>"
                                        alt="Tournament Thumbnail"
                                        class="w-16 h-10 object-cover rounded border border-gray-600">
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-300 font-medium"><?= htmlspecialchars($item['title']) ?></td>

                                <td class="px-4 py-3 text-sm text-gray-300 font-medium"><?= htmlspecialchars($item['type']) ?></td>
                                <td class="px-4 py-3 text-sm text-gray-300 font-medium"><?= htmlspecialchars($item['post_by']) ?></td>
                                <td class="px-4 py-3 text-sm text-gray-400">
                                    <?= htmlspecialchars(date('Y-m-d', strtotime($item['created_at']))) ?>
                                </td>

                                <td class="px-4 py-3 text-center space-x-2">
                                    <a href="edit?id=<?= urlencode($item['id']) ?>"
                                        class="inline-block bg-yellow-500 hover:bg-yellow-600 text-black px-3 py-1 rounded font-medium transition">
                                        Edit
                                    </a>

                                    <form method="POST" action="delete" class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this tournament?');">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($item['id']) ?>">
                                        <button type="submit"
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded font-medium transition">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-400 text-center mt-10">No Prev tournaments found.</p>
        <?php endif; ?>
    </main>


</body>

</html>