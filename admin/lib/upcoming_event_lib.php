<?php
class UpcomingEvent
{
    public $db;

    public function __construct()
    {
        $this->db = dbConn();
    }


    public function create(string $title, string $matches, string $event_date, int $duration): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO upcoming_event (title, matches, event_date, duration, created_at)
            VALUES (:title, :matches, :event_date, :duration, NOW())
        ");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':matches', $matches);
        $stmt->bindParam(':event_date', $event_date);
        $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Update an existing event
    public function update(int $id, string $title, string $matches, string $event_date, int $duration): bool
    {
        $stmt = $this->db->prepare("
            UPDATE upcoming_event
            SET title = :title, matches = :matches, event_date = :event_date, duration = :duration
            WHERE id = :id
        ");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':matches', $matches);
        $stmt->bindParam(':event_date', $event_date);
        $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Fetch all events
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM upcoming_event ORDER BY event_date ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
