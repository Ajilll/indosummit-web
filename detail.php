<?php
session_start();
require_once 'config/database.php';

// 1. Cek ID di URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// 2. Ambil Data Gunung
$stmt = $conn->prepare("SELECT * FROM mountains WHERE id = ?");
$stmt->execute([$id]);
$m = $stmt->fetch();

if (!$m) die("Gunung tidak ditemukan.");

// 3. Ambil Data Jalur
$stmt_routes = $conn->prepare("SELECT * FROM hiking_routes WHERE mountain_id = ?");
$stmt_routes->execute([$id]);
$routes = $stmt_routes->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail <?= $m['name'] ?> - IndoSummit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="ambient-glow"></div>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-filter-left"></i> INDOSUMMIT</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-arrow-left"></i> Kembali</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="padding-top: 100px;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-info text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-white-50" aria-current="page"><?= $m['name'] ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <!-- KOLOM KIRI: INFO GUNUNG -->
            <div class="col-lg-8">
                <div class="position-relative mb-4">
                    <!-- Foto dari Database -->
                    <img src="assets/img/<?= $m['image_url'] ?>" class="detail-header-img" alt="<?= $m['name'] ?>" style="width:100%; height:400px; object-fit:cover; border-radius:15px;">
                    <div class="position-absolute bottom-0 start-0 p-3">
                        <span class="badge bg-info text-dark mb-2"><?= $m['province'] ?></span>
                        <h1 class="fw-bold display-5 text-white" style="text-shadow: 2px 2px 4px black;"><?= $m['name'] ?></h1>
                        <p class="text-white mb-0"><i class="bi bi-bar-chart-fill text-warning"></i> <?= $m['elevation'] ?> mdpl</p>
                    </div>
                </div>

                <div class="glass-card mb-4">
                    <h4 class="mb-3 text-info">Tentang Gunung</h4>
                    <p class="text-muted leading-relaxed">
                        <?= nl2br($m['description']) ?>
                    </p>
                    
                    <div class="row mt-4 text-center">
                        <div class="col-4 border-end border-secondary">
                            <h5 class="fw-bold mb-0 text-white"><?= count($routes) ?></h5><small class="text-muted">Total Jalur</small>
                        </div>
                        <div class="col-4 border-end border-secondary">
                            <h5 class="fw-bold mb-0 text-white">Buka</h5><small class="text-muted">Status</small>
                        </div>
                        <div class="col-4">
                            <h5 class="fw-bold mb-0 text-white">4.8</h5><small class="text-muted">Rating</small>
                        </div>
                    </div>
                </div>

                <!-- Map Section -->
                <?php if($m['map_url']): ?>
                <div class="glass-card mb-4">
                    <h4 class="mb-3 text-info">Lokasi Gunung</h4>
                    <div style="width: 100%; height: 400px; border-radius: 10px; overflow: hidden;">
                         <iframe src="<?= $m['map_url'] ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- KOLOM KANAN: PILIHAN JALUR (STICKY) -->
            <div class="col-lg-4">
                <div class="glass-card sticky-top" style="top: 100px; border: 1px solid rgba(0, 194, 255, 0.3);">
                    <h5 class="mb-3 fw-bold text-white"><i class="bi bi-signpost-split"></i> Pilih Jalur</h5>
                    <p class="text-muted small mb-4">Pilih jalur untuk akses booking resmi.</p>

                    <div class="list-group">
                        <?php if(count($routes) == 0): ?>
                            <div class="alert alert-warning text-dark">Belum ada data jalur.</div>
                        <?php endif; ?>

                        <!-- LOOPING DATA JALUR -->
                        <?php foreach($routes as $r): ?>
                        <div class="list-group-item list-group-item-glass p-3 mb-2">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-0 fw-bold text-white"><?= $r['name'] ?></h6>
                                <?php if($r['status'] == 'Tutup'): ?>
                                    <span class="badge bg-danger">Tutup</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Buka</span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="mb-2 small text-white-50">
                                Kesulitan: <?= $r['difficulty'] ?> • <?= $r['est_time'] ?>
                            </p>
                            
                            <!-- LOGIKA TOMBOL BOOKING -->
                            <?php if($r['status'] == 'Tutup'): ?>
                                <button disabled class="btn btn-secondary w-100 btn-sm">Jalur Ditutup</button>
                            <?php else: ?>
                                <?php if($r['is_online_booking']): ?>
                                    <!-- Jika Online -->
                                    <a href="<?= $r['booking_url'] ?>" target="_blank" class="btn btn-info w-100 btn-sm fw-bold">
                                        Booking Online <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                <?php else: ?>
                                    <!-- Jika Offline (Panggil Modal) -->
                                    <button class="btn btn-outline-light w-100 btn-sm" data-bs-toggle="modal" data-bs-target="#offlineModal<?= $r['id'] ?>">
                                        Info Basecamp <i class="bi bi-info-circle"></i>
                                    </button>

                                    <!-- MODAL KHUSUS PER JALUR -->
                                    <div class="modal fade" id="offlineModal<?= $r['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-dark">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Info Basecamp - <?= $r['name'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-warning text-dark">
                                                        <i class="bi bi-exclamation-triangle"></i> Jalur ini menggunakan sistem <b>On The Spot (Offline)</b>.
                                                    </div>
                                                    <p>Silakan daftar langsung di basecamp:</p>
                                                    <ul>
                                                        <li>Bawa KTP Asli & Fotokopi</li>
                                                        <li>Surat Keterangan Sehat</li>
                                                        <li>Membayar Simaksi di Lokasi</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
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