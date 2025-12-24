<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();
include '../lib/checkroles.php';
include '../lib/post_lib.php';
include '../lib/users_lib.php';
include '../lib/category_lib.php';
protectRoute([1, 3]);
$product = new Post();
$category = new Category();

$currentUser = $_SESSION['username'];

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $productData = $product->getPostById($id, 'en');
    if (!$productData) {
        die("Game not found");
    }
    $productDataBn = $product->getPostById($id, 'bn');
} else {
    die("No game ID provided");
}

$categories = $category->getCategories();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $gameName = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $game_link = $_POST['game_link'] ?? '';
    $categoryId = $_POST['category_id'] ?? '';
    $meta_text = $_POST['meta_text'] ?? '';
    $name_bn = $_POST['name_bn'] ?? '';
    $description_bn = $_POST['description_bn'] ?? '';
    $meta_text_bn = $_POST['meta_text_bn'] ?? '';
    $meta_desc = $_POST['meta_desc'] ?? '';
    $meta_keyword = $_POST['meta_keyword'] ?? '';
    $meta_desc_bn = $_POST['meta_desc_bn'] ?? '';
    $meta_keyword_bn = $_POST['meta_keyword_bn'] ?? '';
    $post_by = $currentUser ?? '';
    $status = $_POST['status'] ?? '';
    $postNo = $_POST['postNo'] ?? '';
    $meta_title = $_POST['meta_title'] ?? '';
    $imagePath   = $productData['image'];
    $imageMbPath = $productData['image_mb'];
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === 0) {
        $uploadDir = "post_image/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $imagePath = $uploadDir . uniqid('pc_', true) . '.' . $ext;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $imagePath = $productData['image'];
        }
    }

    if (!empty($_FILES['image_mb']['name']) && $_FILES['image_mb']['error'] === 0) {
        $uploadDir = "post_image/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($_FILES['image_mb']['name'], PATHINFO_EXTENSION));
        $imageMbPath = $uploadDir . uniqid('mb_', true) . '.' . $ext;

        if (!move_uploaded_file($_FILES['image_mb']['tmp_name'], $imageMbPath)) {
            $imageMbPath = $productData['image_mb'];
        }
    }


    // Validate required fields
    if (empty($gameName) || empty($description) || empty($categoryId)) {
        echo "<p class='text-red-500 text-center'>Error: Title, Description, and Category are required.</p>";
    } else {
        if ($product->updatePost($id, $gameName, $imagePath, $imageMbPath, $description, $game_link, $categoryId, $meta_text, $name_bn, $description_bn, $meta_text_bn, $meta_desc, $meta_keyword, $meta_desc_bn, $meta_keyword_bn, $post_by, $status, $postNo, $meta_title)) {
            header("Location: index.php");
            exit;
        } else {
            echo "<p class='text-red-500 text-center'>Error: Product could not be updated.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <link href="/dist/output.css" rel="stylesheet">


    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="/js/tinymce/tinymce.min.js"></script>
    <style>
        .ql-editor {
            min-height: 150px;
        }

        .form-section {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body class="bg-gray-800 flex items-center justify-center w-full">
    <div class="w-full max-w-4xl bg-white p-8 rounded-lg shadow-lg">
        <div class="flex justify-end">
            <button onclick="location.href='./'" class=" bg-red-600 text-white py-2 px-4 rounded-md text-lg font-semibold hover:bg-gray-900 transition-all duration-300 cursor-pointer justify-end">
                Close
            </button>
        </div>
        <h2 class="text-3xl font-bold text-center mb-6 text-indigo-700">Edit Post</h2>

        <form action="edit?id=<?= htmlspecialchars($productData['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-5" onsubmit="syncTinyMCEContent()">
            <!-- English Fields -->
            <div class="form-section">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Edit Contents</h3>
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Title*</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($productData['name']) ?>" required
                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div class="mt-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description*</label>
                    <textarea id="editor-en" name="description"><?= htmlspecialchars($productData['description']) ?></textarea>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Title*</label>
                    <input type="text" id="meta_title" name="meta_title"
                        value="<?= htmlspecialchars($productData['meta_title']) ?>"
                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Description*</label>
                    <input type="text" id="meta_desc" name="meta_desc"
                        value="<?= htmlspecialchars($productData['meta_desc']) ?>"
                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">

                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Keyword*</label>
                    <input type="text" name="meta_keyword"
                        value="<?= htmlspecialchars($productData['meta_keyword']) ?>"
                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                        placeholder="Separate keywords with commas">
                </div>



                <div class="mt-4">
                    <label for="meta_text" class="block text-sm font-medium text-gray-700">Alt image*</label>
                    <input type="text" name="meta_text" value="<?= htmlspecialchars($productData['meta_text']) ?>"
                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>

                <div class="mt-4" hidden>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">No*</label>
                    <input type="text" name="postNo" value="<?= htmlspecialchars($productData['postNo']) ?>" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div class="mt-4" hidden>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">status</label>
                    <input type="text" name="status" value="<?= htmlspecialchars($productData['status']) ?>" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
            </div>

            <!-- Images -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- PC Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Image PC (1024px x 400px)
                    </label>
                    <img
                        id="preview_pc"
                        src="<?= !empty($productData['image']) ? htmlspecialchars($productData['image']) : '' ?>"
                        class="h-50 w-full rounded-md mb-2 border <?= empty($productData['image']) ? 'hidden' : '' ?>">

                    <p id="no_pc" class="text-gray-500 mb-2 <?= !empty($productData['image']) ? 'hidden' : '' ?>">
                        No image available
                    </p>

                    <input
                        type="file"
                        name="image"
                        id="image_pc"
                        accept="image/*"
                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <!-- Mobile Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Image Mb (300px x 210px)
                    </label>

                    <img
                        id="preview_mb"
                        src="<?= !empty($productData['image_mb']) ? htmlspecialchars($productData['image_mb']) : '' ?>"
                        class="h-50 w-full rounded-md mb-2 border <?= empty($productData['image_mb']) ? 'hidden' : '' ?>">

                    <p id="no_mb" class="text-gray-500 mb-2 <?= !empty($productData['image_mb']) ? 'hidden' : '' ?>">
                        No image available
                    </p>

                    <input
                        type="file"
                        name="image_mb"
                        id="image_mb"
                        accept="image/*"
                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>


            </div>


            <!-- Category -->
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category_id" required class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>" <?= ($cat['id'] == $productData['category_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Link(Optional)</label>
                <input type="text" name="game_link" value="<?= htmlspecialchars($productData['game_link'] ?? '') ?>" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
            </div>
            <!-- Submit Button -->
            <div class="flex gap-4 pt-5">
                <button onclick="location.href='./'" class=" bg-red-600 text-white py-2 px-4 rounded-md text-lg font-semibold hover:bg-gray-900 transition-all duration-300 cursor-pointer">
                    Back
                </button>
                <button type="submit" class=" bg-green-600 text-white py-2 px-4 rounded-md text-lg font-semibold hover:bg-indigo-700 transition-all duration-300 cursor-pointer">
                    Update
                </button>
            </div>
        </form>
    </div>
    <script>
        function previewImage(input, previewId, emptyTextId) {
            const file = input.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                alert('Please select an image file');
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById(previewId);
                const emptyText = document.getElementById(emptyTextId);

                preview.src = e.target.result;
                preview.classList.remove('hidden');
                emptyText.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }

        // PC preview
        document.getElementById('image_pc').addEventListener('change', function() {
            previewImage(this, 'preview_pc', 'no_pc');
        });

        // Mobile preview
        document.getElementById('image_mb').addEventListener('change', function() {
            previewImage(this, 'preview_mb', 'no_mb');
        });
    </script>

    <script>
        const example_image_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.withCredentials = true;
            xhr.open('POST', '/admin/api/upload_image');
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