<?php
// File: auth/google_callback.php
session_start();

require_once '../config/database.php';
require_once '../config/google_setup.php';

// 1. Cek apakah Google mengirimkan kode?
if (isset($_GET['code'])) {
    
    // 2. Persiapkan data untuk ditukar ke Google
    $token_request_data = [
        'code' => $_GET['code'],
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URL,
        'grant_type' => 'authorization_code'
    ];

    // 3. Gunakan cURL untuk menukar Kode dengan Token Akses
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_request_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Matikan verifikasi SSL sementara (untuk localhost/XAMPP sering error SSL)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    // Cek jika gagal dapat token
    if (!isset($data['access_token'])) {
        die("Gagal mendapatkan token akses dari Google.");
    }

    $access_token = $data['access_token'];

    // 4. Gunakan Token untuk mengambil Data User (Email, Nama, Foto)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $access_token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $user_info_response = curl_exec($ch);
    curl_close($ch);

    $google_user = json_decode($user_info_response, true);

    // Data yang didapat dari Google
    $email = $google_user['email'];
    $name = $google_user['name'];
    $google_id = $google_user['id'];
    $avatar = $google_user['picture'];

    // 5. LOGIKA DATABASE (Simpan atau Update User)
    try {
        // Cek apakah email sudah ada di database?
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // JIKA USER SUDAH ADA: Update Google ID dan Avatar terbaru
            $update = $conn->prepare("UPDATE users SET google_id = ?, avatar = ? WHERE email = ?");
            $update->execute([$google_id, $avatar, $email]);
            
            // Set Session untuk Login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['avatar'] = $avatar;
        } else {
            // JIKA USER BARU: Insert ke database
            $insert = $conn->prepare("INSERT INTO users (name, email, google_id, avatar, role) VALUES (?, ?, ?, ?, 'user')");
            $insert->execute([$name, $email, $google_id, $avatar]);
            
            // Ambil ID yang baru dibuat
            $new_user_id = $conn->lastInsertId();

            // Set Session
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['role'] = 'user';
            $_SESSION['name'] = $name;
            $_SESSION['avatar'] = $avatar;
        }

        // 6. Redirect ke Halaman Utama
        header("Location: ../index.php");
        exit();

    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }

} else {
    // Jika user mengakses halaman ini tanpa login Google
    header("Location: ../index.php");
    exit();
}
?>