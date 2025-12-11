<?php
session_start();
require_once '../config/database.php';

// Cek Admin
if ($_SESSION['role'] != 'admin') exit();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Hapus data (Jalur yg terhubung otomatis terhapus karena ON DELETE CASCADE di database)
    $stmt = $conn->prepare("DELETE FROM mountains WHERE id = ?");
    $stmt->execute([$id]);
}
header("Location: dashboard.php");
?>