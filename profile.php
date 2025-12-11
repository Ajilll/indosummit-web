<?php
session_start();
require_once 'config/database.php';

// 1. CEK LOGIN (Wajib Login)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. LOGIKA UPDATE PROFIL
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = strip_tags($_POST['name']);
    $phone = strip_tags($_POST['phone']); // Opsional jika mau nambah no hp

    // Update data
    $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
    $stmt->execute([$name, $user_id]);
    
    // Update session
    $_SESSION['name'] = $name;
    $success_msg = "Profil berhasil diperbarui!";
}

// 3. LOGIKA HAPUS REVIEW (Hapus Jejak)
if (isset($_GET['action']) && $_GET['action'] == 'delete_review' && isset($_GET['id'])) {
    $review_id = $_GET['id'];
    
    // Hapus hanya punya user sendiri
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
    $stmt->execute([$review_id, $user_id]);
    
    header("Location: profile.php?msg=deleted");
    exit();
}

// 4. AMBIL DATA USER
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 5. AMBIL DATA HISTORY (Berdasarkan Review yang dibuat)
$sql_history = "SELECT r.id as review_id, m.id as mountain_id, m.name, m.province, m.image_url, r.rating, r.comment, r.created_at 
                FROM reviews r 
                JOIN mountains m ON r.mountain_id = m.id 
                WHERE r.user_id = ? 
                ORDER BY r.created_at DESC";
$stmt_h = $conn->prepare($sql_history);
$stmt_h->execute([$user_id]);
$my_history = $stmt_h->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya - IndoSummit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="ambient-glow"></div>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid px-lg-5"> 
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <i class="bi bi-filter-left fs-3"></i> 
                <span>IndoSummit</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#explore">Explore</a></li>
                </ul>

                <div class="d-flex gap-2 mt-3 mt-lg-0 align-items-center">
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="<?= $user['avatar'] ?>" alt="User" class="rounded-circle me-2" style="width: 35px; height: 35px; object-fit: cover;">
                            <span class="small fw-bold"><?= explode(' ', $user['name'])[0] ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end bg-dark border-secondary">
                            <li><a class="dropdown-item text-white active" href="profile.php">Profil Saya</a></li>
                            <li><hr class="dropdown-divider bg-secondary"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <div class="container" style="padding-top: 100px;">
        <h2 class="fw-bold text-white mb-4">Dashboard Saya</h2>
        
        <?php if(isset($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $success_msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                Ulasan berhasil dihapus dari riwayat.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- SIDEBAR KIRI -->
            <div class="col-lg-3 mb-4">
                <div class="glass-card p-0 mb-3">
                    <div class="p-3 text-center border-bottom border-secondary">
                        <img src="<?= $user['avatar'] ?>" class="rounded-circle mb-2" width="80" height="80">
                        <h6 class="text-white fw-bold mb-0"><?= $user['name'] ?></h6>
                        <small class="text-muted"><?= $user['email'] ?></small>
                        <br>
                        <a href="public_profile.php?id=<?= $user['id'] ?>" class="btn btn-outline-info btn-sm mt-3 w-100">
                            <i class="bi bi-eye"></i> Lihat Tampilan Publik
                        </a>
                    </div>
                    <div class="list-group list-group-flush rounded-bottom">
                        <button class="list-group-item list-group-item-action bg-transparent text-white active" data-bs-toggle="tab" data-bs-target="#riwayat">
                            <i class="bi bi-signpost-2 me-2"></i> Jejak Pendakian
                        </button>
                        <button class="list-group-item list-group-item-action bg-transparent text-white" data-bs-toggle="tab" data-bs-target="#settings">
                            <i class="bi bi-gear me-2"></i> Pengaturan Akun
                        </button>
                    </div>
                </div>
            </div>

            <!-- KONTEN KANAN -->
            <div class="col-lg-9">
                <div class="tab-content">
                    
                    <!-- TAB 1: RIWAYAT / HISTORY -->
                    <div class="tab-pane fade show active" id="riwayat">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="text-white mb-0">Gunung yang Telah Saya Daki</h5>
                            <span class="badge bg-success"><?= count($my_history) ?> Gunung</span>
                        </div>
                        
                        <?php if(count($my_history) > 0): ?>
                            <?php foreach($my_history as $h): ?>
                                <div class="glass-card p-3 mb-3">
                                    <div class="d-flex align-items-start">
                                        <!-- Gambar Gunung -->
                                        <img src="assets/img/<?= $h['image_url'] ?>" class="rounded me-3" width="90" height="70" style="object-fit: cover;">
                                        
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <h5 class="fw-bold text-white mb-1">
                                                    <a href="detail.php?id=<?= $h['mountain_id'] ?>" class="text-white text-decoration-none">
                                                        <?= $h['name'] ?>
                                                    </a>
                                                </h5>
                                                <small class="text-white-50"><?= date('d M Y', strtotime($h['created_at'])) ?></small>
                                            </div>
                                            
                                            <p class="text-muted small mb-2"><?= $h['province'] ?></p>
                                            
                                            <div class="bg-dark bg-opacity-50 p-2 rounded">
                                                <div class="text-warning small mb-1">
                                                    Penilaian Anda: 
                                                    <?php for($i=0; $i<$h['rating']; $i++) echo "★"; ?> 
                                                    (<?= $h['rating'] ?>/5)
                                                </div>
                                                <p class="text-white-50 small mb-0 fst-italic">"<?= $h['comment'] ?>"</p>
                                            </div>
                                        </div>

                                        <!-- Tombol Hapus -->
                                        <div class="ms-3">
                                            <a href="profile.php?action=delete_review&id=<?= $h['review_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus ulasan ini? Data akan hilang dari jejak pendakian.')" title="Hapus Ulasan">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="glass-card p-5 text-center text-muted">
                                <i class="bi bi-flag fs-1 d-block mb-3"></i>
                                <h5>Belum ada jejak pendakian.</h5>
                                <p class="small">Mulailah memberi ulasan pada gunung yang pernah kamu daki.</p>
                                <a href="index.php" class="btn btn-info btn-sm mt-2">Cari Gunung</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- TAB 2: PENGATURAN -->
                    <div class="tab-pane fade" id="settings">
                        <div class="glass-card">
                            <h5 class="mb-4 text-white">Edit Profil</h5>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="text-white-50 small mb-1">Nama Lengkap</label>
                                    <input type="text" name="name" class="form-control form-control-glass text-white" value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="text-white-50 small mb-1">Email (Akun Google)</label>
                                    <input type="email" class="form-control form-control-glass text-muted" value="<?= $user['email'] ?>" readonly>
                                    <small class="text-muted" style="font-size: 0.7rem;">*Email tidak dapat diubah karena terhubung dengan Google.</small>
                                </div>
                                <div class="mb-4">
                                    <label class="text-white-50 small mb-1">Status Akun</label> <br>
                                    <span class="badge bg-secondary"><?= strtoupper($user['role']) ?></span>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-info fw-bold w-100">Simpan Perubahan</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <footer class="footer-section mt-5 pt-5 pb-3">
        <div class="container text-center">
            <div class="border-top border-secondary pt-3 mt-3 small text-muted">
                &copy; 2026 IndoSummit.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>