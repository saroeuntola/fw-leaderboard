<?php
class Banner {
    public $db;

    public function __construct() {
        $this->db = dbConn(); 
    }

    // Create a new Banner
    public function createBanner($title, $image, $link) {
        $data = [
            'title' => $title,
            'image' => $image,
            'link' => $link
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


    // READ a specific Banner by ID
    public function getBannerById($id)
    {
        $quotedId = $this->db->quote($id);
        $result = dbSelect('banner', '*', "id=$quotedId");

        return ($result && count($result) > 0) ? $result[0] : null;
    }
    // Update a Banner
    public function updateBanner($id, $title, $image, $link) {
        if (!$this->getBannerById($id)) {
            return false; 
        }

        $data = [
            'title' => $title,
            'image' => $image,
            'link' => $link
        ];
        return dbUpdate('banner', $data, "id=" . $this->db->quote($id));
    }

    // Delete a product
    // Delete a Banner (with image file removal)
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
