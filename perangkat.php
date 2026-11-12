<?php
session_start();
include 'koneksi.php';
include 'cek_session.php';

// Cek apakah sudah login
if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$role = $_SESSION['role'] ?? 'User';

// Ambil Data Perangkat beserta Relasinya (JOIN) - Kolom building & floor dihapus
$query_perangkat = "SELECT devices.*, 
                           categories.category_name, 
                           brands.brand_name, 
                           locations.unit 
                    FROM devices 
                    LEFT JOIN categories ON devices.category_id = categories.id 
                    LEFT JOIN brands ON devices.brand_id = brands.id 
                    LEFT JOIN locations ON devices.location_id = locations.id 
                    ORDER BY devices.id DESC";
$result = mysqli_query($conn, $query_perangkat);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Perangkat - PLN Asset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --pln-blue: #005691; --pln-yellow: #F7B500; }
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-pln { background-color: var(--pln-blue) !important; }
        .brand-logo { font-weight: 700; color: white !important; }
        .brand-logo span { color: var(--pln-yellow); }
        .table-container { background-color: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
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
                        <a class="nav-link" href="index.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active fw-semibold" href="perangkat.php"><i class="bi bi-hdd-rack me-1"></i> Daftar Perangkat</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="text-white me-3">Halo, <b><?= htmlspecialchars($_SESSION['nama']); ?></b> <span class="badge bg-warning text-dark ms-1"><?= htmlspecialchars($role); ?></span></span>
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
                <h3 class="fw-bold text-dark mb-1">Manajemen Perangkat Jaringan & Endpoint</h3>
                <p class="text-muted small mb-0">Daftar keseluruhan inventaris perangkat keras yang terdaftar di sistem.</p>
            </div>
            <?php if ($role === 'Admin'): ?>
                <a href="tambah_perangkat.php" class="btn btn-primary btn-sm shadow-sm px-3 text-white text-decoration-none" style="background-color: var(--pln-blue); border: none;">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Perangkat
                </a>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Perangkat</th>
                            <th>Kategori</th>
                            <th>Merk / Model</th>
                            <th>IP / MAC Address</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <?php if ($role === 'Admin'): ?>
                                <th class="text-center">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['device_name']); ?></strong><br>
                                        <small class="text-muted">Hostname: <?= htmlspecialchars($row['hostname']); ?></small>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($row['category_name']); ?></span></td>
                                    <td>
                                        <?= htmlspecialchars($row['brand_name']); ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($row['model']); ?></small>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($row['ip_address']); ?></code><br>
                                        <small class="text-muted"><?= htmlspecialchars($row['mac_address']); ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($row['unit'] ?? '-'); ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $status = $row['status'];
                                            $badge_bg = 'bg-success';
                                            if ($status == 'Nonaktif') $badge_bg = 'bg-secondary';
                                            elseif ($status == 'Rusak') $badge_bg = 'bg-danger';
                                            elseif ($status == 'Maintenance') $badge_bg = 'bg-warning text-dark';
                                            elseif ($status == 'Disimpan') $badge_bg = 'bg-info text-dark';
                                        ?>
                                        <span class="badge <?= $badge_bg; ?>"><?= $status; ?></span>
                                    </td>
                                    <?php if ($role === 'Admin'): ?>
                                        <td class="text-center">
                                            <a href="edit_perangkat.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning text-dark px-2 py-1" title="Edit Perangkat">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= ($role === 'Admin') ? 8 : 7; ?>" class="text-center text-muted py-4">Belum ada data perangkat yang dimasukkan ke database.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>