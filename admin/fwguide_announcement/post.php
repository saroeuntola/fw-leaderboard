<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();
include "../lib/checkroles.php";
include '../lib/fwguide_announcement.php';
protectRoute([1, 3]);

$fwguide = new FwguideAnnouncement();

$id = $_GET['id'] ?? null;
$announcement = null;

// Load existing announcement
if ($id) {
    $announcement = $fwguide->getById($id);
    if (!$announcement) {
        die("Announcement not found!");
    }
}

// Handle form submit (create or update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $desc = $_POST['description'] ?? '';
    $image_pc = $_FILES['image_pc'] ?? null;
    $image_mb = $_FILES['image_mb'] ?? null;
    $title_bn = $_POST['title_bn'] ?? '';
    $desc_bn  = $_POST['description_bn'] ?? '';
    $uploadDir = "../uploads/fwguide/";
    $publicDir = "fwguide/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $imagePCPath = $announcement['image_pc'] ?? '';
    $imageMBPath = $announcement['image_mb'] ?? '';

    // --- Upload new PC image ---
    if ($image_pc && $image_pc['error'] == 0) {
        // Delete old PC image if exists
        if (!empty($announcement['image_pc']) && file_exists("../uploads/" . $announcement['image_pc'])) {
            @unlink("../uploads/" . $announcement['image_pc']);
        }

        $fileName = time() . "_pc_" . preg_replace("/[^a-zA-Z0-9._-]/", "", basename($image_pc['name']));
        $fullPath = $uploadDir . $fileName;
        if (move_uploaded_file($image_pc['tmp_name'], $fullPath)) {
            $imagePCPath = $publicDir . $fileName;
        }
    }

    // --- Upload new Mobile image ---
    if ($image_mb && $image_mb['error'] == 0) {
        // Delete old MB image if exists
        if (!empty($announcement['image_mb']) && file_exists("../uploads/" . $announcement['image_mb'])) {
            @unlink("../uploads/" . $announcement['image_mb']);
        }

        $fileName = time() . "_mb_" . preg_replace("/[^a-zA-Z0-9._-]/", "", basename($image_mb['name']));
        $fullPath = $uploadDir . $fileName;
        if (move_uploaded_file($image_mb['tmp_name'], $fullPath)) {
            $imageMBPath = $publicDir . $fileName;
        }
    }

    if ($id) {
        // Update existing announcement
        $fwguide->update($id, $title, $desc, $imagePCPath, $imageMBPath, $title_bn, $desc_bn);
        echo "<script>alert('Announcement updated successfully!');window.location='./';</script>";
    } else {
        // Create new announcement
        $fwguide->create($title, $desc, $imagePCPath, $imageMBPath, $title_bn, $desc_bn);
        echo "<script>alert('Announcement created successfully!');window.location='./';</script>";
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $id ? "Edit" : "Create" ?> FWGuide Announcement</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="/v2/js/tinymce/tinymce.min.js"></script>
</head>

<body class="bg-gray-900 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-5xl bg-white p-8 rounded-xl shadow-lg">
        <h2 class="text-3xl font-bold text-center mb-6 text-red-700">
            <?= $id ? "✏️ Edit" : "+ Add" ?> FWGuide Announcement
        </h2>
      
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <p>Bangla Content</p>
            <!-- Title -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Announcement Title</label>
                <input
                    type="text"
                    name="title"
                    value="<?= htmlspecialchars($announcement['title'] ?? '') ?>"
                    required
                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-red-400 focus:outline-none" />
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                <textarea id="editor" name="description"><?= htmlspecialchars($announcement['description'] ?? '') ?></textarea>
            </div>

            <p>Bangla Content</p>
            <!-- Bangla Title -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Title (Bangla)</label>
                <input
                    type="text"
                    name="title_bn"
                    value="<?= htmlspecialchars($announcement['title_bn'] ?? '') ?>"
                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-red-400 focus:outline-none" />
            </div>

            <!-- Bangla Description -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Description (Bangla)</label>
                <textarea id="editor_bn" name="description_bn"><?= htmlspecialchars($announcement['description_bn'] ?? '') ?></textarea>
            </div>


            <!-- Images -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- PC -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">PC Image</label>
                    <input type="file" name="image_pc" accept="image/*"
                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-red-400 focus:outline-none" />
                    <?php if (!empty($announcement['image_pc'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($announcement['image_pc']) ?>" alt="PC Preview" class="mt-2 rounded-lg w-40 shadow-md">
                    <?php endif; ?>
                </div>

                <!-- Mobile -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Mobile Image</label>
                    <input type="file" name="image_mb" accept="image/*"
                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-red-400 focus:outline-none" />
                    <?php if (!empty($announcement['image_mb'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($announcement['image_mb']) ?>" alt="Mobile Preview" class="mt-2 rounded-lg w-40 shadow-md">
                    <?php endif; ?>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit"
                class="w-full bg-red-600 text-white py-2 px-4 rounded-md text-lg font-semibold hover:bg-red-700 transition-all">
                <?= $id ? "Update Announcement" : "Create Announcement" ?>
            </button>
        </form>
    </div>

    <script>
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

        tinymce.init({
            selector: '#editor_bn',
            height: 500,
            plugins: 'image link lists table code',
            toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | image link table code',
            automatic_uploads: true,
            images_upload_handler: image_upload_handler,
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
            image_title: true,
            language: 'bn',
            license_key: 'gpl',
        });
    </script>
</body>

</html>