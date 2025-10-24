<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['player_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if (!isset($_POST['balance'])) {
    echo json_encode(['success' => false, 'message' => 'No balance provided']);
    exit;
}

$playerId = $_SESSION['player_id'];
$newBalance = floatval($_POST['balance']);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=your_db;charset=utf8mb4', 'db_user', 'db_pass');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "UPDATE players SET balance = :balance WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':balance', $newBalance);
    $stmt->bindParam(':id', $playerId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'DB update failed']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}
