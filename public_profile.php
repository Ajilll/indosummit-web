<?php
session_start();
require_once 'config/database.php';

if (!isset($_GET['id'])) { header("Location: index.php"); exit(); }
$user_id = $_GET['id'];

// 1. AMBIL DATA USER (Hanya Nama & Avatar, jangan email demi privasi)
$stmt = $conn->prepare("SELECT name, avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();
if (!$profile) die("User tidak ditemukan.");

// 2. AMBIL JEJAK PENDAKIAN (Berdasarkan Review yang pernah dia buat)
// Kita gunakan DISTINCT agar jika dia review 2x di gunung sama, munculnya cuma 1x
$sql = "SELECT DISTINCT m.id, m.name, m.image_url, m.province, r.rating, r.created_at 
        FROM reviews r 
        JOIN mountains m ON r.mountain_id = m.id 
        WHERE r.user_id = ? 
        ORDER BY r.created_at DESC";
$stmt_hist = $conn->prepare($sql);
$stmt_hist->execute([$user_id]);
$history = $stmt_hist->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil <?= $profile['name'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="ambient-glow"></div>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">INDOSUMMIT</a>
            <div class="ms-auto"><a class="btn btn-sm btn-outline-light" href="index.php">Home</a></div>
        </div>
    </nav>

    <div class="container" style="padding-top: 100px;">
        <!-- HEADER PROFIL -->
        <div class="text-center mb-5">
            <img src="<?= $profile['avatar'] ?>" class="rounded-circle border border-3 border-info shadow" width="120" height="120">
            <h2 class="text-white fw-bold mt-3"><?= $profile['name'] ?></h2>
            <p class="text-muted">Jejak Petualang</p>
            <span class="badge bg-success fs-6"><?= count($history) ?> Gunung Didaki</span>
        </div>

        <!-- LIST GUNUNG YANG SUDAH DIDAKI -->
        <h4 class="text-white mb-4 border-bottom border-secondary pb-2">Riwayat Pendakian</h4>
        
        <div class="row g-4">
            <?php if(count($history) > 0): ?>
                <?php foreach($history as $h): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card p-3 d-flex align-items-center h-100">
                        <img src="assets/img/<?= $h['image_url'] ?>" class="rounded me-3" width="80" height="80" style="object-fit:cover;">
                        <div>
                            <h5 class="fw-bold text-white mb-1">
                                <a href="detail.php?id=<?= $h['id'] ?>" class="text-white text-decoration-none"><?= $h['name'] ?></a>
                            </h5>
                            <small class="text-muted d-block"><?= $h['province'] ?></small>
                            <div class="text-warning small mt-1">
                                Rated: <b><?= $h['rating'] ?>.0</b> <span class="text-white-50 ms-1" style="font-size:0.7rem;">(<?= date('M Y', strtotime($h['created_at'])) ?>)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted py-5">
                    User ini belum membagikan riwayat pendakiannya.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>