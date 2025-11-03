<?php
class Brand {
    public $db;

    public function __construct() {
        $this->db = dbConn(); 
    }

    // Create a new Brand
    public function createBrand($name, $image, $link) {
        $data = [
            'brand_name' => $name,
            'brand_image' => $image,
            'link' => $link
        ];
        return dbInsert('brand', $data);
    }
    // READ all Brand
    public function getBrandLimit($limit = 1)
    {
        $sql = "SELECT * FROM brand ORDER BY id DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getBrand()
    {
        return dbSelect('brand', '*');
    }
    // READ a specific Brand by ID
    public function getBrandById($id)
    {
        $quotedId = $this->db->quote($id);
        $result = dbSelect('brand', '*', "id=$quotedId");

        return ($result && count($result) > 0) ? $result[0] : null;
    }
    // Update a Brand
    public function updateBrand($id, $name, $image, $link) {
        if (!$this->getBrandById($id)) {
            return false; 
        }

        $data = [
            'brand_name' => $name,
            'brand_image' => $image,
            'link' => $link
        ];
        return dbUpdate('brand', $data, "id=" . $this->db->quote($id));
    }

    // Delete a product
    public function deleteBrand($id) {
        return dbDelete('brand', "id=" . $this->db->quote($id));
    }
}
?>
