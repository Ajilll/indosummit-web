<?php
// File: api/search.php
header('Content-Type: application/json');
require_once '../config/database.php';

$keyword = isset($_GET['term']) ? $_GET['term'] : '';

if (strlen($keyword) > 0) {
    try {
        // Cari gunung berdasarkan nama, batasi 5 hasil saja biar rapi
        $stmt = $conn->prepare("SELECT id, name, province, image_url FROM mountains WHERE name LIKE :keyword LIMIT 5");
        $stmt->execute(['keyword' => "%$keyword%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($results);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?>