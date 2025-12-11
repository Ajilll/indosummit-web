<?php
session_start();
require_once '../config/database.php';
if ($_SESSION['role'] != 'admin') exit();

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM hiking_routes WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}
header("Location: dashboard.php");
?>