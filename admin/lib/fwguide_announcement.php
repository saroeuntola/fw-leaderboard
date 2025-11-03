<?php
class FwguideAnnouncement
{
    private $db;

    public function __construct()
    {
        $this->db = dbConn();
    }

    // ✅ Create a new announcement
    public function create($title, $description, $image_pc, $image_mb, $title_bn = '', $description_bn = '')
    {
        $sql = "INSERT INTO fwguide_announcement 
                (title, description, image_pc, image_mb, title_bn, description_bn, created_at)
                VALUES (:title, :description, :image_pc, :image_mb, :title_bn, :description_bn, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':image_pc' => $image_pc,
            ':image_mb' => $image_mb,
            ':title_bn' => $title_bn,
            ':description_bn' => $description_bn
        ]);
    }

    // ✅ Get all announcements (newest first)
    public function getAll()
    {
        $sql = "SELECT * FROM fwguide_announcement ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Get announcement by ID
    public function getById($id)
    {
        $sql = "SELECT * FROM fwguide_announcement WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ Update an announcement
    public function update($id, $title, $description, $image_pc, $image_mb, $title_bn = '', $description_bn = '')
    {
        $sql = "UPDATE fwguide_announcement 
                SET title = :title, description = :description, 
                    image_pc = :image_pc, image_mb = :image_mb,
                    title_bn = :title_bn, description_bn = :description_bn
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':title' => $title,
            ':description' => $description,
            ':image_pc' => $image_pc,
            ':image_mb' => $image_mb,
            ':title_bn' => $title_bn,
            ':description_bn' => $description_bn
        ]);
    }

    // ✅ Delete an announcement
    public function delete($id)
    {
        $sql = "DELETE FROM fwguide_announcement WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
