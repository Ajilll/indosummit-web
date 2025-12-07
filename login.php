<?php
session_start();
require_once 'config/google_setup.php';

// Jika sudah login, lempar ke home
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - IndoSummit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid px-lg-5">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <i class="bi bi-filter-left fs-3"></i> <span>IndoSummit</span>
            </a>
            <div class="d-flex ms-auto">
                 <a href="index.php" class="btn btn-sm btn-outline-light">Kembali</a>
            </div>
        </div>
    </nav>

    <div class="hero-section d-flex align-items-center justify-content-center" style="min-height: 100vh; padding-top: 0;">
        <div class="hero-overlay" style="background: rgba(5, 10, 20, 0.8);"></div>
        <div class="container position-relative" style="z-index: 2;">
            <div class="row justify-content-center">
                <div class="col-md-5 col-lg-4">
                    
                    <div class="glass-card p-4 p-md-5 text-center">
                        <h4 class="fw-bold text-white mb-2">Welcome Back!</h4>
                        <p class="text-muted small mb-4">Masuk untuk mulai menjelajah.</p>

                        <!-- TOMBOL GOOGLE LOGIN -->
                        <a href="<?= $google_login_url ?>" class="btn btn-light w-100 fw-bold py-2 mb-3 d-flex align-items-center justify-content-center gap-2">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" width="20">
                            Masuk dengan Google
                        </a>

                        <div class="text-white-50 small mb-3">atau</div>

                        <!-- Form Login Biasa (Opsional, dummy dulu) -->
                        <form action="" method="POST">
                            <div class="mb-3 text-start">
                                <label class="text-white small mb-1">Email</label>
                                <input type="email" class="form-control form-control-glass" disabled placeholder="Fitur Email coming soon">
                            </div>
                            <div class="mb-3 text-start">
                                <label class="text-white small mb-1">Password</label>
                                <input type="password" class="form-control form-control-glass" disabled placeholder="••••••••">
                            </div>
                            <button type="button" class="btn btn-info w-100 fw-bold py-2 mt-2" disabled>MASUK</button>
                        </form>
                        
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>