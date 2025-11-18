<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include "../lib/checkroles.php";
include '../lib/users_lib.php';
include '../lib/fwguide_announcement.php';
protectRoute([1, 3]);

$FwAnm = new FwguideAnnouncement();
$allFwAnms = $FwAnm->getAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FWGuide Announcement List</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
</head>

<body class="bg-gray-900 min-h-screen text-gray-100">

    <?php include "../include/sidebar.php"; ?>

    <main class="flex-1 ml-64 p-6 transition-all duration-300" id="main-content">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-indigo-400">FWGuide Announcements</h1>
            <a href="post"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-medium transition">
                + Add Announcement
            </a>
        </div>

        <?php if (!empty($allFwAnms)): ?>
            <div class="overflow-x-auto bg-gray-800 rounded-lg p-4 shadow-lg">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead>
                        <tr class="text-left">
                            <th class="px-4 py-2 text-sm font-semibold text-gray-300">ID</th>
                            <th class="px-4 py-2 text-sm font-semibold text-gray-300">PC Image</th>
                            <th class="px-4 py-2 text-sm font-semibold text-gray-300">MB Image</th>
                            <th class="px-4 py-2 text-sm font-semibold text-gray-300">Title</th>
                            <th class="px-4 py-2 text-sm font-semibold text-gray-300">Date</th>
                            <th class="px-4 py-2 text-sm font-semibold text-gray-300 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php foreach ($allFwAnms as $item): ?>
                            <?php
                            $imagePC = !empty($item['image_pc']) && file_exists('../uploads/' . $item['image_pc']) ? '../uploads/' . $item['image_pc'] : '/v2/images/no-image.png';
                            $imageMB = !empty($item['image_mb']) && file_exists('../uploads/' . $item['image_mb']) ? '../uploads/' . $item['image_mb'] : '/v2/images/no-image.png';
                            ?>
                            <tr class="hover:bg-gray-700 transition">
                                <td class="px-4 py-3 text-sm text-gray-200"><?= htmlspecialchars($item['id']) ?></td>
                                <td class="px-4 py-3">
                                    <img src="<?= $imagePC ?>" alt="PC" class="w-20 h-12 object-cover rounded border border-gray-600">
                                </td>
                                <td class="px-4 py-3">
                                    <img src="<?= $imageMB ?>" alt="Mobile" class="w-20 h-12 object-cover rounded border border-gray-600">
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-300 font-medium"><?= htmlspecialchars($item['title']) ?></td>
                                <td class="px-4 py-3 text-sm text-gray-400"><?= htmlspecialchars(date('Y-m-d', strtotime($item['created_at']))) ?></td>
                                <td class="px-4 py-3 text-center space-x-2">
                                    <a href="post?id=<?= urlencode($item['id']) ?>"
                                        class="inline-block bg-yellow-500 hover:bg-yellow-600 text-black px-3 py-1 rounded font-medium transition">
                                        Edit
                                    </a>
                                    <form method="POST" action="delete" class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this announcement?');">
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
            <p class="text-gray-400 text-center mt-10">No announcements found.</p>
        <?php endif; ?>
    </main>

</body>

</html>