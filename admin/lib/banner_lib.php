<?php
class Banner {
    public $db;

    public function __construct() {
        $this->db = dbConn(); 
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
