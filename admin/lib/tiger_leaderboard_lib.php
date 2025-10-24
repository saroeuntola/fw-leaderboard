<?php
class TigerLeaderboard
{
    private $db;

    public function __construct()
    {
        $this->db = dbConn();
    }

    // Create (insert new row)
    public function create($uid, $matches, $t_o, $price)
    {
        $data = [
            'uid' => $uid,
            'matches' => $matches,
            't_o' => $t_o,
            'price' => $price,
        ];
        return dbInsert('tiger_leaderboard', $data);
    }

    // Read all
    public function all()
    {
        return dbSelect('tiger_leaderboard', '*');
    }

    // Get one by ID
    public function getById($id)
    {
        $quotedId = $this->db->quote($id);
        $result = dbSelect('tiger_leaderboard', '*', "id=$quotedId");
        return ($result && count($result) > 0) ? $result[0] : null;
    }

    // Update row
    public function update($id, $uid, $matches, $t_o, $price)
    {
        $data = [
            'uid' => $uid,
            'matches' => $matches,
            't_o' => $t_o,
            'price' => $price
        ];
        return dbUpdate('tiger_leaderboard', $data, "id=" . $this->db->quote($id));
    }

    // Delete one row
    public function delete($id)
    {
        return dbDelete('tiger_leaderboard', "id=" . $this->db->quote($id));
    }

    // Delete all rows
    public function truncate()
    {
        return $this->db->query("TRUNCATE TABLE tiger_leaderboard");
    }
}
