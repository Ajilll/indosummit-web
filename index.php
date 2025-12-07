<?php
// index.php
session_start();
require_once 'config/database.php';
require_once 'config/google_setup.php'; // Ambil variabel $google_login_url

// 1. QUERY UNTUK FITUR SEARCH (Jika user mencari sesuatu)
$keyword = isset($_GET['q']) ? $_GET['q'] : '';
$sql = "SELECT * FROM mountains";

if ($keyword) {
    $sql .= " WHERE name LIKE :keyword OR province LIKE :keyword";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['keyword' => "%$keyword%"]);
} else {
    // Default: Tampilkan semua (bisa dilimit misal LIMIT 6)
    $sql .= " ORDER BY id DESC";
    $stmt = $conn->query($sql);
}

$mountains = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IndoSummit - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="ambient-glow"></div>

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
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#explore">Explore</a></li>
                    <!-- Menu Admin hanya muncul jika role admin -->
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item"><a class="nav-link text-warning" href="admin/tambah_gunung.php">Admin Panel</a></li>
                    <?php endif; ?>
                </ul>

                <div class="d-flex gap-2 mt-3 mt-lg-0 align-items-center">
                    
                    <!-- LOGIKA LOGIN / USER PROFIL -->
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <!-- Jika SUDAH Login -->
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                                <img src="<?= $_SESSION['avatar'] ?>" alt="User" class="rounded-circle me-2" style="width: 35px; height: 35px; object-fit: cover;">
                                <span class="small fw-bold"><?= explode(' ', $_SESSION['name'])[0] ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end bg-dark border-secondary">
                                <li><a class="dropdown-item text-white" href="#">Profil Saya</a></li>
                                <li><hr class="dropdown-divider bg-secondary"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Jika BELUM Login -->
                        <a href="login.php" class="btn btn-nav-login text-decoration-none">Login</a>
                        <a href="register.php" class="btn btn-nav-register text-decoration-none">Register</a>
                    <?php endif; ?>

                </div>

            </div>
        </div>
    </nav>

    <section class="hero-section d-flex align-items-center justify-content-center">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <p class="text-info text-uppercase tracking-wide mb-2 small fw-bold">Information & Directory</p>
            
            <h1 class="hero-title">THE BEAUTY OF<br>INDONESIAN MOUNTAINS</h1>
            
            <p class="hero-subtitle mb-5" style="max-width: 650px; margin: 0 auto;">
                Temukan informasi jalur pendakian, status buka-tutup, dan akses booking resmi taman nasional dalam satu portal terintegrasi.
            </p>

            <div class="row justify-content-center mb-5">
                <div class="col-md-7 position-relative"> <!-- Tambah position-relative disini -->
        
                    <!-- FORM PENCARIAN -->
                    <form action="index.php" method="GET" autocomplete="off">
                        <div class="search-box-glass">
                            <i class="bi bi-search text-white me-3 fs-5"></i>
                
                            <!-- Tambahkan ID="searchInput" -->
                            <input type="text" id="searchInput" name="q" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari gunung (contoh: Semeru, Rinjani)...">
                
                            <button type="submit" class="search-btn-glass"><i class="bi bi-arrow-right"></i></button>
                        </div>
                     </form>

                    <!-- CONTAINER UNTUK HASIL LIVE SEARCH -->
                    <div id="searchResults" class="search-results-container">
                    <!-- Hasil pencarian akan muncul disini lewat Javascript -->
                    </div>

                </div>
            </div>

            <div class="row g-3 justify-content-center mt-4">
                <div class="col-md-3 col-6">
                    <div class="glass-card py-3 px-3 text-start">
                        <small class="text-muted d-block mb-1">Total Gunung</small>
                        <h5 class="mb-0 fw-bold text-white"><i class="bi bi-check-circle-fill text-success"></i> <?= count($mountains) ?> Data</h5>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="glass-card py-3 px-3 text-start">
                        <small class="text-muted d-block mb-1">Cuaca Rata-rata</small>
                        <h5 class="mb-0 fw-bold text-white"><i class="bi bi-cloud-sun text-warning"></i> Cerah</h5>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="explore" class="container py-5 position-relative">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <small class="text-info text-uppercase">Explore</small>
                <?php if($keyword): ?>
                    <h2 class="fw-bold display-6 text-white">Hasil Pencarian: "<?= htmlspecialchars($keyword) ?>"</h2>
                    <a href="index.php" class="btn btn-sm btn-outline-light mt-2">Reset Pencarian</a>
                <?php else: ?>
                    <h2 class="fw-bold display-6 text-white">Destinasi Populer</h2>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            
            <!-- LOOPING DATABASE MOUNTAINS -->
            <?php foreach($mountains as $m): ?>
            <div class="col-md-4">
                <div class="glass-card h-100 position-relative">
                    <!-- Gambar Dinamis dari Folder assets/img -->
                    <img src="assets/img/<?= $m['image_url'] ?>" class="card-content-img" alt="<?= $m['name'] ?>" style="object-fit: cover; height: 250px; width: 100%;">
                    
                    <div class="card-real-title mb-2 text-white"><?= $m['name'] ?></div>
                    <p class="text-muted small"><?= $m['province'] ?> • <?= $m['elevation'] ?> mdpl</p>
                    
                    <a href="detail.php?id=<?= $m['id'] ?>" class="text-info text-decoration-none small mt-3 d-inline-block">
                        Lihat Detail <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if(count($mountains) == 0): ?>
                <div class="col-12 text-center text-white">
                    <p class="py-5">Maaf, gunung yang kamu cari belum tersedia di database kami.</p>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <footer class="footer-section mt-5 pt-5 pb-3">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h4 class="text-white fw-bold mb-3">IndoSummit</h4>
                    <p class="text-muted small">
                        Platform direktori pendakian gunung terpercaya di Indonesia. Temukan informasi jalur, estimasi biaya, dan tips pendakian terbaik.
                    </p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-white"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-6 mb-4">
                    <h5 class="text-white mb-3">Menu</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="index.php" class="text-muted text-decoration-none">Beranda</a></li>
                        <li class="mb-2"><a href="#explore" class="text-muted text-decoration-none">Destinasi</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-6 mb-4">
                    <h5 class="text-white mb-3">Bantuan</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">FAQ</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Kontak Kami</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5 class="text-white mb-3">Newsletter</h5>
                    <p class="text-muted small">Dapatkan info jalur terbaru dan promo menarik.</p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control bg-transparent text-white border-secondary" placeholder="Email Anda" aria-label="Email Anda">
                        <button class="btn btn-primary" type="button">Langganan</button>
                    </div>
                </div>
            </div>
            <div class="border-top border-secondary pt-3 mt-3 text-center small text-muted">
                &copy; 2026 IndoSummit. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    
    <script>
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');

    // Event saat user mengetik
    searchInput.addEventListener('keyup', function() {
        let query = this.value;

        if (query.length > 1) { // Mulai cari jika lebih dari 1 huruf
            fetch('api/search.php?term=' + query)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    
                    if (data.length > 0) {
                        data.forEach(gunung => {
                            // Link langsung ke detail.php?id=...
                            html += `
                                <a href="detail.php?id=${gunung.id}" class="search-item">
                                    <img src="assets/img/${gunung.image_url}" class="search-thumb">
                                    <div>
                                        <div class="fw-bold text-dark">${gunung.name}</div>
                                        <small class="text-muted">${gunung.province}</small>
                                    </div>
                                </a>
                            `;
                        });
                    } else {
                        html = `<div class="p-3 text-muted text-center small">Gunung tidak ditemukan.</div>`;
                    }

                    searchResults.innerHTML = html;
                    searchResults.style.display = 'block';
                })
                .catch(err => console.error('Error:', err));
        } else {
            searchResults.style.display = 'none';
        }
    });

    // Sembunyikan hasil jika klik di luar area
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
</script>
</body>
</html>