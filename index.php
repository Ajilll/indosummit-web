<?php
session_start();
require_once 'config/google_setup.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>IndoSummit Test</title>
    <!-- CSS Bootstrap via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5 text-center">

    <h1>Selamat Datang di IndoSummit</h1>

    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Tampilan Jika SUDAH Login -->
        <div class="card mx-auto mt-4" style="width: 18rem;">
            <img src="<?= $_SESSION['avatar'] ?>" class="card-img-top" alt="Avatar">
            <div class="card-body">
                <h5 class="card-title">Halo, <?= $_SESSION['name'] ?>!</h5>
                <p class="card-text">Status: <?= $_SESSION['role'] ?></p>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Tampilan Jika BELUM Login -->
        <p class="mt-4">Silakan login untuk melanjutkan.</p>
        <a href="<?= $google_login_url ?>" class="btn btn-primary btn-lg">
            Login dengan Google
        </a>
    <?php endif; ?>

</body>
</html>