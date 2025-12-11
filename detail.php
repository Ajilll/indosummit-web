<?php
session_start();
require_once 'config/database.php';

if (!isset($_GET['id'])) { header("Location: index.php"); exit(); }
$id = $_GET['id'];

// 1. PROSES KIRIM REVIEW
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Harap login untuk menulis ulasan!'); window.location='login.php';</script>";
        exit();
    }
    
    $u_id = $_SESSION['user_id'];
    $rating = $_POST['rating'];
    $comment = htmlspecialchars($_POST['comment']);

    // Cek apakah user sudah pernah review gunung ini? (Satu user satu review per gunung)
    $cek = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND mountain_id = ?");
    $cek->execute([$u_id, $id]);

    if ($cek->rowCount() > 0) {
        echo "<script>alert('Kamu sudah pernah mengulas gunung ini!');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, mountain_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$u_id, $id, $rating, $comment]);
        // Refresh halaman agar ulasan muncul
        header("Location: detail.php?id=$id");
        exit();
    }
}

// 2. AMBIL DATA GUNUNG
$stmt = $conn->prepare("SELECT * FROM mountains WHERE id = ?");
$stmt->execute([$id]);
$m = $stmt->fetch();
if (!$m) die("Gunung tidak ditemukan.");

// 3. AMBIL JALUR
$stmt_routes = $conn->prepare("SELECT * FROM hiking_routes WHERE mountain_id = ?");
$stmt_routes->execute([$id]);
$routes = $stmt_routes->fetchAll();

// 4. AMBIL REVIEW + DATA USERNYA
$stmt_rev = $conn->prepare("
    SELECT r.*, u.name, u.avatar, u.id as user_id 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.mountain_id = ? 
    ORDER BY r.created_at DESC
");
$stmt_rev->execute([$id]);
$reviews = $stmt_rev->fetchAll();

// Hitung Rating Rata-rata
$avg_rating = 0;
if (count($reviews) > 0) {
    $total_rating = 0;
    foreach($reviews as $r) $total_rating += $r['rating'];
    $avg_rating = round($total_rating / count($reviews), 1);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail <?= $m['name'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="ambient-glow"></div>

    <!-- NAVBAR SEDERHANA -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-filter-left"></i> INDOSUMMIT</a>
            <div class="ms-auto">
                <a class="btn btn-sm btn-outline-light" href="index.php">Kembali</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding-top: 100px;">
        <!-- HEADER GUNUNG -->
        <div class="row g-5 mb-5">
            <div class="col-lg-8">
                <div class="position-relative mb-4">
                    <img src="assets/img/<?= $m['image_url'] ?>" class="detail-header-img w-100 rounded" style="height:400px; object-fit:cover;">
                    <div class="position-absolute bottom-0 start-0 p-3">
                        <h1 class="fw-bold display-5 text-white" style="text-shadow: 2px 2px 4px black;"><?= $m['name'] ?></h1>
                        <p class="text-white mb-0">
                            <i class="bi bi-geo-alt-fill text-danger"></i> <?= $m['province'] ?> | 
                            <i class="bi bi-star-fill text-warning"></i> <?= $avg_rating ?>/5.0
                        </p>
                    </div>
                </div>
                
                <div class="glass-card mb-4">
                    <h4 class="text-info">Deskripsi</h4>
                    <p class="text-muted"><?= nl2br($m['description']) ?></p>
                </div>

                <!-- BAGIAN KOMENTAR / ULASAN -->
                <div class="glass-card mb-4">
                    <h4 class="text-info mb-4">Ulasan Pendaki (<?= count($reviews) ?>)</h4>

                    <!-- FORM INPUT ULASAN -->
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="card bg-dark border-secondary p-3 mb-4">
                            <h6 class="text-white">Bagikan pengalamanmu</h6>
                            <form method="POST">
                                <div class="mb-2">
                                    <select name="rating" class="form-select form-select-sm bg-dark text-white border-secondary" style="width: 150px;">
                                        <option value="5">⭐⭐⭐⭐⭐ (5.0)</option>
                                        <option value="4">⭐⭐⭐⭐ (4.0)</option>
                                        <option value="3">⭐⭐⭐ (3.0)</option>
                                        <option value="2">⭐⭐ (2.0)</option>
                                        <option value="1">⭐ (1.0)</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <textarea name="comment" class="form-control bg-dark text-white border-secondary" rows="2" placeholder="Tulis cerita singkatmu disini..." required></textarea>
                                </div>
                                <button type="submit" name="submit_review" class="btn btn-info btn-sm fw-bold">Kirim Ulasan</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary">Silakan <a href="login.php">Login</a> untuk menulis ulasan.</div>
                    <?php endif; ?>

                    <!-- LIST ULASAN -->
                    <?php foreach($reviews as $rev): ?>
                    <div class="border-bottom border-secondary pb-3 mb-3">
                        <div class="d-flex justify-content-between">
                            <div class="d-flex align-items-center">
                                <a href="public_profile.php?id=<?= $rev['user_id'] ?>" class="text-decoration-none">
                                    <img src="<?= $rev['avatar'] ?>" class="rounded-circle me-2" width="40" height="40" style="object-fit:cover;">
                                </a>
                                <div>
                                    <!-- NAMA BISA DIKLIK -->
                                    <a href="public_profile.php?id=<?= $rev['user_id'] ?>" class="fw-bold text-white text-decoration-none">
                                        <?= $rev['name'] ?>
                                    </a>
                                    <div class="text-warning small">
                                        <?php for($i=0; $i<$rev['rating']; $i++) echo "★"; ?>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted"><?= date('d M Y', strtotime($rev['created_at'])) ?></small>
                        </div>
                        <p class="text-white-50 mt-2 mb-0 small"><?= $rev['comment'] ?></p>
                    </div>
                    <?php endforeach; ?>

                </div>
            </div>

            <!-- SIDEBAR JALUR -->
            <!-- SIDEBAR: PILIHAN JALUR -->
            <div class="col-lg-4">
                <div class="glass-card sticky-top" style="top: 100px; border: 1px solid rgba(0, 194, 255, 0.3);">
                    <h5 class="mb-3 fw-bold text-white"><i class="bi bi-signpost-split"></i> Pilih Jalur</h5>
                    <p class="text-muted small mb-4">Klik jalur untuk melihat detail lengkap.</p>

                    <div class="list-group">
                        <?php if(count($routes) == 0): ?>
                            <div class="alert alert-warning text-dark">Belum ada data jalur.</div>
                        <?php endif; ?>

                        <?php foreach($routes as $r): ?>
                        <!-- Ganti div menjadi tag <a> agar seluruh kotak bisa diklik -->
                        <a href="detail_jalur.php?id=<?= $r['id'] ?>" class="list-group-item list-group-item-glass p-3 mb-2 text-decoration-none action-hover">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold text-white"><?= $r['name'] ?></h6>
                                <i class="bi bi-chevron-right text-white-50"></i>
                            </div>
                            
                            <div class="d-flex gap-2 mt-2">
                                <span class="badge bg-secondary font-monospace"><?= $r['difficulty'] ?></span>
                                <span class="badge bg-dark border border-secondary"><?= $r['est_time'] ?></span>
                            </div>

                            <div class="mt-2 text-info small">
                                <?php if($r['status'] == 'Tutup'): ?>
                                    <span class="text-danger"><i class="bi bi-x-circle"></i> Tutup</span>
                                <?php else: ?>
                                    <?php if($r['is_online_booking']): ?>
                                        <i class="bi bi-globe"></i> Booking Online
                                    <?php else: ?>
                                        <i class="bi bi-geo-alt"></i> Daftar di Basecamp
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <style>
                .action-hover { transition: all 0.2s; border-left: 3px solid transparent; }
                .action-hover:hover { 
                    background: rgba(255,255,255,0.1); 
                    border-left: 3px solid #0dcaf0; /* Warna biru muda */
                    padding-left: 1.5rem !important;
                }
            </style>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>