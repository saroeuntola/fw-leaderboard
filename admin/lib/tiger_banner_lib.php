<?php
class Tiger_banners
{
    public $db;

    public function __construct()
    {
        $this->db = dbConn();
    }

    public function toggleBannerStatus($id)
    {
        $sql = "UPDATE tiger_banners
            SET status = IF(status = 1, 0, 1)
            WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Create a new tiger_banners
    public function createBanner($title, $image, $link, $post, $status)
    {
        $data = [
            'title' => $title,
            'image' => $image,
            'link' => $link,
            'post_by' => $post,
            'status' => $status
        ];
        return dbInsert('tiger_banners', $data);
    }

    public function gettiger_bannersByStatus()
    {
        $sql = "SELECT * FROM tiger_banners WHERE status = 1 ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // READ all tiger_banners
    public function gettiger_banners()
    {
        $sql = "SELECT * FROM tiger_banners ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // READ a specific tiger_banners by ID
    public function gettiger_bannersById($id)
    {
        $quotedId = $this->db->quote($id);
        $result = dbSelect('tiger_banners', '*', "id=$quotedId");

        return ($result && count($result) > 0) ? $result[0] : null;
    }
    // Update a tiger_banners
    public function updatetiger_banners($id, $title, $image, $link, $post, $status)
    {
        if (!$this->gettiger_bannersById($id)) {
            return false;
        }

        $data = [
            'title' => $title,
            'image' => $image,
            'link' => $link,
            'post_by' => $post,
            'status' => $status
        ];
        return dbUpdate('tiger_banners', $data, "id=" . $this->db->quote($id));
    }

    // Delete a product
    // Delete a tiger_banners (with image file removal)
    public function deletetiger_banners($id)
    {
        // Get tiger_banners info
        $tiger_banners = $this->gettiger_bannersById($id);
        if ($tiger_banners && !empty($tiger_banners['image'])) {
            $filePath = "../" . $tiger_banners['image'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        return dbDelete('tiger_banners', "id=" . $this->db->quote($id));
    }
}
