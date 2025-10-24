<?php
include '../lib/post_lib.php';
include '../lib/users_lib.php';
include '../lib/checkroles.php';
protectPathAccess();
$product = new Post();
$products = $product->getPost();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Content Management</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-900 text-white">
    <?php include '../include/sidebar.php'; ?>
    <main class="flex-1 ml-64 p-6 transition-all duration-300" id="main-content">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-8">
            <h2 class="text-3xl font-bold text-white mb-4 md:mb-0">📝 Post Content</h2>
            <a href="create.php"
                class="bg-indigo-600 text-white font-semibold px-6 py-3 rounded-lg shadow-lg hover:bg-indigo-700 transition duration-300 ease-in-out">
                + Add Post
            </a>
        </div>

        <!-- Table Container -->
        <div class="bg-gray-800 rounded-xl shadow-xl overflow-x-auto border border-gray-700">
            <table class="w-full text-sm text-left text-gray-300">
                <thead class="bg-gray-700 text-gray-100 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">Image</th>
                        <th class="px-6 py-3">Title</th>
                        <th class="px-6 py-3">Category</th>
                        <th class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products && count($products) > 0): ?>
                        <?php foreach ($products as $item): ?>
                            <tr class="border-b border-gray-700 hover:bg-gray-700/40 transition">
                                <td class="px-6 py-4">
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="Post Image"
                                        class="h-12 w-12 object-cover rounded-md ring-2 ring-gray-600" loading="lazy">
                                </td>
                                <td class="px-6 py-4 font-medium text-white">
                                    <?= htmlspecialchars($item['name']); ?>
                                </td>
                                <td class="px-6 py-4 font-medium text-white">
                                    <?= htmlspecialchars($item['category_name']); ?>
                                </td>
                                <td class="px-4 py-4 flex justify-center space-x-3">
                                    <a href="edit?id=<?= $item['id']; ?>"
                                        class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg text-white font-medium transition">
                                        Edit
                                    </a>
                                    <a href="delete?id=<?= $item['id']; ?>"
                                        class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-white font-medium transition"
                                        onclick="return confirm('Are you sure you want to delete this post?');">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400 text-lg">
                                No posts found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>

</html>