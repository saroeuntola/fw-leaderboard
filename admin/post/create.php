<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();
include '../lib/checkroles.php';
include '../lib/post_lib.php';
include '../lib/users_lib.php';
include '../lib/category_lib.php';
// include $_SERVER['DOCUMENT_ROOT'] . '/config/baseURL.php';
protectRoute([1, 3]);
$product = new Post();
$category = new Category();
$categories = $category->getCategories();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $gameName = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $categoryId = $_POST['category_id'] ?? '';
    $meta_text = $_POST['meta_text'] ?? '';
    $name_bn = $_POST['name_bn'] ?? '';
    $description_bn = $_POST['description_bn'] ?? '';
    $meta_text_bn = $_POST['meta_text_bn'] ?? '';
    $meta_desc = $_POST['meta_desc'] ?? '';
    $meta_keyword = $_POST['meta_keyword'] ?? '';
    $meta_desc_bn = $_POST['meta_desc_bn'] ?? '';
    $meta_keyword_bn = $_POST['meta_keyword_bn'] ?? '';
    $game_link = $_POST['game_link'] ?? '';

    // Handle Image Upload
    $imagePath = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = "post_image/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $imageFileName = preg_replace("/[^a-zA-Z0-9._-]/", "", basename($_FILES["image"]["name"]));
        $imagePath = $uploadDir . time() . "_" . $imageFileName;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
            $imagePath = "";
        }
    }

    // Validate required fields
    if (empty($gameName) || empty($description) || empty($categoryId)) {
        echo "<p class='text-red-500 text-center'>Error: Title, Description, and Category are required.</p>";
    } else {
        if ($product->createpost($gameName, $imagePath, $description, $game_link, $categoryId, $meta_text, $name_bn, $description_bn, $meta_text_bn, $meta_desc, $meta_keyword, $meta_desc_bn, $meta_keyword_bn)) {
            header("Location: ./");
            exit;
        } else {
            echo "<p class='text-red-500 text-center'>Error: Content could not be created.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Posts</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="/v2/js/tinymce/tinymce.min.js"></script>
    <style>
        .form-section {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body class="bg-gray-800 flex items-center justify-center min-h-screen w-full">
    <div class="w-full max-w-4xl bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-3xl font-bold text-center mb-6 text-indigo-700">Create Post</h2>

        <form action="create" method="POST" enctype="multipart/form-data" class="space-y-5">
            <!-- English Fields -->
            <div class="form-section">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">English Content</h3>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Title* (English)</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Description* (English)</label>
                    <textarea id="editor-en" name="description"></textarea>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Description* (English)</label>
                    <input type="text" name="meta_desc" required class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Keyword* (English)</label>
                    <input type="text" name="meta_keyword" required class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Alt image* (English)</label>
                    <input type="text" name="meta_text" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
            </div>

            <!-- Bengali Fields -->
            <!-- <div class="form-section">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Bengali Content</h3>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Title* (Bengali)</label>
                    <input type="text" name="name_bn" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Description* (Bengali)</label>
                    <textarea id="editor-bn" name="description_bn"></textarea>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Description* (Bengali)</label>
                    <input type="text" name="meta_desc_bn" required class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Keyword* (Bengali)</label>
                    <input type="text" name="meta_keyword_bn" required class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Alt image* (Bengali)</label>
                    <input type="text" name="meta_text_bn" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
            </div> -->

            <!-- Image Upload -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Upload Image</label>
                <input type="file" name="image" accept="image/*" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
            </div>

            <!-- Category -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Category</label>
                <select name="category_id" required class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $categorys): ?>
                        <option value="<?= htmlspecialchars($categorys['id']) ?>"><?= htmlspecialchars($categorys['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md text-lg font-semibold hover:bg-indigo-700 transition-all">
                Post
            </button>
        </form>
    </div>
    <script>
        const example_image_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.withCredentials = true;
            xhr.open('POST', '/v2/admin/api/upload_image');

            xhr.upload.onprogress = (e) => {
                progress(e.loaded / e.total * 100);
                console.log(`Uploading: ${(e.loaded / e.total * 100).toFixed(2)}%`);
            };

            xhr.onload = () => {
                if (xhr.status < 200 || xhr.status >= 300) {
                    reject(`HTTP Error: ${xhr.status}`);
                    return;
                }
                let json;
                try {
                    json = JSON.parse(xhr.responseText);
                } catch (err) {
                    reject('Invalid JSON: ' + xhr.responseText);
                    return;
                }

                if (!json || typeof json.url !== 'string') {
                    reject('Invalid JSON structure: ' + xhr.responseText);
                    return;
                }

                console.log('Upload success:', json.url);
                resolve(json.url); 
            };
            xhr.onerror = () => {
                reject('Image upload failed due to XHR transport error.');
            };

            const formData = new FormData();
            formData.append('image', blobInfo.blob(), blobInfo.filename());
            xhr.send(formData);
        });

        tinymce.init({
            selector: '#editor-en',
            height: 850,
            plugins: 'table image link lists code',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | table | image | code',
            automatic_uploads: true,
            images_upload_handler: example_image_upload_handler,
            images_upload_credentials: true,
            images_reuse_filename: true,
            image_title: true,
            image_advtab: true,
            image_description: true,
            file_picker_types: 'image',
            license_key: 'gpl',
            setup: (editor) => {
                editor.on('init', () => {
                    console.log('TinyMCE initialized:', editor.id);
                });
            }
        });
    </script>
</body>
</html>