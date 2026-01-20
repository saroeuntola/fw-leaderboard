<?php
class Banner {
    public $db;

    public function __construct() {
        $this->db = dbConn(); 
    }


    public function replacePostNoForCreate($postNo)
    {
        try {
            $this->db->beginTransaction();

            // get max postNo
            $stmt = $this->db->query("SELECT IFNULL(MAX(postNo), 0) + 1 AS nextNo FROM banner");
            $nextNo = (int)$stmt->fetch(PDO::FETCH_ASSOC)['nextNo'];

            // move existing post
            $update = $this->db->prepare(
                "UPDATE banner SET postNo = :nextNo WHERE postNo = :postNo"
            );
            $update->execute([
                'nextNo' => $nextNo,
                'postNo' => $postNo
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // Check if postNo exists (exclude current post)
    public function findPostByPostNo($postNo, $excludeId = null)
    {
        $sql = "SELECT id FROM banner WHERE postNo = ?";
        $params = [$postNo];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get next available postNo
    public function getNextPostNo()
    {
        $stmt = $this->db->query("SELECT MAX(postNo) AS max_no FROM banner");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['max_no'] ?? 0) + 1;
    }

    // Replace postNo logic
    public function replacePostNo($currentId, $newPostNo)
    {
        $this->db->beginTransaction();

        try {
            // Find conflict post
            $conflict = $this->findPostByPostNo($newPostNo, $currentId);

            if ($conflict) {
                $newFreeNo = $this->getNextPostNo();

                // Move old post to new number
                $stmt = $this->db->prepare("UPDATE banner SET postNo = ? WHERE id = ?");
                $stmt->execute([$newFreeNo, $conflict['id']]);
            }

            // Update current post
            $stmt = $this->db->prepare("UPDATE banner SET postNo = ? WHERE id = ?");
            $stmt->execute([$newPostNo, $currentId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    // Check if postNo exists
    public function postNoExists(int $postNo, int $excludeId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM banners WHERE postNo = ?";
        $params = [$postNo];

        if ($excludeId > 0) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() > 0;
    }

    public function clearPostNo(int $postNo): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE banners SET postNo = 0 WHERE postNo = ?"
        );
        return $stmt->execute([$postNo]);
    }

    public function updatePostNo(int $id, int $postNo): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE banners SET postNo = ? WHERE id = ?"
        );
        return $stmt->execute([$postNo, $id]);
    }

    // 🔁 SWAP postNo
    public function swapPostNo($id, $newPostNo)
    {
        $this->db->beginTransaction();

        try {
            // Get current postNo of the target banner
            $stmt = $this->db->prepare("SELECT postNo FROM banner WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$current) {
                throw new Exception('Post not found');
            }

            $currentPostNo = (int)$current['postNo'];

            // If no change needed
            if ($currentPostNo === (int)$newPostNo) {
                $this->db->commit();
                return true;
            }

            // Check if another banner already has the newPostNo
            $stmt = $this->db->prepare("SELECT id FROM banner WHERE postNo = ? AND id != ?");
            $stmt->execute([$newPostNo, $id]);
            $conflict = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($conflict) {
                // Get a temporary free postNo (max+1)
                $stmt = $this->db->query("SELECT IFNULL(MAX(postNo),0)+1 AS tempNo FROM banner");
                $tempNo = (int)$stmt->fetch(PDO::FETCH_ASSOC)['tempNo'];

                // Move conflicting banner to temporary number
                $stmt = $this->db->prepare("UPDATE banner SET postNo = ? WHERE id = ?");
                $stmt->execute([$tempNo, $conflict['id']]);
            }

            // Update current banner to the newPostNo
            $stmt = $this->db->prepare("UPDATE banner SET postNo = ? WHERE id = ?");
            $stmt->execute([$newPostNo, $id]);

            // If conflict existed, move it to old postNo
            if ($conflict) {
                $stmt = $this->db->prepare("UPDATE banner SET postNo = ? WHERE id = ?");
                $stmt->execute([$currentPostNo, $conflict['id']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            // Optional: log $e->getMessage()
            return false;
        }
    }







    public function toggleBannerStatus($id)
    {
        $sql = "UPDATE banner
            SET status = IF(status = 1, 0, 1)
            WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Create a new Banner
    public function createBanner($title, $image, $link, $post_by, $status) {
        $data = [
            'title' => $title,
            'image' => $image,
            'link' => $link,
            'post_by' => $post_by,
            'status' => $status
        ];
        return dbInsert('banner', $data);
    }

    // READ all Banner
 public function getBanner() {
    $sql = "SELECT * FROM banner ORDER BY created_at DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getBannerBySatus()
    {
        $sql = "SELECT * FROM banner 
            WHERE status = 1
            ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    // READ a specific Banner by ID
    public function getBannerById($id)
    {
        $quotedId = $this->db->quote($id);
        $result = dbSelect('banner', '*', "id=$quotedId");

        return ($result && count($result) > 0) ? $result[0] : null;
    }
    // Update a Banner
    public function updateBanner($id, $title, $image, $link, $post_by, $status ) {
        if (!$this->getBannerById($id)) {
            return false; 
        }

        $data = [
            'title' => $title,
            'image' => $image,
            'link' => $link,
            'post_by' => $post_by,
            'status' => $status
        ];
        return dbUpdate('banner', $data, "id=" . $this->db->quote($id));
    }

    public function deleteBanner($id)
    {
        // Get banner info
        $banner = $this->getBannerById($id);
        if ($banner && !empty($banner['image'])) {
            $filePath = "../" . $banner['image']; // adjust path to your uploads dir
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        return dbDelete('banner', "id=" . $this->db->quote($id));
    }
}
?>
