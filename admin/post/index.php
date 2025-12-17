<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();
include '../lib/checkroles.php';
include '../lib/post_lib.php';
include '../lib/users_lib.php';
protectRoute([1, 3]);
$product = new Post();
$products = $product->getPost();
$categories = $product->getCategories();
?>

<!DOCTYPE html>
<html lang="en" class="bg-gray-900">

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
        <!-- Filter/Search -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
            <div class="flex gap-2 items-center w-25">
                <label for="filterCategory" class="text-white font-semibold">Filter:</label>
                <select id="filterCategory" class=" py-1 px-2 rounded-md text-gray-200 w-full md:w-48 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-green-600">
                    <option value="">All</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>

            </div>

            <div class="flex gap-2 items-center">
                <input type="text" id="searchInput" placeholder="Search posts..."
                    class="px-4 py-2 rounded bg-gray-700 text-white">
            </div>
        </div>
        <!-- Table Container -->
        <div class="bg-gray-800 rounded-xl shadow-xl overflow-x-auto border border-gray-700">


            <table class="w-full text-sm text-left text-gray-300">
                <thead class="bg-gray-700 text-gray-100 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">Post No.</th>
                        <th class="px-6 py-3">Image</th>
                        <th class="px-6 py-3">Title</th>
                        <th class="px-6 py-3">Category</th>
                        <th class="px-6 py-3">Post by</th>
                        <th class="px-6 py-3">created_at</th>

                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products && count($products) > 0): ?>
                        <?php foreach ($products as $item): ?>
                            <tr class="border-b border-gray-700 hover:bg-gray-700/40 transition">
                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($item['postNo']); ?>
                                </td>
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

                                <td class="px-6 py-4 font-medium text-white">
                                    <?= htmlspecialchars($item['post_by']); ?>
                                </td>

                                <td class="px-6 py-4 font-medium text-white">
                                    <?= htmlspecialchars($item['created_at']); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <button
                                        onclick="toggleStatus(<?= $item['id'] ?>)"
                                        class="px-3 py-1 rounded text-white text-sm transition-all duration-300 cursor-pointer hover:opacity-70 <?= $item['status'] ? 'bg-green-600' : 'bg-red-600' ?>">
                                        <?= $item['status'] ? 'Published' : 'Dratf' ?>
                                    </button>
                                </td>

                                <td class="px-4 py-4 flex justify-center space-x-3">
                                    <button
                                        class="bg-yellow-700 hover:bg-green-600 px-4 py-2 rounded-lg text-white font-medium transition cursor-pointer"
                                        onclick="openPostNoModal(<?= $item['id'] ?>, <?= $item['postNo'] ?>)">
                                        Edit No.
                                    </button>
                                    <a href="edit?id=<?= $item['id']; ?>"
                                        class="bg-blue-600 hover:bg-sky-700 px-4 py-2 rounded-lg text-white font-medium transition">
                                        Edit
                                    </a>
                                    <a href="delete?id=<?= $item['id']; ?>"
                                        class="bg-red-600 hover:bg-black px-4 py-2 rounded-lg text-white font-medium transition"
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

            <dialog id="postNoModal" class="modal">
                <div class="modal-box bg-sky-900 w-70">
                    <h3 class="font-bold text-lg mb-4">Update Post No.</h3>
                    <form id="postNoForm">
                        <input type="hidden" name="id" id="modalPostId">

                        <div class="flex flex-col gap-2 mb-4">
                            <label for="modalPostNo" class="font-semibold">Number</label>
                            <input type="number" name="postNo" id="modalPostNo" class="input input-bordered bg-gray-500" min="0" oninput="checkModalPostNo()">
                            <small id="modalPostNoError" class="text-red-500 hidden">This number is already used</small>
                        </div>

                        <div class="modal-action justify-start ">
                            <button type="button" class="btn btn-primary" id="modalSaveBtn" onclick="saveModalPostNo()">Save</button>
                            <button type="button" class="btn" onclick="document.getElementById('postNoModal').close()">Cancel</button>
                        </div>
                    </form>
                </div>
            </dialog>

        </div>
    </main>
    <script src="post-status.js"></script>
    <script src="search.js"></script>
</body>

</html>