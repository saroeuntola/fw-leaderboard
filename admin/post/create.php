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
function generateUniqueImageName($originalName, $prefix = '')
{
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $unique = uniqid($prefix, true) . '_' . bin2hex(random_bytes(4));
    return $unique . '.' . $ext;
}

$currentUser = $_SESSION['username'] ?? '';
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
    $post_by = $currentUser ?? '';
    $status = $_POST['status'];
    $postNo = $_POST['postNo'];
    $meta_title = $_POST['meta_title'] ?? '';
    $replace = $_POST['replacePostNo'] ?? 0;
    // Handle Image Upload
    $uploadDir = "post_image/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    /* ================= PC IMAGE ================= */
    $imagePath = "";
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === 0) {

        $fileName = generateUniqueImageName($_FILES['image']['name'], 'pc_');
        $imagePath = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $imagePath = "";
        }
    }

    /* ================= MOBILE IMAGE ================= */
    $imageMbPath = "";
    if (!empty($_FILES['image_mb']['name']) && $_FILES['image_mb']['error'] === 0) {

        $fileNameMb = generateUniqueImageName($_FILES['image_mb']['name'], 'mb_');
        $imageMbPath = $uploadDir . $fileNameMb;

        if (!move_uploaded_file($_FILES['image_mb']['tmp_name'], $imageMbPath)) {
            $imageMbPath = "";
        }
    }


    // Validate required fields
    if (empty($gameName) || empty($description) || empty($categoryId)) {
        echo "<p class='text-red-500 text-center'>Error: Title, Description, and Category are required.</p>";
    } else {
        if ($product->createpost($gameName, $imagePath, $imageMbPath, $description, $game_link, $categoryId, $meta_text, $name_bn, $description_bn, $meta_text_bn, $meta_desc, $meta_keyword, $meta_desc_bn, $meta_keyword_bn, $post_by, $status, $postNo, $meta_title)) {
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
    <script src="/js/tinymce/tinymce.min.js"></script>
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
        <div class="flex justify-end">
            <button onclick="location.href='./'" class=" bg-red-600 text-white py-2 px-4 rounded-md text-lg font-semibold hover:bg-gray-900 transition-all duration-300 cursor-pointer justify-end">
            Close
        </button>
        </div>
        
        <h2 class="text-3xl font-bold text-center mb-6 text-indigo-700">Create Post</h2>

        <form action="create" method="POST" enctype="multipart/form-data" class="space-y-5">
            <!-- English Fields -->
            <div class="form-section">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Post Contents</h3>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Title*</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Description*</label>
                    <textarea id="editor-en" name="description"></textarea>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Title*</label>
                    <input type="text" id="meta_title" name="meta_title"
                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Description*</label>
                    <input type="text" id="meta_desc" name="meta_desc"
                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">

                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1 ">Meta Keyword*</label>
                    <input type="text" name="meta_keyword" required class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none" placeholder="Separate keywords with commas">
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Alt image*</label>
                    <input type="text" name="meta_text" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
            </div>

            <!-- Image Upload Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- PC Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Image PC (1024px × 400px)
                    </label>

                    <img
                        id="preview_pc"
                        src="<?= !empty($productData['image']) ? htmlspecialchars($productData['image']) : '' ?>"
                        class="w-full h-50 rounded-md mb-2 border
                   <?= empty($productData['image']) ? 'hidden' : '' ?>">

                    <p
                        id="no_pc"
                        class="text-gray-500 mb-2
                   <?= !empty($productData['image']) ? 'hidden' : '' ?>">
                        No image available
                    </p>

                    <input
                        type="file"
                        name="image"
                        id="image_pc"
                        accept="image/*"
                        class="w-full px-4 py-2 border rounded-md
                   focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>

                <!-- Mobile Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Image Mobile (300px × 210px)
                    </label>

                    <img
                        id="preview_mb"
                        src="<?= !empty($productData['image_mb']) ? htmlspecialchars($productData['image_mb']) : '' ?>"
                        class="w-full h-50 rounded-md mb-2 border
                   <?= empty($productData['image_mb']) ? 'hidden' : '' ?>">

                    <p
                        id="no_mb"
                        class="text-gray-500 mb-2
                   <?= !empty($productData['image_mb']) ? 'hidden' : '' ?>">
                        No image available
                    </p>

                    <input
                        type="file"
                        name="image_mb"
                        id="image_mb"
                        accept="image/*"
                        class="w-full px-4 py-2 border rounded-md
                   focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>

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
            <div class="mt-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Link(Optional)</label>
                <input type="text" name="game_link" class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none">
            </div>
            <!-- Post No -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Post No*</label>

                <input type="number" name="postNo" id="postNo" required
                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                    oninput="checkPostNo()">

                <p id="postNoError" class="text-red-500 text-sm mt-1 hidden">
                    Number already exists.
                </p>

                <button type="button"
                    id="replaceBtn"
                    onclick="enableReplace()"
                    class="mt-2 hidden bg-orange-600 text-white px-4 py-2 rounded-md text-sm hover:bg-orange-700">
                    Replace number existing post
                </button>

                <input type="hidden" name="replacePostNo" id="replacePostNo" value="0">
            </div>
            <div class="">
                <label class="font-semibold mr-4">Status:</label><br>
                <input type="radio" name="status" value="1" checked class="">
                <label for="">
                    Public
                </label>
                <input type="radio" name="status" value="0" class="ml-2">
                <label for="">
                    Draft
                </label>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-4 pt-5">
                <button onclick="location.href='./'" class=" bg-red-600 text-white py-2 px-4 rounded-md text-lg font-semibold hover:bg-gray-900 transition-all duration-300 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class=" bg-green-600 text-white py-2 px-4 rounded-md text-lg font-semibold hover:bg-indigo-700 transition-all duration-300 cursor-pointer">
                    Post
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
                document.getElementById(previewId).src = e.target.result;
                document.getElementById(previewId).classList.remove('hidden');
                document.getElementById(emptyTextId).classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }

        document.getElementById('image_pc').addEventListener('change', function() {
            previewImage(this, 'preview_pc', 'no_pc');
        });

        document.getElementById('image_mb').addEventListener('change', function() {
            previewImage(this, 'preview_mb', 'no_mb');
        });
    </script>


    <script>
        let postNoInput = document.getElementById('postNo');
        let postNoError = document.getElementById('postNoError');
        let replaceBtn = document.getElementById('replaceBtn');
        let replaceHidden = document.getElementById('replacePostNo');

        function checkPostNo() {
            const value = postNoInput.value;

            if (!value) {
                postNoError.classList.add('hidden');
                replaceBtn.classList.add('hidden');
                replaceHidden.value = 0;
                return;
            }

            fetch('check_postno', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `postNo=${value}`
                })
                .then(r => r.json())
                .then(d => {
                    if (d.exists) {
                        postNoError.classList.remove('hidden');
                        replaceBtn.classList.remove('hidden');
                        replaceHidden.value = 0;
                    } else {
                        postNoError.classList.add('hidden');
                        replaceBtn.classList.add('hidden');
                        replaceHidden.value = 0;
                    }
                });
        }

        function enableReplace() {
            const value = postNoInput.value;
            console.log('Sending postNo:', value); // 👈 DEBUG

            fetch('replace_postno_create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `postNo=${encodeURIComponent(value)}`
                })
                .then(r => r.json())
                .then(d => {
                    console.log('replace response:', d);
                    if (d.success) {
                        replaceHidden.value = 1;
                        postNoError.classList.add('hidden');
                        replaceBtn.classList.add('hidden');
                    } else {
                        alert(d.message);
                    }
                });
        }
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