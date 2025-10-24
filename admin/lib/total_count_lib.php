<?php

class Count
{

private $db;

public function __construct()
{
$this->db = dbConn();
}
    function getUserCount()
    {

        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        return $stmt->fetchColumn();
    }

    function getPostCount()
    {
  
        $stmt = $this->db->query("SELECT COUNT(*) FROM post");
        return $stmt->fetchColumn();
    }

    function getUpcomingEventCount()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM upcoming_event WHERE event_date >= CURDATE()");
        return $stmt->fetchColumn();
    }
}