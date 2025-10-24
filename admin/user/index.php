<?php
ob_start();
include('../lib/checkroles.php');
include "../lib/users_lib.php";
protectPathAccess();
$user = new User();
$users = $user->getUsers();
// Handle status toggle
if (isset($_GET['toggle_status_id'])) {
    $id = intval($_GET['toggle_status_id']);
    $user->toggleStatus($id);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<style>
    .admin {
        background-color: brown;
    }

    .user {
        background-color: yellowgreen;
    }

    .manager {
        background-color: skyblue;
    }

    .active {
        background-color: green;
    }

    .inactive {
        background-color: black;
    }
</style>

<body class="flex h-screen bg-gray-900">

    <?php include "../include/sidebar.php" ?>
    <!-- Main Content -->
    <main class="flex-1 ml-64 p-6 transition-all duration-300" id="main-content">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-center">
            <h2 class="text-3xl font-extrabold text-gray-100 mb-4 md:mb-0">Users</h2>
            <a href="create"
                class="bg-yellow-600 text-white font-semibold px-6 py-3 rounded-xl shadow-lg hover:bg-indigo-700 transition duration-300 ease-in-out text-center">
                + Create New User
            </a>
        </div>

        <!-- Search Input -->
        <div class="mb-4">
            <input type="text" id="searchInput" placeholder="Search users..."
                class="w-full md:w-1/3 px-4 py-2 border rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <!-- Table -->
        <div class=" rounded-xl shadow-lg overflow-x-auto">
            <table class="w-full text-sm text-left text-white" id="usersTable">
                <thead class="text-xs text-white uppercase bg-yellow-600">
                    <tr>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Username</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users && count($users) > 0): ?>
                        <?php foreach ($users as $userRow): ?>
                            <tr class="bg-gray-800">
                                <td class="px-6 py-4 font-medium"><?= $userRow['id']; ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($userRow['username']); ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($userRow['email']); ?></td>
                                <td class="px-6 py-4">
                                    <?php
                                    $roleName = $userRow['role_name'] ?? '';
                                    $roleColor = match (strtolower($roleName)) {
                                        'admin' => 'admin',
                                        'manager' => 'manager',
                                        'user' => 'user',
                                        default => 'bg-gray-500',
                                    };
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-white text-sm font-semibold <?= $roleColor; ?>">
                                        <?= htmlspecialchars($roleName); ?>
                                    </span>

                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($userRow['status'] == 1): ?>
                                        <span class="active px-3 py-1 text-white rounded-full text-sm">Active</span>
                                    <?php else: ?>
                                        <span class="inactive px-3 py-1 text-white rounded-full text-sm">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 flex justify-center space-x-2">
                                    <a href="edit?id=<?= $userRow['id']; ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">Edit</a>

                                    <?php if ($userRow['status'] == 1): ?>
                                        <a href="?toggle_status_id=<?= $userRow['id']; ?>" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition inactive ">Deactivate</a>
                                    <?php else: ?>
                                        <a href="?toggle_status_id=<?= $userRow['id']; ?>" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition active">Activate</a>
                                    <?php endif; ?>

                                    <a href="delete?id=<?= $userRow['id']; ?>" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 text-lg">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>



</body>
<script>
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#usersTable tbody tr');

    searchInput.addEventListener('input', () => {
        const query = searchInput.value.toLowerCase();
        tableRows.forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });
</script>

</html>