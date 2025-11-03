<?php
class Leaderboard
{
    private $db;

    public function __construct()
    {
        $this->db = dbConn(); 
    }

    // Create (insert new row)
    public function create($username, $point, $bet_market, $price)
    {
        $data = [
            'username' => $username,
            'point'    => $point,
            'bet_market' => $bet_market,
            'price'    => $price
        ];
        return dbInsert('fw_leaderboard', $data);
    }

    // Read all
    public function all()
    {
        return dbSelect('fw_leaderboard', '*');
    }

    // Get one by ID
    public function getById($id)
    {
        $quotedId = $this->db->quote($id);
        $result = dbSelect('fw_leaderboard', '*', "id=$quotedId");
        return ($result && count($result) > 0) ? $result[0] : null;
    }

    // Update row
    public function update($id, $username, $point,$bet_market, $price)
    {
        $data = [
            'username' => $username,
            'point'    => $point,
            'bet_market' => $bet_market,
            'price'    => $price
        ];
        return dbUpdate('fw_leaderboard', $data, "id=" . $this->db->quote($id));
    }

    // Delete one row
    public function delete($id)
    {
        return dbDelete('fw_leaderboard', "id=" . $this->db->quote($id));
    }

    // Delete all rows
    public function truncate()
    {
        return $this->db->query("TRUNCATE TABLE fw_leaderboard");
    }
}
