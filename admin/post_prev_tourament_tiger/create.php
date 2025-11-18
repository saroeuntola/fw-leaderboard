<?php
ob_start();
include "../lib/checkroles.php";
include '../lib/tiger_tourament_lib.php';
protectRoute([1, 3]);
$tournament = new TigerTouramentPost();
// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $desc = $_POST['description'] ?? '';
    $image = $_FILES['image'] ?? null;

    $imagePath = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Server upload directory
        $uploadDir = "../uploads/img/";
        $publicDir = "img/";

        // Ensure folder exists
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        // Clean up filename
        $imageFileName = preg_replace("/[^a-zA-Z0-9._-]/", "", basename($_FILES["image"]["name"]));
        $uniqueName = time() . "_" . $imageFileName;

        // Full path to save file
        $fullPath = $uploadDir . $uniqueName;

        // Move file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $fullPath)) {
            // Save only public path to DB (e.g., /uploads/img/filename.png)
            $imagePath = $publicDir . $uniqueName;
        }
    }

    // Store only the relative path (safe for display)
    $tournament->createTournament($title, $imagePath, $desc);

    echo "<script>alert('Tournament created successfully!');window.location='./';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Tournament</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="/v2/js/tinymce/tinymce.min.js"></script>
</head>

<body class="bg-gray-900 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-3xl bg-white p-8 rounded-xl shadow-lg">
        <h2 class="text-3xl font-bold text-center mb-6 text-indigo-700">Create Tournament</h2>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <!-- Title -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tournament Title</label>
                <input type="text" name="title" required
                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
            </div>

            <!-- Description (TinyMCE) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                <textarea id="editor" name="description"></textarea>
            </div>

            <!-- Image Upload -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Upload Image</label>
                <input type="file" name="image" accept="image/*"
                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
            </div>

            <!-- Submit -->
            <button type="submit"
                class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md text-lg font-semibold hover:bg-indigo-700 transition-all">
                Create Tournament
            </button>
        </form>
    </div>

    <script>
        // TinyMCE setup with image upload handler
        const image_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/v2/admin/api/upload_image');
            xhr.upload.onprogress = (e) => progress(e.loaded / e.total * 100);

            xhr.onload = () => {
                if (xhr.status !== 200) {
                    reject('HTTP Error: ' + xhr.status);
                    return;
                }
                const json = JSON.parse(xhr.responseText);
                if (!json || typeof json.url !== 'string') {
                    reject('Invalid JSON: ' + xhr.responseText);
                    return;
                }
                resolve(json.url);
            };

            xhr.onerror = () => reject('Image upload failed.');
            const formData = new FormData();
            formData.append('image', blobInfo.blob(), blobInfo.filename());
            xhr.send(formData);
        });

        tinymce.init({
            selector: '#editor',
            height: 800,
            plugins: 'image link lists table code',
            toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | image link table code',
            automatic_uploads: true,
            images_upload_handler: image_upload_handler,
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
            image_title: true,
            license_key: 'gpl',
        });
    </script>
</body>

</html>