<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();
include '../lib/checkroles.php';
include '../lib/post_lib.php';
include '../lib/users_lib.php';
include '../lib/category_lib.php';
protectPathAccess();
$product = new Post();
$category = new Category();

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

// Handle form submission
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
    // Keep old image unless a new one is uploaded
    $imagePath = $productData['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = "post_image/";
        // Sanitize filename to prevent path traversal
        $imageFileName = preg_replace("/[^a-zA-Z0-9._-]/", "", basename($_FILES["image"]["name"]));
        $imagePath = $uploadDir . $imageFileName;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
            $imagePath = $productData['image']; // Revert to old image if upload fails
        }
    }

    // Validate required fields
    if (empty($gameName) || empty($description) || empty($categoryId)) {
        echo "<p class='text-red-500 text-center'>Error: Title, Description, and Category are required.</p>";
    } else {
        if ($product->updatePost($id, $gameName, $imagePath, $description, $game_link, $categoryId, $meta_text, $name_bn, $description_bn, $meta_text_bn, $meta_desc, $meta_keyword, $meta_desc_bn, $meta_keyword_bn)) {
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
    <script src="/v2/js/tinymce/tinymce.min.js"></script>
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
        <h2 class="text-3xl font-bold text-center mb-6 text-indigo-700">Edit Post</h2>

        <form action="edit?id=<?= htmlspecialchars($productData['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-5" onsubmit="syncTinyMCEContent()">
            <!-- English Fields -->
            <div class="form-section">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">English Content</h3>
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Title* (English)</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($productData['name']) ?>" required
                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div class="mt-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description* (English)</label>
                    <textarea id="editor-en" name="description"><?= htmlspecialchars($productData['description']) ?></textarea>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Description* (English)</label>
                    <input type="text" name="meta_desc" value="<?= htmlspecialchars($productData['meta_desc']) ?>" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Keyword* (English)</label>
                    <input type="text" name="meta_keyword" value="<?= htmlspecialchars($productData['meta_keyword']) ?>" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div class="mt-4">
                    <label for="meta_text" class="block text-sm font-medium text-gray-700">Alt image* (English)</label>
                    <input type="text" name="meta_text" value="<?= htmlspecialchars($productData['meta_text']) ?>"
                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
            </div>

            <!-- Bengali Fields -->
            <!-- <div class="form-section">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Bengali Content</h3>
                <div>
                    <label for="name_bn" class="block text-sm font-medium text-gray-700">Title* (Bengali)</label>
                    <input type="text" name="name_bn" value="<?= htmlspecialchars($productDataBn['name']) ?>"
                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div class="mt-4">
                    <label for="description_bn" class="block text-sm font-medium text-gray-700">Description* (Bengali)</label>
                    <textarea id="editor-bn" name="description_bn"><?= htmlspecialchars($productDataBn['description']) ?></textarea>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Description* (Bengali)</label>
                    <input type="text" name="meta_desc_bn" value="<?= htmlspecialchars($productDataBn['meta_desc_bn']) ?>" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Keyword* (Bengali)</label>
                    <input type="text" name="meta_keyword_bn" value="<?= htmlspecialchars($productDataBn['meta_keyword_bn']) ?>" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div class="mt-4">
                    <label for="meta_text_bn" class="block text-sm font-medium text-gray-700">Alt image* (Bengali)</label>
                    <input type="text" name="meta_text_bn" value="<?= htmlspecialchars($productDataBn['meta_text']) ?>"
                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
            </div> -->

            <!-- Image -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Image</label>
                <?php if (!empty($productData['image'])): ?>
                    <img src="<?= htmlspecialchars($productData['image']) ?>" class="h-20 w-20 object-cover rounded-md mb-2">
                <?php else: ?>
                    <p class="text-gray-500 mb-2">No image available</p>
                <?php endif; ?>
                <input type="file" name="image" accept="image/*" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
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

            <!-- Submit Button -->
            <button type="submit"
                class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md text-lg font-semibold hover:bg-indigo-700 transition-all">
                Update
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