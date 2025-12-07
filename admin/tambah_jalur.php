<?php
// admin/tambah_jalur.php
session_start();
require_once '../config/database.php';

// 1. Ambil daftar gunung untuk Dropdown Pilihan
$stmt_gunung = $conn->query("SELECT id, name FROM mountains ORDER BY name ASC");
$mountains = $stmt_gunung->fetchAll();

// 2. LOGIKA SIMPAN DATA
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mountain_id = $_POST['mountain_id'];
    $name = $_POST['name'];
    $difficulty = $_POST['difficulty'];
    $est_time = $_POST['est_time'];
    $status = $_POST['status'];
    
    // Logika Booking: Jika Offline, booking_url dikosongkan/diabaikan
    $booking_type = $_POST['booking_type'];
    $booking_url = ($booking_type == 'online') ? $_POST['booking_url'] : null;

    try {
        $stmt = $conn->prepare("INSERT INTO hiking_routes (mountain_id, name, difficulty, est_time, is_online_booking, booking_url, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        // is_online_booking kita simpan sebagai boolean (1 atau 0)
        $is_online = ($booking_type == 'online') ? 1 : 0;
        
        $stmt->execute([$mountain_id, $name, $difficulty, $est_time, $is_online, $booking_url, $status]);
        $success = "Jalur berhasil ditambahkan!";
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Admin - Tambah Jalur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5 mb-5">
    <h2>Tambah Jalur Pendakian</h2>
    <a href="tambah_gunung.php" class="btn btn-secondary mb-4">Tambah Gunung Baru</a>
    <a href="../index.php" class="btn btn-outline-secondary mb-4">Ke Home</a>

    <?php if(isset($success)): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if(isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div class="card p-4">
        <form method="POST">
            <!-- Piliih Gunung -->
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
                    <label>Estimasi Waktu</label>
                    <input type="text" name="est_time" class="form-control" placeholder="6-8 Jam" required>
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
            
            <!-- LOGIKA BOOKING -->
            <div class="mb-3">
                <label>Sistem Booking</label>
                <select name="booking_type" class="form-select" id="bookingType" onchange="toggleBookingUrl()">
                    <option value="offline">Offline / On The Spot</option>
                    <option value="online">Online (Website Resmi)</option>
                </select>
            </div>

            <div class="mb-3 d-none" id="bookingUrlGroup">
                <label>Link Website Booking</label>
                <input type="url" name="booking_url" class="form-control" placeholder="https://booking.gedepangrango.org">
            </div>

            <button type="submit" class="btn btn-success w-100">Simpan Jalur</button>
        </form>
    </div>

    <!-- Script Sederhana untuk Show/Hide Input Link -->
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