<?php
class lion_banners
{
    public $db;

    public function __construct()
    {
        $this->db = dbConn();
    }

    // Create a new lion_banners
    public function createBanner($title, $image, $link)
    {
        $data = [
            'title' => $title,
            'image' => $image,
            'link' => $link
        ];
        return dbInsert('lion_banners', $data);
    }

    // READ all lion_banners
    public function getlion_banners()
    {
        $sql = "SELECT * FROM lion_banners ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // READ a specific lion_banners by ID
    public function getlion_bannersById($id)
    {
        $quotedId = $this->db->quote($id);
        $result = dbSelect('lion_banners', '*', "id=$quotedId");

        return ($result && count($result) > 0) ? $result[0] : null;
    }
    // Update a lion_banners
    public function updatelion_banners($id, $title, $image, $link)
    {
        if (!$this->getlion_bannersById($id)) {
            return false;
        }

        $data = [
            'title' => $title,
            'image' => $image,
            'link' => $link
        ];
        return dbUpdate('lion_banners', $data, "id=" . $this->db->quote($id));
    }

    // Delete a product
    // Delete a lion_banners (with image file removal)
    public function deletelion_banners($id)
    {
        // Get lion_banners info
        $lion_banners = $this->getlion_bannersById($id);
        if ($lion_banners && !empty($lion_banners['image'])) {
            $filePath = "../" . $lion_banners['image'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        return dbDelete('lion_banners', "id=" . $this->db->quote($id));
    }
}
