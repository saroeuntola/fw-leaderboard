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


    public function updatePostNo(int $id, int $postNo): bool
    {
        $sql = "UPDATE banner SET postNo = :postNo WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':postNo' => $postNo,
            ':id' => $id
        ]);
    }
  

    public function postNoExists(int $postNo, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM banner WHERE postNo = :postNo";

        if ($excludeId !== null) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':postNo', $postNo, PDO::PARAM_INT);

        if ($excludeId !== null) {
            $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
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
    public function createBanner($title, $image, $link, $post_by, $status, $postNo) {
        $data = [
            'title' => $title,
            'image' => $image,
            'link' => $link,
            'post_by' => $post_by,
            'status' => $status,
            'postNo' => $postNo
        ];
        return dbInsert('banner', $data);
    }

    // READ all Banner
 public function getBanner() {
    $sql = "SELECT * FROM banner ORDER BY postNo ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getBannerBySatus()
    {
        $sql = "SELECT * FROM banner 
            WHERE status = 1
            ORDER BY postNo ASC";

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
    public function updateBanner($id, $title, $image, $link, $post_by, $status, $postNo ) {
        if (!$this->getBannerById($id)) {
            return false; 
        }

        $data = [
            'title' => $title,
            'image' => $image,
            'link' => $link,
            'post_by' => $post_by,
            'status' => $status,
            'postNo' => $postNo
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

    public function swapPostNo($id, $newPostNo)
    {
        $this->db->beginTransaction();

        try {
            // Get current postNo
            $stmt = $this->db->prepare("SELECT postNo FROM banner WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetch();

            if (!$current) {
                throw new Exception('Post not found');
            }

            $currentPostNo = $current['postNo'];

            // Find conflict
            $stmt = $this->db->prepare("SELECT id FROM banner WHERE postNo = ? AND id != ?");
            $stmt->execute([$newPostNo, $id]);
            $conflict = $stmt->fetch();

            if ($conflict) {
                // Swap
                $stmt = $this->db->prepare("UPDATE banner SET postNo = ? WHERE id = ?");
                $stmt->execute([$currentPostNo, $conflict['id']]);
            }

            // Update current
            $stmt = $this->db->prepare("UPDATE banner SET postNo = ? WHERE id = ?");
            $stmt->execute([$newPostNo, $id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}


?>
