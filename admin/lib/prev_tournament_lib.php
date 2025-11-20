<?php

class TournamentPost
{
    public $db;

    public function __construct()
    {
        $this->db = dbConn();
    }

    /**
     * Get all tournaments
     */
    public function getAllTournaments($limit = null)
    {
        $sql = "SELECT * FROM tournaments ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->db->prepare($sql);

        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get latest tournaments
     */
    public function getLatest($limit = 1)
    {
        $query = "SELECT id, title, image, description, created_at, type
              FROM tournaments
              ORDER BY created_at DESC, id DESC
              LIMIT :limit";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data ?: [];
        } catch (PDOException $e) {
            error_log('Error fetching latest tournament: ' . $e->getMessage());
            return [];
        }
    }


    /**
     * Get a single tournament by ID
     */
    public function getTournamentById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM tournaments WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new tournament
     */
    public function createTournament($title, $image, $description, $type, $post_by)
    {
        $stmt = $this->db->prepare("
            INSERT INTO tournaments (title, image, description, type, post_by)
            VALUES (:title, :image, :description, :type, :post_by)
        ");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':post_by', $post_by);
        return $stmt->execute();
    }
    public function getLatestByType($type, $limit = 2)
    {
        $query = "SELECT * FROM tournaments WHERE type = :type AND title IS NOT NULL ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    /**
     * Update tournament
     */
    public function updateTournament($id, $title, $image, $description, $type, $post_by)
    {
        $stmt = $this->db->prepare("
            UPDATE tournaments 
            SET title = :title, image = :image, description = :description, type = :type, post_by = :post_by, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':post_by', $post_by);
        return $stmt->execute();
    }

    /**
     * Delete tournament
     */
    public function deleteTournament($id)
    {
        $stmt = $this->db->prepare("DELETE FROM tournaments WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
