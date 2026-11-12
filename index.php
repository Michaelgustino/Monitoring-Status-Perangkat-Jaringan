<?php
session_start();
include 'koneksi.php';
include 'cek_session.php';

// Cek login: jika belum login, arahkan ke login.php
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Ambil Data Statistik untuk Dashboard
$total_perangkat = 0;
$total_aktif = 0;
$total_gangguan = 0;
$total_lokasi = 0;

$q1 = mysqli_query($conn, "SELECT COUNT(*) as total FROM devices");
if($q1) $total_perangkat = mysqli_fetch_assoc($q1)['total'];

$q2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM devices WHERE status = 'Aktif'");
if($q2) $total_aktif = mysqli_fetch_assoc($q2)['total'];

$q3 = mysqli_query($conn, "SELECT COUNT(*) as total FROM devices WHERE status IN ('Rusak', 'Maintenance')");
if($q3) $total_gangguan = mysqli_fetch_assoc($q3)['total'];

$q4 = mysqli_query($conn, "SELECT COUNT(*) as total FROM locations");
if($q4) $total_lokasi = mysqli_fetch_assoc($q4)['total'];

// Ambil Data untuk Pie Chart (Kategori Perangkat)
$query_chart = "SELECT categories.category_name, COUNT(devices.id) as total_per_kategori 
                FROM categories 
                LEFT JOIN devices ON categories.id = devices.category_id 
                GROUP BY categories.id, categories.category_name";
$result_chart = mysqli_query($conn, $query_chart);

$category_labels = [];
$category_data = [];

while($row_c = mysqli_fetch_assoc($result_chart)) {
    $category_labels[] = $row_c['category_name'];
    $category_data[] = $row_c['total_per_kategori'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PLN Asset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --pln-blue: #005691; --pln-yellow: #F7B500; }
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-pln { background-color: var(--pln-blue) !important; }
        .brand-logo { font-weight: 700; color: white !important; }
        .brand-logo span { color: var(--pln-yellow); }
        .card-stat { border: none; border-radius: 12px; color: white; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); background: white; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-pln sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand brand-logo" href="index.php">
                <i class="bi bi-hdd-network-fill text-warning"></i> PLN<span>Asset</span>
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-4">
                    <li class="nav-item">
                        <a class="nav-link active fw-semibold" href="index.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="perangkat.php"><i class="bi bi-hdd-rack me-1"></i> Daftar Perangkat</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="text-white me-3">Halo, <b><?= htmlspecialchars($_SESSION['nama']); ?></b> <span class="badge bg-warning text-dark ms-1"><?= htmlspecialchars($_SESSION['role']); ?></span></span>
                    <a href="logout.php" class="btn btn-outline-warning btn-sm px-3 rounded-pill">
                        <i class="bi bi-box-arrow-right"></i> Keluar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Dashboard Inventaris Perangkat</h3>
                <p class="text-muted small mb-0">Monitoring perangkat jaringan dan endpoint kantor secara terpusat.</p>
            </div>
            <?php if ($_SESSION['role'] === 'Admin'): ?>
                <a href="tambah_perangkat.php" class="btn btn-primary btn-sm shadow-sm px-3 text-white text-decoration-none" style="background-color: var(--pln-blue); border: none;">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Perangkat
                </a>
            <?php endif; ?>
        </div>

        <!-- Kartu Statistik -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card card-stat p-3 bg-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-white-50 text-uppercase fw-bold">Total Perangkat</span>
                            <h2 class="fw-bold mb-0 mt-1"><?= $total_perangkat; ?></h2>
                            <small class="text-white-50" style="font-size: 0.75rem;">Terdaftar di database</small>
                        </div>
                        <i class="bi bi-router fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat p-3 bg-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-white-50 text-uppercase fw-bold">Status Aktif</span>
                            <h2 class="fw-bold mb-0 mt-1"><?= $total_aktif; ?></h2>
                            <small class="text-white-50" style="font-size: 0.75rem;">Beroperasi normal</small>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat p-3 bg-danger">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-white-50 text-uppercase fw-bold">Maintenance / Rusak</span>
                            <h2 class="fw-bold mb-0 mt-1"><?= $total_gangguan; ?></h2>
                            <small class="text-white-50" style="font-size: 0.75rem;">Perlu penanganan</small>
                        </div>
                        <i class="bi bi-tools fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat p-3 bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-black-50 text-uppercase fw-bold">Total Lokasi Unit</span>
                            <h2 class="fw-bold mb-0 mt-1 text-dark"><?= $total_lokasi; ?></h2>
                            <small class="text-black-50" style="font-size: 0.75rem;">Gedung & ruangan terdata</small>
                        </div>
                        <i class="bi bi-shop fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie Chart Kategori Perangkat -->
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card card-custom p-4">
                    <h5 class="fw-bold text-dark mb-3 text-center">Distribusi Perangkat Berdasarkan Kategori</h5>
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="categoryPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <footer class="text-center py-4 text-muted small">
        &copy; <?= date('Y'); ?> PT PLN (Persero). All rights reserved.
    </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const ctx = document.getElementById('categoryPieChart').getContext('2d');
    const categoryPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?= json_encode($category_labels); ?>,
            datasets: [{
                data: <?= json_encode($category_data); ?>,
                backgroundColor: [
                    '#005691', '#F7B500', '#28a745', '#dc3545', '#17a2b8', '#6c757d', '#6610f2', '#fd7e14'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Skrip Auto-Logout 15 Menit AFK
    let batasWaktu = 15 * 60 * 1000; // 15 menit dalam milidetik
    let timerLogout;

    function resetTimer() {
        clearTimeout(timerLogout);
        timerLogout = setTimeout(function() {
            alert("Sesi Anda telah habis karena tidak ada aktivitas selama 15 menit.");
            window.location.href = 'logout.php';
        }, batasWaktu);
    }

    window.onload = resetTimer;
    window.onmousemove = resetTimer;
    window.onmousedown = resetTimer;
    window.onclick = resetTimer;
    window.onkeypress = resetTimer;
    window.addEventListener('scroll', resetTimer, true);
</script>
</body>
</html>