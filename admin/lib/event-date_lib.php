<?php
class Event
{
    private $db;

    public function __construct()
    {
        // Use your PDO connection function
        $this->db = dbConn(); // assumes dbConn() returns PDO instance
    }

    // -------------------
    // Create Event
    // -------------------
    public function createEvent($start_date, $end_date)
    {
        $sql = "INSERT INTO event_date (start_date, end_date) VALUES (:start_date, :end_date)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);
    }

    // -------------------
    // Read All Events
    // -------------------
    public function getEvents()
    {
        $sql = "SELECT * FROM event_date ORDER BY id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------
    // Read Single Event
    // -------------------
    public function getEventById($id)
    {
        $sql = "SELECT * FROM event_date WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // -------------------
    // Update Event
    // -------------------
    public function updateEvent($id, $start_date, $end_date)
    {
        $sql = "UPDATE event_date SET start_date = :start_date, end_date = :end_date WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':start_date' => $start_date,
            ':end_date' => $end_date,
            ':id' => $id
        ]);
    }

    // -------------------
    // Delete Event
    // -------------------
    public function deleteEvent($id)
    {
        $sql = "DELETE FROM event_date WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
