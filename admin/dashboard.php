<?php
session_start();
require_once '../config/database.php';

// CEK ADMIN (Keamanan)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses Ditolak!'); window.location='../login.php';</script>";
    exit();
}

// AMBIL STATISTIK
$total_gunung = $conn->query("SELECT COUNT(*) FROM mountains")->fetchColumn();
$total_jalur = $conn->query("SELECT COUNT(*) FROM hiking_routes")->fetchColumn();
$total_user = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();

// AMBIL DATA LENGKAP
$gunungs = $conn->query("SELECT * FROM mountains ORDER BY id DESC")->fetchAll();
$jalurs = $conn->query("SELECT hiking_routes.*, mountains.name as mountain_name FROM hiking_routes JOIN mountains ON hiking_routes.mountain_id = mountains.id ORDER BY id DESC")->fetchAll();
$users = $conn->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Mundur satu folder -->
</head>
<body>

    <nav class="sidebar d-none d-md-block">
        <div class="p-4"><h5 class="fw-bold text-white">ADMIN PANEL</h5></div>
        <ul class="nav flex-column mt-2 nav-pills" id="adminTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active w-100 text-start text-white" data-bs-toggle="tab" data-bs-target="#dashboard" type="button"><i class="bi bi-speedometer2 me-2"></i> Dashboard</button>
            </li>
            <li class="nav-item">
                <button class="nav-link w-100 text-start text-white" data-bs-toggle="tab" data-bs-target="#mountains" type="button"><i class="bi bi-map me-2"></i> Data Gunung</button>
            </li>
            <li class="nav-item">
                <button class="nav-link w-100 text-start text-white" data-bs-toggle="tab" data-bs-target="#trails" type="button"><i class="bi bi-signpost-split me-2"></i> Data Jalur</button>
            </li>
        </ul>
        <div class="p-4 mt-auto"><a href="../logout.php" class="btn btn-outline-danger w-100 btn-sm">Logout</a></div>
    </nav>

    <main class="main-content">
        <div class="container-fluid tab-content">
            
            <!-- Dashboard Tab -->
            <div class="tab-pane fade show active" id="dashboard">
                <h2 class="fw-bold text-white mb-5">Dashboard Overview</h2>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="glass-card p-4 d-flex align-items-center">
                            <div class="bg-info bg-opacity-25 p-3 rounded-circle me-3"><i class="bi bi-map fs-3 text-info"></i></div>
                            <div><h3 class="fw-bold mb-0 text-white"><?= $total_gunung ?></h3><small class="text-muted">Total Gunung</small></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="glass-card p-4 d-flex align-items-center">
                            <div class="bg-warning bg-opacity-25 p-3 rounded-circle me-3"><i class="bi bi-signpost-split fs-3 text-warning"></i></div>
                            <div><h3 class="fw-bold mb-0 text-white"><?= $total_jalur ?></h3><small class="text-muted">Total Jalur</small></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="glass-card p-4 d-flex align-items-center">
                            <div class="bg-success bg-opacity-25 p-3 rounded-circle me-3"><i class="bi bi-people fs-3 text-success"></i></div>
                            <div><h3 class="fw-bold mb-0 text-white"><?= $total_user ?></h3><small class="text-muted">User Terdaftar</small></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mountains Tab -->
            <div class="tab-pane fade" id="mountains">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold text-white">Manajemen Gunung</h2>
                    <!-- Link ke file tambah_gunung.php yang sudah kamu buat sebelumnya -->
                    <a href="tambah_gunung.php" class="btn btn-info fw-bold">+ Tambah Gunung</a>
                </div>
                <div class="glass-card p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-glass mb-0 text-white">
                            <thead><tr><th>Nama</th><th>Lokasi</th><th>Elevasi</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php foreach($gunungs as $g): ?>
                                <tr>
                                    <td class="fw-bold"><?= $g['name'] ?></td>
                                    <td><?= $g['province'] ?></td>
                                    <td><?= $g['elevation'] ?> mdpl</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-danger">Hapus</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Trails Tab -->
            <div class="tab-pane fade" id="trails">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold text-white">Manajemen Jalur</h2>
                    <a href="tambah_jalur.php" class="btn btn-info fw-bold">+ Tambah Jalur</a>
                </div>
                <div class="glass-card p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-glass mb-0 text-white">
                            <thead><tr><th>Nama Jalur</th><th>Gunung</th><th>Tipe</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach($jalurs as $j): ?>
                                <tr>
                                    <td class="fw-bold"><?= $j['name'] ?></td>
                                    <td><?= $j['mountain_name'] ?></td>
                                    <td>
                                        <?php if($j['is_online_booking']): ?>
                                            <span class="badge bg-primary">Online</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Offline</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $j['status'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>