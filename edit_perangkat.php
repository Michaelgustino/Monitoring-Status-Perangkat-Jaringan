<?php
session_start();
include 'koneksi.php';
include 'cek_session.php';

// Validasi hak akses Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Admin') {
    header("Location: perangkat.php");
    exit;
}

$pesan_error = "";

// Ambil ID perangkat dari URL dan validasi sebagai integer
if (!isset($_GET['id'])) {
    header("Location: perangkat.php");
    exit;
}
$id = intval($_GET['id']);

// Ambil data perangkat berdasarkan ID menggunakan Prepared Statement
$stmt_current = mysqli_prepare($conn, "SELECT * FROM devices WHERE id = ?");
mysqli_stmt_bind_param($stmt_current, "i", $id);
mysqli_stmt_execute($stmt_current);
$query_current = mysqli_stmt_get_result($stmt_current);

if (mysqli_num_rows($query_current) === 0) {
    header("Location: perangkat.php");
    exit;
}
$device = mysqli_fetch_assoc($query_current);
mysqli_stmt_close($stmt_current);

// Proses Update Perangkat
if (isset($_POST['update'])) {
    $nama_perangkat = $_POST['device_name'];
    $kategori_id    = $_POST['category_id'];
    $merk_id        = $_POST['brand_id'];
    $model          = $_POST['model'];
    $serial_number  = $_POST['serial_number'];
    $mac_address    = $_POST['mac_address'];
    $ip_address     = $_POST['ip_address'];
    $hostname       = $_POST['hostname'];
    $lokasi_id      = $_POST['location_id'];
    $status         = $_POST['status'];

    // Cek apakah Serial Number sudah terdaftar di perangkat LAIN (Prepared Statement)
    $stmt_cek = mysqli_prepare($conn, "SELECT id FROM devices WHERE serial_number = ? AND id != ?");
    mysqli_stmt_bind_param($stmt_cek, "si", $serial_number, $id);
    mysqli_stmt_execute($stmt_cek);
    mysqli_stmt_store_result($stmt_cek);

    if (mysqli_stmt_num_rows($stmt_cek) > 0) {
        $pesan_error = "Gagal: Serial Number '$serial_number' sudah terdaftar di perangkat lain. Gunakan nomor lain.";
    } else {
        // Query Update menggunakan Prepared Statement
        $query_update = "UPDATE devices SET 
                            device_name = ?, 
                            category_id = ?, 
                            brand_id = ?, 
                            model = ?, 
                            serial_number = ?, 
                            mac_address = ?, 
                            ip_address = ?, 
                            hostname = ?, 
                            location_id = ?, 
                            status = ? 
                         WHERE id = ?";
        
        $stmt_update = mysqli_prepare($conn, $query_update);
        mysqli_stmt_bind_param($stmt_update, "siisssssisi", 
            $nama_perangkat, 
            $kategori_id, 
            $merk_id, 
            $model, 
            $serial_number, 
            $mac_address, 
            $ip_address, 
            $hostname, 
            $lokasi_id, 
            $status, 
            $id
        );
        
        if (mysqli_stmt_execute($stmt_update)) {
            mysqli_stmt_close($stmt_update);
            header("Location: perangkat.php");
            exit;
        } else {
            $pesan_error = "Gagal memperbarui data perangkat ke database.";
        }
        mysqli_stmt_close($stmt_update);
    }
    mysqli_stmt_close($stmt_cek);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Perangkat - PLN Asset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --pln-blue: #005691; --pln-yellow: #F7B500; }
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .form-card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 30px; }
    </style>
</head>
<body>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="mb-3">
                    <a href="perangkat.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Kembali ke Daftar Perangkat</a>
                </div>
                <div class="form-card">
                    <h3 class="fw-bold text-dark mb-1">Edit Perangkat</h3>
                    <p class="text-muted small mb-4">Perbarui spesifikasi perangkat jaringan atau endpoint.</p>

                    <?php if (!empty($pesan_error)): ?>
                        <div class="alert alert-danger py-2 small" role="alert">
                            <?= htmlspecialchars($pesan_error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Nama Perangkat</label>
                                <input type="text" class="form-control" name="device_name" value="<?= htmlspecialchars($device['device_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Kategori</label>
                                <select class="form-select" name="category_id" required>
                                    <option value="" disabled>Pilih Kategori...</option>
                                    <?php 
                                    $categories = mysqli_query($conn, "SELECT * FROM categories");
                                    while($c = mysqli_fetch_assoc($categories)): 
                                    ?>
                                        <option value="<?= $c['id']; ?>" <?= ($device['category_id'] == $c['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($c['category_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Merk</label>
                                <select class="form-select" name="brand_id" required>
                                    <option value="" disabled>Pilih Merk...</option>
                                    <?php 
                                    $brands = mysqli_query($conn, "SELECT * FROM brands");
                                    while($b = mysqli_fetch_assoc($brands)): 
                                    ?>
                                        <option value="<?= $b['id']; ?>" <?= ($device['brand_id'] == $b['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($b['brand_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Model / Tipe</label>
                                <input type="text" class="form-control" name="model" value="<?= htmlspecialchars($device['model']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Serial Number</label>
                                <input type="text" class="form-control" name="serial_number" value="<?= htmlspecialchars($device['serial_number']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">MAC Address</label>
                                <input type="text" class="form-control" name="mac_address" value="<?= htmlspecialchars($device['mac_address']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">IP Address</label>
                                <input type="text" class="form-control" name="ip_address" value="<?= htmlspecialchars($device['ip_address']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Hostname</label>
                                <input type="text" class="form-control" name="hostname" value="<?= htmlspecialchars($device['hostname']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Lokasi Fisik</label>
                                <select class="form-select" name="location_id" required>
                                    <option value="" disabled>Pilih Lokasi...</option>
                                    <?php 
                                    $locations = mysqli_query($conn, "SELECT * FROM locations ORDER BY unit ASC");
                                    while($l = mysqli_fetch_assoc($locations)): 
                                    ?>
                                        <option value="<?= $l['id']; ?>" <?= ($device['location_id'] == $l['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($l['unit']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Status Perangkat</label>
                                <select class="form-select" name="status" required>
                                    <option value="Aktif" <?= ($device['status'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="Nonaktif" <?= ($device['status'] == 'Nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                                    <option value="Rusak" <?= ($device['status'] == 'Rusak') ? 'selected' : ''; ?>>Rusak</option>
                                    <option value="Maintenance" <?= ($device['status'] == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                    <option value="Disimpan" <?= ($device['status'] == 'Disimpan') ? 'selected' : ''; ?>>Disimpan</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="update" class="btn btn-warning w-100 py-2 text-dark fw-semibold">
                                <i class="bi bi-pencil-square me-1"></i> Perbarui Perangkat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>