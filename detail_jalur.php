<?php
session_start();
require_once 'config/database.php';

if (!isset($_GET['id'])) { header("Location: index.php"); exit(); }
$id = $_GET['id'];

// Ambil Data Jalur JOIN dengan Gunung
$stmt = $conn->prepare("
    SELECT r.*, m.name as mountain_name, m.image_url, m.province 
    FROM hiking_routes r 
    JOIN mountains m ON r.mountain_id = m.id 
    WHERE r.id = ?
");
$stmt->execute([$id]);
$route = $stmt->fetch();

if (!$route) die("Jalur tidak ditemukan.");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jalur <?= $route['name'] ?> - <?= $route['mountain_name'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="ambient-glow"></div>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">INDOSUMMIT</a>
            <div class="ms-auto">
                <!-- Tombol kembali ke detail gunung -->
                <a class="btn btn-sm btn-outline-light" href="detail.php?id=<?= $route['mountain_id'] ?>">
                    <i class="bi bi-arrow-left"></i> Kembali ke <?= $route['mountain_name'] ?>
                </a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding-top: 100px; padding-bottom: 50px;">
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- HEADER GAMBAR GUNUNG -->
                <div class="position-relative mb-4 rounded overflow-hidden shadow">
                    <img src="assets/img/<?= $route['image_url'] ?>" class="w-100" style="height: 250px; object-fit: cover; filter: brightness(0.6);">
                    <div class="position-absolute top-50 start-50 translate-middle text-center w-100">
                        <span class="badge bg-info text-dark mb-2"><?= $route['mountain_name'] ?></span>
                        <h1 class="fw-bold text-white display-5"><?= $route['name'] ?></h1>
                        <p class="text-white-50"><?= $route['basecamp_info'] ? $route['basecamp_info'] : 'Lokasi Basecamp Standar' ?></p>
                    </div>
                </div>

                <!-- INFO STATUS -->
                <?php if($route['status'] == 'Tutup'): ?>
                    <div class="alert alert-danger text-center fw-bold">
                        <i class="bi bi-exclamation-octagon-fill"></i> JALUR INI SEDANG DITUTUP
                    </div>
                <?php endif; ?>

                <!-- GRID INFORMASI -->
                <div class="glass-card p-4 mb-4">
                    <h4 class="text-info mb-4 border-bottom border-secondary pb-2">Informasi Jalur</h4>
                    
                    <div class="row g-4 text-center">
                        <div class="col-6 col-md-3">
                            <div class="p-3 border border-secondary rounded bg-dark bg-opacity-25">
                                <i class="bi bi-bar-chart-steps fs-2 text-warning"></i>
                                <div class="text-white-50 small mt-2">Kesulitan</div>
                                <div class="fw-bold text-white"><?= $route['difficulty'] ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 border border-secondary rounded bg-dark bg-opacity-25">
                                <i class="bi bi-clock-history fs-2 text-info"></i>
                                <div class="text-white-50 small mt-2">Estimasi Waktu</div>
                                <div class="fw-bold text-white"><?= $route['est_time'] ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 border border-secondary rounded bg-dark bg-opacity-25">
                                <i class="bi bi-arrows-expand fs-2 text-success"></i>
                                <div class="text-white-50 small mt-2">Panjang Jalur</div>
                                <div class="fw-bold text-white"><?= $route['route_length'] ? $route['route_length'] : '-' ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 border border-secondary rounded bg-dark bg-opacity-25">
                                <i class="bi bi-graph-up-arrow fs-2 text-danger"></i>
                                <div class="text-white-50 small mt-2">Kemiringan</div>
                                <div class="fw-bold text-white"><?= $route['slope'] ? $route['slope'] : '-' ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5 class="text-white">Detail Basecamp</h5>
                        <p class="text-muted">
                            Lokasi Basecamp berada di <b><?= $route['basecamp_info'] ? $route['basecamp_info'] : 'area kaki gunung' ?></b>. 
                            Pastikan Anda melapor ke petugas basecamp sebelum memulai pendakian untuk pemeriksaan simaksi dan perlengkapan.
                        </p>
                    </div>
                </div>

                <!-- TOMBOL BOOKING (ACTION) -->
                <div class="glass-card p-4 text-center">
                    <h5 class="text-white mb-3">Siap Mendaki?</h5>
                    
                    <?php if($route['status'] == 'Tutup'): ?>
                        <button class="btn btn-secondary btn-lg w-100" disabled>Pendakian Ditutup</button>
                    <?php else: ?>
                        <?php if($route['is_online_booking']): ?>
                            <p class="text-muted small mb-3">Jalur ini mewajibkan booking online melalui website resmi.</p>
                            <a href="<?= $route['booking_url'] ?>" target="_blank" class="btn btn-info btn-lg w-100 fw-bold shadow">
                                <i class="bi bi-ticket-perforated"></i> BOOKING TIKET ONLINE SEKARANG
                            </a>
                            <small class="text-muted mt-2 d-block">*Anda akan diarahkan ke website resmi pengelola.</small>
                        <?php else: ?>
                            <p class="text-muted small mb-3">Pendaftaran dilakukan secara langsung di tempat (On The Spot).</p>
                            <button class="btn btn-outline-light btn-lg w-100" disabled>
                                <i class="bi bi-house-door"></i> Registrasi di Basecamp
                            </button>
                            <small class="text-info mt-2 d-block fw-bold">Silakan datang langsung ke alamat basecamp diatas.</small>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>