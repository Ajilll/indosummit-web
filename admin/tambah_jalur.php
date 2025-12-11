<?php
session_start();
require_once '../config/database.php';

// Cek Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$stmt_gunung = $conn->query("SELECT id, name FROM mountains ORDER BY name ASC");
$mountains = $stmt_gunung->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mountain_id = $_POST['mountain_id'];
    $name = $_POST['name'];
    $basecamp_info = $_POST['basecamp_info']; // Data baru
    $est_time = $_POST['est_time'];
    $route_length = $_POST['route_length']; // Data baru
    $slope = $_POST['slope']; // Data baru
    $difficulty = $_POST['difficulty'];
    $status = $_POST['status'];
    
    $booking_type = $_POST['booking_type'];
    $booking_url = ($booking_type == 'online') ? $_POST['booking_url'] : null;
    $is_online = ($booking_type == 'online') ? 1 : 0;

    try {
        $sql = "INSERT INTO hiking_routes 
                (mountain_id, name, basecamp_info, difficulty, est_time, route_length, slope, is_online_booking, booking_url, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $mountain_id, $name, $basecamp_info, $difficulty, $est_time, $route_length, $slope, $is_online, $booking_url, $status
        ]);
        
        $success = "Jalur berhasil ditambahkan!";
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah Jalur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Paksa input berwarna hitam di atas putih agar terbaca */
        .form-control, .form-select {
            background-color: #fff !important;
            color: #000 !important;
            border: 1px solid #ced4da;
        }
        /* Label tetap gelap agar kontras dengan background halaman putih */
        label { color: #333; font-weight: 500; }
    </style>
</head>
<body class="bg-light container mt-5 pb-5">
    
    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">Tambah Jalur Pendakian</h4>
        </div>
        <div class="card-body p-4">
            
            <div class="d-flex gap-2 mb-4">
                <a href="tambah_gunung.php" class="btn btn-secondary btn-sm">Tambah Gunung Baru</a>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Ke Dashboard</a>
            </div>

            <?php if(isset($success)): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
            <?php if(isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

            <form method="POST">
                <!-- Pilih Gunung -->
                <div class="mb-3">
                    <label>Pilih Gunung</label>
                    <select name="mountain_id" class="form-select" required>
                        <option value="">-- Pilih Gunung --</option>
                        <?php foreach($mountains as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= $m['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Nama Jalur</label>
                        <input type="text" name="name" class="form-control" placeholder="Via Putri" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Lokasi Basecamp</label>
                        <input type="text" name="basecamp_info" class="form-control" placeholder="Desa Cibodas, Cianjur..." required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Estimasi Waktu</label>
                        <input type="text" name="est_time" class="form-control" placeholder="7-9 Jam" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Panjang Jalur (KM)</label>
                        <input type="text" name="route_length" class="form-control" placeholder="9 KM">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Kemiringan</label>
                        <input type="text" name="slope" class="form-control" placeholder="30-45 Derajat">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Tingkat Kesulitan</label>
                        <select name="difficulty" class="form-select">
                            <option value="Mudah">Mudah</option>
                            <option value="Sedang">Sedang</option>
                            <option value="Sulit">Sulit</option>
                            <option value="Ekstrim">Ekstrim</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Status Jalur</label>
                        <select name="status" class="form-select">
                            <option value="Buka">Buka</option>
                            <option value="Tutup">Tutup</option>
                        </select>
                    </div>
                </div>

                <hr>
                
                <div class="mb-3">
                    <label>Sistem Booking</label>
                    <select name="booking_type" class="form-select" id="bookingType" onchange="toggleBookingUrl()">
                        <option value="offline">Offline / On The Spot</option>
                        <option value="online">Online (Website Resmi)</option>
                    </select>
                </div>

                <div class="mb-3 d-none" id="bookingUrlGroup">
                    <label>Link Website Booking</label>
                    <input type="url" name="booking_url" class="form-control" placeholder="https://booking...">
                </div>

                <button type="submit" class="btn btn-success w-100 py-2">Simpan Jalur</button>
            </form>
        </div>
    </div>

    <script>
        function toggleBookingUrl() {
            var type = document.getElementById('bookingType').value;
            var inputGroup = document.getElementById('bookingUrlGroup');
            if (type === 'online') {
                inputGroup.classList.remove('d-none');
            } else {
                inputGroup.classList.add('d-none');
            }
        }
    </script>
</body>
</html>