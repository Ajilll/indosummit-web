<?php
session_start();
require_once '../config/database.php';

if (!isset($_GET['id'])) header("Location: dashboard.php");
$id = $_GET['id'];

// Ambil data lama
$stmt = $conn->prepare("SELECT * FROM mountains WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $province = $_POST['province'];
    $elevation = $_POST['elevation'];
    $description = $_POST['description'];
    $map_url = $_POST['map_url'];

    // Cek apakah upload gambar baru?
    if (!empty($_FILES['image']['name'])) {
        $file_name = time() . '_' . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], "../assets/img/" . $file_name);
        
        // Update dengan gambar baru
        $sql = "UPDATE mountains SET name=?, province=?, elevation=?, description=?, map_url=?, image_url=? WHERE id=?";
        $conn->prepare($sql)->execute([$name, $province, $elevation, $description, $map_url, $file_name, $id]);
    } else {
        // Update tanpa ganti gambar
        $sql = "UPDATE mountains SET name=?, province=?, elevation=?, description=?, map_url=? WHERE id=?";
        $conn->prepare($sql)->execute([$name, $province, $elevation, $description, $map_url, $id]);
    }
    
    echo "<script>alert('Data Berhasil Diupdate'); window.location='dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Gunung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Edit Gunung: <?= $data['name'] ?></h2>
    <form method="POST" enctype="multipart/form-data" class="card p-4 mt-3">
        <div class="mb-3">
            <label>Nama Gunung</label>
            <input type="text" name="name" class="form-control" value="<?= $data['name'] ?>" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Provinsi</label>
                <input type="text" name="province" class="form-control" value="<?= $data['province'] ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Ketinggian</label>
                <input type="number" name="elevation" class="form-control" value="<?= $data['elevation'] ?>" required>
            </div>
        </div>
        <div class="mb-3">
            <label>Deskripsi</label>
            <textarea name="description" class="form-control" rows="4"><?= $data['description'] ?></textarea>
        </div>
        <div class="mb-3">
            <label>Link Maps Embed</label>
            <input type="text" name="map_url" class="form-control" value="<?= htmlspecialchars($data['map_url']) ?>">
        </div>
        <div class="mb-3">
            <label>Ganti Foto (Kosongkan jika tidak ingin mengganti)</label>
            <input type="file" name="image" class="form-control">
            <small>Foto saat ini: <?= $data['image_url'] ?></small>
        </div>
        <button type="submit" class="btn btn-warning w-100">Update Data</button>
        <a href="dashboard.php" class="btn btn-secondary w-100 mt-2">Batal</a>
    </form>
</body>
</html>