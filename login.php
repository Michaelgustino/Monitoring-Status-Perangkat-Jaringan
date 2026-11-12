<?php
session_start();
include 'koneksi.php';

// Jika sudah login, langsung lempar ke index.php (Dashboard)
if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$pesan_login = "";
$pesan_register = "";

// 1. Logika Proses Login (Diamankan dengan Prepared Statement)
if (isset($_POST['login'])) {
    $nik = $_POST['nik'];
    $password = $_POST['password'];

    // Menggunakan Prepared Statement untuk mencegah SQL Injection
    $stmt = $conn->prepare("SELECT * FROM akun_pegawai WHERE nik = ?");
    $stmt->bind_param("s", $nik);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Regenerasi ID Session untuk mencegah Session Fixation
            session_regenerate_id(true);
            
            $_SESSION['login'] = true;
            $_SESSION['nama'] = $row['nama_lengkap'];
            $_SESSION['nik'] = $row['nik'];
            $_SESSION['role'] = $row['role'];
            header("Location: index.php");
            exit;
        }
    }
    $pesan_login = "NIK atau Password salah!";
}

// 2. Logika Proses Register (Diamankan dengan Prepared Statement)
if (isset($_POST['register'])) {
    $nik = $_POST['nik_reg'];
    $nama = $_POST['nama_reg'];
    $password = password_hash($_POST['pass_reg'], PASSWORD_DEFAULT);

    // Otomatis jadi Admin jika NIK 12345 atau ada kata 'admin', sisanya User
    $role = 'User';
    if ($nik === '12345' || strpos($nik, 'admin') !== false) {
        $role = 'Admin';
    }

    // Cek apakah NIK sudah terdaftar menggunakan Prepared Statement
    $stmt_cek = $conn->prepare("SELECT * FROM akun_pegawai WHERE nik = ?");
    $stmt_cek->bind_param("s", $nik);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();

    if ($result_cek->num_rows > 0) {
        $pesan_register = "NIK sudah terdaftar!";
    } else {
        // Insert data baru menggunakan Prepared Statement
        $stmt_insert = $conn->prepare("INSERT INTO akun_pegawai (nik, nama_lengkap, password, role) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("ssss", $nik, $nama, $password, $role);
        
        if ($stmt_insert->execute()) {
            $pesan_register = "Akun berhasil dibuat sebagai ($role)! Silakan masuk.";
        } else {
            $pesan_register = "Gagal mendaftarkan akun.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register - PLN Asset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --pln-blue: #005691; --pln-yellow: #F7B500; }
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { background: white; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); padding: 40px; width: 100%; max-width: 450px; }
        .brand-title { font-weight: 700; color: var(--pln-blue); }
        .brand-title span { color: var(--pln-yellow); }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="text-center mb-4">
            <h3 class="brand-title"><i class="bi bi-hdd-network-fill"></i> PLN<span>Asset</span></h3>
            <p class="text-muted small">Sistem Inventaris Perangkat Jaringan & Endpoint</p>
        </div>

        <ul class="nav nav-pills nav-fill mb-4" id="authTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-pane" type="button" role="tab">Masuk</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-pane" type="button" role="tab">Daftar Akun</button>
            </li>
        </ul>

        <div class="tab-content" id="authTabContent">
            <!-- Pane Login -->
            <div class="tab-pane fade show active" id="login-pane" role="tabpanel">
                <?php if (!empty($pesan_login)): ?>
                    <div class="alert alert-danger py-2 small" role="alert"><?= $pesan_login; ?></div>
                <?php endif; ?>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">NIK Pegawai</label>
                        <input type="text" class="form-control" name="nik" placeholder="Masukkan NIK" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Masukkan password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-semibold text-white" style="background-color: var(--pln-blue); border: none;">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                    </button>
                </form>
            </div>

            <!-- Pane Register -->
            <div class="tab-pane fade" id="register-pane" role="tabpanel">
                <?php if (!empty($pesan_register)): ?>
                    <div class="alert alert-info py-2 small" role="alert"><?= $pesan_register; ?></div>
                <?php endif; ?>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">NIK Pegawai</label>
                        <input type="text" class="form-control" name="nik_reg" placeholder="Nomor Induk Karyawan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_reg" placeholder="Nama lengkap Anda" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Password</label>
                        <input type="password" class="form-control" name="pass_reg" placeholder="Buat password" required>
                    </div>
                    <button type="submit" name="register" class="btn btn-warning w-100 py-2 fw-semibold text-dark">
                        <i class="bi bi-person-plus me-1"></i> Daftar Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>