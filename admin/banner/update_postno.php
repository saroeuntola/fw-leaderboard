<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once '../lib/checkroles.php';
require_once '../lib/banner_lib.php';

protectRoute([1, 3]);

$id      = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$postNo  = isset($_POST['postNo']) ? (int)$_POST['postNo'] : null;
$replace = isset($_POST['replace']) ? (int)$_POST['replace'] : 0;

if ($id <= 0 || $postNo === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input',
        'debug' => $_POST
    ]);
    exit;
}

$banner = new Banner();

try {
    if ($replace === 1) {
        // Swap logic with temporary number
        $stmt = $banner->db->prepare("SELECT postNo FROM banner WHERE id = ?");
        $stmt->execute([$id]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$current) throw new Exception('Post not found');

        $currentPostNo = (int)$current['postNo'];

        if ($currentPostNo === $postNo) {
            echo json_encode(['success' => true, 'message' => 'No change needed']);
            exit;
        }

        $banner->db->beginTransaction();

        // Check if newPostNo exists
        $stmt = $banner->db->prepare("SELECT id FROM banner WHERE postNo = ? AND id != ?");
        $stmt->execute([$postNo, $id]);
        $conflict = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($conflict) {
            $tempNo = (int)$banner->db->query("SELECT IFNULL(MAX(postNo),0)+1 AS tempNo FROM banner")->fetch(PDO::FETCH_ASSOC)['tempNo'];
            $stmt = $banner->db->prepare("UPDATE banner SET postNo = ? WHERE id = ?");
            $stmt->execute([$tempNo, $conflict['id']]);
        }

        $stmt = $banner->db->prepare("UPDATE banner SET postNo = ? WHERE id = ?");
        $stmt->execute([$postNo, $id]);

        if ($conflict) {
            $stmt = $banner->db->prepare("UPDATE banner SET postNo = ? WHERE id = ?");
            $stmt->execute([$currentPostNo, $conflict['id']]);
        }

        $banner->db->commit();

        echo json_encode(['success' => true, 'message' => 'swapPostNo executed']);
        exit;
    } else {
        // Normal update
        if ($banner->postNoExists($postNo, $id)) {
            echo json_encode([
                'success' => false,
                'message' => 'Post number already exists'
            ]);
            exit;
        }

        $success = $banner->updatePostNo($id, $postNo);
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Updated successfully' : 'Update failed'
        ]);
        exit;
    }
} catch (Exception $e) {
    if ($banner->db->inTransaction()) $banner->db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Exception: ' . $e->getMessage()
    ]);
    exit;
}
