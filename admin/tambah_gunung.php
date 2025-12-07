<?php
// admin/tambah_gunung.php
session_start();
require_once '../config/database.php';

// Cek apakah user adalah ADMIN? (Keamanan sederhana)
// Nanti bisa diaktifkan setelah tabel users ada adminnya.
// if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
//     header("Location: ../index.php");
//     exit();
// }

// LOGIKA PROSES FORM
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $province = $_POST['province'];
    $elevation = $_POST['elevation'];
    $description = $_POST['description'];
    $map_url = $_POST['map_url'];

    // Proses Upload Gambar
    $target_dir = "../assets/img/";
    // Pastikan folder assets/img ada!
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_name = time() . '_' . basename($_FILES["image"]["name"]); // Rename biar unik
    $target_file = $target_dir . $file_name;
    
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Simpan ke Database
        try {
            $stmt = $conn->prepare("INSERT INTO mountains (name, province, elevation, description, image_url, map_url) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $province, $elevation, $description, $file_name, $map_url]);
            
            $success = "Data Gunung berhasil ditambahkan!";
        } catch (PDOException $e) {
            $error = "Error Database: " . $e->getMessage();
        }
    } else {
        $error = "Gagal upload gambar.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Admin - Tambah Gunung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5 mb-5">
    <h2>Form Tambah Data Gunung</h2>
    <a href="../index.php" class="btn btn-secondary mb-4">&larr; Kembali ke Home</a>

    <?php if(isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card p-4">
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Nama Gunung</label>
                <input type="text" name="name" class="form-control" required placeholder="Contoh: Gunung Rinjani">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Provinsi</label>
                    <input type="text" name="province" class="form-control" required placeholder="Contoh: Nusa Tenggara Barat">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Ketinggian (MDPL)</label>
                    <input type="number" name="elevation" class="form-control" required placeholder="3726">
                </div>
            </div>
            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <label>Link Google Maps (Embed src)</label>
                <input type="text" name="map_url" class="form-control" placeholder="Paste link embed maps disini">
                <small class="text-muted">Buka Google Maps > Share > Embed a map > Copy link di dalam src="..."</small>
            </div>
            <div class="mb-3">
                <label>Foto Cover Gunung</label>
                <input type="file" name="image" class="form-control" required accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary w-100">Simpan Data</button>
        </form>
    </div>
</body>
</html>