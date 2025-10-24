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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $sex      = $_POST['sex'];

    if (!empty($_FILES['profile']['name'])) {
        $targetDir = "user_image/";
        $filename = basename($_FILES["profile"]["name"]);
        $targetFilePath = $targetDir . $filename;
        if (move_uploaded_file($_FILES["profile"]["tmp_name"], $targetFilePath)) {
            $filename = $filename;
        } else {
            $filename = $user['profile'];
        }
    } else {
        $filename = $user['profile'];
    }

    $data = [
        'username' => $username,
        'sex'      => $sex,
        'profile'  => $filename
    ];

    $updateSuccess = $userLib->updateUser($userId, $data);

    if ($updateSuccess) {
        header("Location: profile.php");
        exit;
    } else {
        echo "There was an error updating your profile.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>

<body class="bg-gray-900 flex items-center justify-center min-h-screen">

    <div class="bg-gray-800 p-8 rounded-3xl shadow-xl w-full max-w-2xl">
        <h2 class="text-2xl font-bold mb-6 text-center text-white">Edit Profile</h2>

        <form action="edit_profile" method="POST" enctype="multipart/form-data" class="space-y-5">

            <!-- Username -->
            <div>
                <label class="block mb-1 font-medium text-white">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>
            <div>
                <label class="block mb-1 font-medium text-white">Gender</label>
                <div class="flex space-x-6 mt-2">
                    <label class="inline-flex items-center">
                        <input type="radio" name="sex" value="male" <?= $user['sex'] == 'male' ? 'checked' : '' ?> class="form-radio text-blue-600">
                        <span class="ml-2 text-white">Male</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="sex" value="female" <?= $user['sex'] == 'female' ? 'checked' : '' ?> class="form-radio text-blue-600">
                        <span class="ml-2 text-white">Female</span>
                    </label>
                </div>
            </div>

            <!-- Profile Picture -->
            <div>
                <label class="block mb-1 font-medium text-white">Profile Image</label>
                <?php if (!empty($user['profile'])): ?>
                    <img id="previewImage" src="./user_image/<?= htmlspecialchars($user['profile']) ?>" alt="Current Profile"
                        class="w-24 h-24 object-cover rounded-full mb-3 border border-gray-300 shadow-sm">
                <?php else: ?>
                    <img id="previewImage" src="https://via.placeholder.com/100" alt="Profile Preview"
                        class="w-24 h-24 object-cover rounded-full mb-3 border border-gray-300 shadow-sm">
                <?php endif; ?>
                <input type="file" name="profile" onchange="previewProfileImage(event)" class="block w-full text-sm text-white file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>
            <!-- Submit -->
            <div class="text-center pt-4">
                <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 shadow-md">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
    <script>
        function previewProfileImage(event) {
            const image = document.getElementById('previewImage');
            const file = event.target.files[0];

            if (file) {
                image.src = URL.createObjectURL(file);
            }
        }
    </script>
</body>

</html>