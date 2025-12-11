<?php
session_start();
require_once '../config/database.php';

// CEK ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses Ditolak!'); window.location='../login.php';</script>";
    exit();
}

// 1. STATISTIK
$total_gunung = $conn->query("SELECT COUNT(*) FROM mountains")->fetchColumn();
$total_jalur = $conn->query("SELECT COUNT(*) FROM hiking_routes")->fetchColumn();
$total_user = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();

// 2. AMBIL DATA
$gunungs = $conn->query("SELECT * FROM mountains ORDER BY id DESC")->fetchAll();
$jalurs = $conn->query("SELECT hiking_routes.*, mountains.name as mountain_name FROM hiking_routes JOIN mountains ON hiking_routes.mountain_id = mountains.id ORDER BY id DESC")->fetchAll();
$users = $conn->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - IndoSummit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* CSS KHUSUS DASHBOARD DENGAN HEADER */
        
        /* 1. Turunkan body agar tidak tertutup Navbar Fixed */
        body {
            padding-top: 76px; 
        }

        /* 2. Style Khusus Navbar Admin */
        .navbar-admin {
            background-color: #050a14; /* Warna gelap sesuai tema */
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1040; /* Pastikan di atas sidebar */
        }

        /* 3. Sesuaikan Sidebar agar mulai DI BAWAH Navbar */
        .sidebar {
            top: 76px !important; /* Wajib !important untuk menimpa style bawaan */
            height: calc(100vh - 76px); /* Kurangi tinggi navbar agar scroll pas */
            position: fixed;
            left: 0;
            width: 250px;
            background: #050a14;
            border-right: 1px solid rgba(255,255,255,0.1);
            overflow-y: auto;
        }

        /* 4. Pastikan konten utama ada margin kiri (karena sidebar fixed) */
        .main-content {
            margin-left: 250px; /* Lebar sidebar */
            padding: 2rem;
        }

        /* Responsif untuk HP */
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
        }
    </style>

    <script>
        function confirmDelete() {
            return confirm("Yakin ingin menghapus data ini? Data yang dihapus tidak bisa dikembalikan.");
        }
    </script>
</head>
<body>

    <!-- 1. NAVBAR GLOBAL (HEADER) -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-admin">
        <div class="container-fluid px-4">
            <!-- Brand / Logo -->
            <a class="navbar-brand d-flex align-items-center gap-2" href="../index.php">
                <i class="bi bi-filter-left fs-3"></i> 
                <span>IndoSummit <span class="badge bg-warning text-dark ms-2" style="font-size: 0.6rem;">ADMIN</span></span>
            </a>
            
            <!-- Menu Kanan -->
            <div class="d-flex ms-auto align-items-center gap-3">
                <div class="d-none d-md-block text-white small text-end">
                    <div class="fw-bold"><?= $_SESSION['name'] ?></div>
                    <div class="text-white-50" style="font-size: 0.75rem;">Administrator</div>
                </div>
                <img src="<?= $_SESSION['avatar'] ?>" class="rounded-circle border border-secondary" width="40" height="40">
                
                <!-- TOMBOL KEMBALI KE HOME -->
                <a href="../index.php" class="btn btn-outline-info btn-sm d-flex align-items-center gap-2">
                    <i class="bi bi-house-door-fill"></i> <span class="d-none d-sm-inline">Ke Website Utama</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- 2. SIDEBAR -->
    <nav class="sidebar d-none d-md-block">
        <div class="p-4 border-bottom border-secondary border-opacity-25">
            <h6 class="text-white-50 text-uppercase small ls-1">Menu Utama</h6>
        </div>
        <ul class="nav flex-column mt-3 nav-pills px-2" id="adminTabs" role="tablist">
            <li class="nav-item mb-1"><button class="nav-link active w-100 text-start text-white" data-bs-toggle="tab" data-bs-target="#dashboard"><i class="bi bi-speedometer2 me-2"></i> Dashboard</button></li>
            <li class="nav-item mb-1"><button class="nav-link w-100 text-start text-white" data-bs-toggle="tab" data-bs-target="#mountains"><i class="bi bi-map me-2"></i> Data Gunung</button></li>
            <li class="nav-item mb-1"><button class="nav-link w-100 text-start text-white" data-bs-toggle="tab" data-bs-target="#trails"><i class="bi bi-signpost-split me-2"></i> Data Jalur</button></li>
            <li class="nav-item mb-1"><button class="nav-link w-100 text-start text-white" data-bs-toggle="tab" data-bs-target="#users"><i class="bi bi-people me-2"></i> Data User</button></li>
        </ul>
        <div class="p-4 mt-auto">
            <a href="../logout.php" class="btn btn-danger w-100 btn-sm d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>
        </div>
    </nav>

    <!-- 3. MAIN CONTENT -->
    <main class="main-content">
        <div class="container-fluid tab-content">
            
            <!-- TAB DASHBOARD -->
            <div class="tab-pane fade show active" id="dashboard">
                <h2 class="fw-bold text-white mb-4">Dashboard Overview</h2>
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

            <!-- TAB MOUNTAINS -->
            <div class="tab-pane fade" id="mountains">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold text-white">Manajemen Gunung</h2>
                    <a href="tambah_gunung.php" class="btn btn-info fw-bold">+ Tambah Gunung</a>
                </div>
                <div class="glass-card p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-glass mb-0 text-white align-middle">
                            <thead class="bg-dark bg-opacity-50"><tr><th>Gunung</th><th>Lokasi</th><th>Elevasi</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php foreach($gunungs as $g): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../assets/img/<?= $g['image_url'] ?>" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            <span class="fw-bold"><?= $g['name'] ?></span>
                                        </div>
                                    </td>
                                    <td><?= $g['province'] ?></td>
                                    <td><?= $g['elevation'] ?> mdpl</td>
                                    <td>
                                        <a href="edit_gunung.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                        <a href="hapus_gunung.php?id=<?= $g['id'] ?>" onclick="return confirmDelete()" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB TRAILS -->
            <div class="tab-pane fade" id="trails">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold text-white">Manajemen Jalur</h2>
                    <a href="tambah_jalur.php" class="btn btn-info fw-bold">+ Tambah Jalur</a>
                </div>
                <div class="glass-card p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-glass mb-0 text-white align-middle">
                            <thead class="bg-dark bg-opacity-50"><tr><th>Nama Jalur</th><th>Gunung</th><th>Tipe</th><th>Status</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php foreach($jalurs as $j): ?>
                                <tr>
                                    <td class="fw-bold"><?= $j['name'] ?></td>
                                    <td><?= $j['mountain_name'] ?></td>
                                    <td>
                                        <?= $j['is_online_booking'] ? '<span class="badge bg-primary">Online</span>' : '<span class="badge bg-secondary">Offline</span>' ?>
                                    </td>
                                    <td>
                                        <?php if($j['status'] == 'Buka'): ?>
                                            <span class="badge bg-success">Buka</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Tutup</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit_jalur.php?id=<?= $j['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                        <a href="hapus_jalur.php?id=<?= $j['id'] ?>" onclick="return confirmDelete()" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB USERS -->
            <div class="tab-pane fade" id="users">
                <h2 class="fw-bold text-white mb-4">Manajemen User</h2>
                <div class="glass-card p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-glass mb-0 text-white align-middle">
                            <thead class="bg-dark bg-opacity-50"><tr><th>User</th><th>Email</th><th>Role</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php foreach($users as $u): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= $u['avatar'] ?>" class="rounded-circle me-2" width="30" height="30">
                                            <?= $u['name'] ?>
                                        </div>
                                    </td>
                                    <td><?= $u['email'] ?></td>
                                    <td>
                                        <?php if($u['role'] == 'admin'): ?>
                                            <span class="badge bg-warning text-dark">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                                            <?php if($u['role'] == 'user'): ?>
                                                <a href="user_action.php?id=<?= $u['id'] ?>&action=promote" onclick="return confirm('Jadikan Admin?')" class="btn btn-sm btn-success" title="Jadikan Admin"><i class="bi bi-arrow-up-circle"></i></a>
                                            <?php else: ?>
                                                <a href="user_action.php?id=<?= $u['id'] ?>&action=demote" onclick="return confirm('Hapus akses Admin?')" class="btn btn-sm btn-outline-warning" title="Turunkan ke User"><i class="bi bi-arrow-down-circle"></i></a>
                                            <?php endif; ?>
                                            <a href="user_action.php?id=<?= $u['id'] ?>&action=delete" onclick="return confirmDelete()" class="btn btn-sm btn-danger" title="Hapus User"><i class="bi bi-trash"></i></a>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">Akun Anda</span>
                                        <?php endif; ?>
                                    </td>
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