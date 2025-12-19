<?php
class UpcomingEvent
{
    public $db;

    public function __construct()
    {
        $this->db = dbConn();
        $this->db->exec("SET time_zone = '+06:00'");
    }


    public function create(
        string $title,
        string $matches,
        string $type,
        string $start_date,
        string $end_date,
        string $post_by
    ): bool {
        // PHP timezone is already Asia/Dhaka
        $stmt = $this->db->prepare("
        INSERT INTO upcoming_event 
        (title, matches, type, start_date, end_date, status, created_at, post_by)
        VALUES (:title, :matches, :type, :start_date, :end_date, 'upcoming', NOW(), :post_by)
    ");

        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':matches', $matches);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':post_by', $post_by);

        return $stmt->execute();
    }



    // Update an existing event
    public function update(
        int $id,
        string $title,
        string $matches,
        string $type,
        string $start_date,
        string $end_date,
        string $post_by
    ): bool {
        $stmt = $this->db->prepare("
        UPDATE upcoming_event
        SET title = :title,
            matches = :matches,
            type = :type,
            start_date = :start_date,
            end_date = :end_date,
            post_by = :post_by
        WHERE id = :id
    ");

        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':matches', $matches);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':post_by', $post_by);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }


    // Fetch all events
    public function getAll(): array
    {
        // 1️⃣ Auto update event status
        $this->db->exec("
        UPDATE upcoming_event
        SET status =
            CASE
                WHEN NOW() < start_date THEN 'upcoming'
                WHEN NOW() BETWEEN start_date AND end_date THEN 'running'
                ELSE 'ended'
            END
    ");

        // 2️⃣ Fetch only running + upcoming
        $stmt = $this->db->query("
        SELECT *
        FROM upcoming_event
        WHERE status IN ('running','upcoming')
        ORDER BY
            CASE status
                WHEN 'running' THEN 1
                WHEN 'upcoming' THEN 2
            END,
            start_date ASC
    ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getRunning()
    {
        $sql = "SELECT * FROM upcoming_event WHERE status = 'running' ORDER BY start_date ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getUpcoming()
    {
        $sql = "SELECT * FROM upcoming_event WHERE status = 'upcoming' ORDER BY start_date ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getUpcomingEvents()
    {
        $sql = "SELECT * FROM upcoming_event";
        return $this->db->query($sql)->fetchAll();
    }
    // Fetch single event by ID
    public function getById(int $id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM upcoming_event WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Delete event
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM upcoming_event WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
