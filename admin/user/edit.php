<?php
ob_start();
include('../lib/checkroles.php');
protectPathAccess();
include('../lib/users_lib.php');
$userAuth = new Auth();
$role = new User();

$roles = $role->getRoles();

if (!isset($_GET['id'])) {
  echo "User ID is required.";
  exit;
}

$userId = intval($_GET['id']);
$userData = $role->getUserById($userId);
if (!$userData) {
  echo "User not found.";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username         = $_POST['username'];
  $email            = $_POST['email'];
  $currentPassword  = $_POST['current_password'];
  $newPassword      = $_POST['new_password'];
  $sex              = $_POST['sex'];
  $role_id          = $_POST['role'];

  $dataToUpdate = [
    'username' => $username,
    'email'    => $email,
    'sex'      => $sex,
    'role_id'  => $role_id,
  ];

  // Check if user wants to change password
  if (!empty($currentPassword)) {
    if (password_verify($currentPassword, $userData['password'])) {
      // Current password is correct
      if (!empty($newPassword)) {
        $dataToUpdate['password'] = $newPassword;
      }
    } else {
      echo "<script>alert('Incorrect current password. Password not changed.');</script>";
    }
  }

  $role->updateUser($userId, $dataToUpdate);
  header('Location: ./');
  exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Edit User</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-center mb-6">Edit User</h2>

    <!-- Edit Form -->
    <form method="POST" action="">
      <!-- Username -->
      <div class="mb-4">
        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($userData['username']) ?>" required
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
      </div>

      <!-- Email -->
      <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" required
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
      </div>
      <!-- Current Password -->
      <div class="mb-4">
        <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
        <input type="password" id="current_password" name="current_password"
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
      </div>

      <!-- New Password -->
      <div class="mb-4">
        <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
        <input type="password" id="new_password" name="new_password"
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
      </div>

      <!-- Role -->
      <div class="mb-4">
        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
        <select id="role" name="role" required
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
          <option value="">Select Role</option>
          <?php foreach ($roles as $r): ?>
            <option value="<?= $r['id'] ?>" <?= $r['id'] == $userData['role_id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($r['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-center">
        <button type="submit"
          class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
          Update User
        </button>
      </div>
    </form>
  </div>
</body>

</html>