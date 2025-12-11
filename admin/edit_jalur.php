<?php
session_start();
require_once '../config/database.php';

// Cek Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) header("Location: dashboard.php");
$id = $_GET['id'];

// AMBIL DATA LAMA
$stmt = $conn->prepare("SELECT * FROM hiking_routes WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) die("Data tidak ditemukan");

// LOGIKA UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $basecamp_info = $_POST['basecamp_info'];
    $est_time = $_POST['est_time'];
    $route_length = $_POST['route_length'];
    $slope = $_POST['slope'];
    $difficulty = $_POST['difficulty'];
    $status = $_POST['status'];
    
    $booking_type = $_POST['booking_type'];
    $booking_url = ($booking_type == 'online') ? $_POST['booking_url'] : null;
    $is_online = ($booking_type == 'online') ? 1 : 0;

    try {
        $sql = "UPDATE hiking_routes SET 
                name=?, basecamp_info=?, est_time=?, route_length=?, slope=?, 
                difficulty=?, status=?, is_online_booking=?, booking_url=? 
                WHERE id=?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $name, $basecamp_info, $est_time, $route_length, $slope, 
            $difficulty, $status, $is_online, $booking_url, $id
        ]);

        echo "<script>alert('Jalur Berhasil Diupdate'); window.location='dashboard.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Gagal: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Jalur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Style manual untuk memastikan teks input berwarna hitam -->
    <style>
        .form-control { color: #000 !important; background-color: #fff !important; }
        label { font-weight: bold; margin-bottom: 5px; }
    </style>
</head>
<body class="bg-light container mt-5 pb-5">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0">Edit Jalur: <?= $data['name'] ?></h4>
        </div>
        <div class="card-body">
            <form method="POST">
                
                <!-- Nama & Basecamp -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Nama Jalur</label>
                        <input type="text" name="name" class="form-control" value="<?= $data['name'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Info Basecamp</label>
                        <input type="text" name="basecamp_info" class="form-control" value="<?= $data['basecamp_info'] ?>" required>
                    </div>
                </div>

                <!-- Detail Teknis -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Estimasi Waktu</label>
                        <input type="text" name="est_time" class="form-control" value="<?= $data['est_time'] ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Panjang Jalur (KM)</label>
                        <input type="text" name="route_length" class="form-control" value="<?= $data['route_length'] ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Kemiringan</label>
                        <input type="text" name="slope" class="form-control" value="<?= $data['slope'] ?>">
                    </div>
                </div>

                <!-- Difficulty & Status -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Tingkat Kesulitan</label>
                        <select name="difficulty" class="form-select">
                            <option value="Mudah" <?= $data['difficulty']=='Mudah'?'selected':'' ?>>Mudah</option>
                            <option value="Sedang" <?= $data['difficulty']=='Sedang'?'selected':'' ?>>Sedang</option>
                            <option value="Sulit" <?= $data['difficulty']=='Sulit'?'selected':'' ?>>Sulit</option>
                            <option value="Ekstrim" <?= $data['difficulty']=='Ekstrim'?'selected':'' ?>>Ekstrim</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Status Jalur</label>
                        <select name="status" class="form-select">
                            <option value="Buka" <?= $data['status']=='Buka'?'selected':'' ?>>Buka</option>
                            <option value="Tutup" <?= $data['status']=='Tutup'?'selected':'' ?>>Tutup</option>
                        </select>
                    </div>
                </div>
                
                <hr>
                
                <!-- Booking System -->
                <div class="mb-3">
                    <label>Sistem Booking</label>
                    <select name="booking_type" class="form-select" id="bookingType" onchange="toggleUrl()">
                        <option value="offline" <?= $data['is_online_booking']==0?'selected':'' ?>>Offline / OTS</option>
                        <option value="online" <?= $data['is_online_booking']==1?'selected':'' ?>>Online (Website)</option>
                    </select>
                </div>
                
                <div class="mb-3 <?= $data['is_online_booking']==0?'d-none':'' ?>" id="urlInput">
                    <label>Link Website Booking</label>
                    <input type="url" name="booking_url" class="form-control" value="<?= $data['booking_url'] ?>">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning w-100">Update Jalur</button>
                    <a href="dashboard.php" class="btn btn-secondary w-100">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleUrl() {
            var type = document.getElementById('bookingType').value;
            var div = document.getElementById('urlInput');
            if(type === 'online') div.classList.remove('d-none');
            else div.classList.add('d-none');
        }
    </script>
</body>
</html>