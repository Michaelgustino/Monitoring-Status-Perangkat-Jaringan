<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tentukan batas waktu idle (15 menit = 900 detik)
$batas_idle = 900; 

// Cek apakah variabel waktu terakhir aktivitas sudah ada
if (isset($_SESSION['terakhir_aktif'])) {
    $durasi_idle = time() - $_SESSION['terakhir_aktif'];
    
    // Jika waktu idle melebihi batas, hancurkan sesi dan paksa logout
    if ($durasi_idle > $batas_idle) {
        session_unset();
        session_destroy();
        header("Location: login.php?pesan=session_habis");
        exit;
    }
}

// Perbarui waktu aktivitas terakhir dengan waktu saat ini
$_SESSION['terakhir_aktif'] = time();
?>