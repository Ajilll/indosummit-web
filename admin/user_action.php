<?php
session_start();
require_once '../config/database.php';

// Pastikan Admin
if ($_SESSION['role'] != 'admin') exit();

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    // Cegah admin menghapus/edit dirinya sendiri
    if ($id == $_SESSION['user_id']) {
        echo "<script>alert('Anda tidak bisa mengubah akun sendiri!'); window.location='dashboard.php';</script>";
        exit();
    }

    if ($action == 'promote') {
        // Jadikan Admin
        $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
        $stmt->execute([$id]);
    } 
    elseif ($action == 'demote') {
        // Jadikan User Biasa
        $stmt = $conn->prepare("UPDATE users SET role = 'user' WHERE id = ?");
        $stmt->execute([$id]);
    } 
    elseif ($action == 'delete') {
        // Hapus User
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }
}

header("Location: dashboard.php");
?>