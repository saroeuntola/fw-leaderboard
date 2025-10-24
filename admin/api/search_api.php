<?php
include "../library/db.php"; // returns PDO connection

header('Content-Type: application/json; charset=UTF-8');

try {
    $conn = dbConn();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES 'utf8mb4'");

    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'bn']) ? $_GET['lang'] : 'bn';

    if ($q === '' || mb_strlen($q) < 2) {
        echo json_encode([]);
        exit;
    }

    // Set columns based on language
    $nameColumn = $lang === 'bn' ? 'name_bn' : 'name';
    $descColumn = $lang === 'bn' ? 'description_bn' : 'description';

    // Prepare SQL to search in name or description
    $sql = "SELECT slug, $nameColumn AS title, image
            FROM games
            WHERE $nameColumn LIKE :q OR $descColumn LIKE :q
            ORDER BY id DESC
            LIMIT 10";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':q' => "%$q%"]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
