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
        $sql = "SELECT * FROM lion_tournament ORDER BY created_at DESC";
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
    public function getLatest($limit = 1)
    {
        $query = "SELECT id, title, image, description, created_at 
                  FROM lion_tournament 
                  ORDER BY created_at DESC 
                  LIMIT :limit";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching tournaments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single tournament by ID
     */
    public function getTournamentById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM lion_tournament WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new tournament
     */
    public function createTournament($title, $image, $description)
    {
        $stmt = $this->db->prepare("
            INSERT INTO lion_tournament (title, image, description)
            VALUES (:title, :image, :description)
        ");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':description', $description);

        return $stmt->execute();
    }

    /**
     * Update tournament
     */
    public function updateTournament($id, $title, $image, $description)
    {
        $stmt = $this->db->prepare("
            UPDATE lion_tournament 
            SET title = :title, image = :image, description = :description, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':description', $description);

        return $stmt->execute();
    }

    /**
     * Delete tournament
     */
    public function deleteTournament($id)
    {
        $stmt = $this->db->prepare("DELETE FROM lion_tournament WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
