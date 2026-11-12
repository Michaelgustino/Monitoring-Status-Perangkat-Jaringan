<?php
session_start();
include 'koneksi.php';
include 'cek_session.php';

// Proteksi akses
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Admin') {
    header("Location: perangkat.php");
    exit;
}

$pesan_error = "";

// Proses Tambah Perangkat
if (isset($_POST['simpan'])) {
    $nama_perangkat = mysqli_real_escape_string($conn, $_POST['device_name']);
    $kategori_id    = $_POST['category_id'];
    $merk_id        = $_POST['brand_id'];
    $model          = mysqli_real_escape_string($conn, $_POST['model']);
    $serial_number  = mysqli_real_escape_string($conn, $_POST['serial_number']);
    $mac_address    = mysqli_real_escape_string($conn, $_POST['mac_address']);
    $ip_address     = mysqli_real_escape_string($conn, $_POST['ip_address']);
    $hostname       = mysqli_real_escape_string($conn, $_POST['hostname']);
    $lokasi_id      = $_POST['location_id'];
    $status         = $_POST['status'];

    // Cek apakah Serial Number sudah terdaftar
    $cek_sn = mysqli_query($conn, "SELECT * FROM devices WHERE serial_number = '$serial_number'");
    if (mysqli_num_rows($cek_sn) > 0) {
        $pesan_error = "Gagal: Serial Number '$serial_number' sudah terdaftar di database. Gunakan nomor lain.";
    } else {
        $query = "INSERT INTO devices (device_name, category_id, brand_id, model, serial_number, mac_address, ip_address, hostname, location_id, status) 
                  VALUES ('$nama_perangkat', '$kategori_id', '$merk_id', '$model', '$serial_number', '$mac_address', '$ip_address', '$hostname', '$lokasi_id', '$status')";
        
        if (mysqli_query($conn, $query)) {
            header("Location: perangkat.php");
            exit;
        } else {
            $pesan_error = "Gagal menyimpan data perangkat ke database: " . mysqli_error($conn);
        }
    }
}

// Ambil data untuk dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories");
$brands     = mysqli_query($conn, "SELECT * FROM brands");
// Lokasi diurutkan berdasarkan unit (kolom 'unit')
$locations  = mysqli_query($conn, "SELECT id, unit FROM locations ORDER BY unit ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Perangkat Baru - PLN Asset</title>
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
                    <h3 class="fw-bold text-dark mb-1">Tambah Perangkat Baru</h3>
                    <p class="text-muted small mb-4">Masukkan spesifikasi perangkat jaringan atau endpoint dengan benar.</p>

                    <?php if (!empty($pesan_error)): ?>
                        <div class="alert alert-danger py-2 small" role="alert">
                            <?= $pesan_error; ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Nama Perangkat</label>
                                <input type="text" class="form-control" name="device_name" placeholder="Contoh: Router Core Gedung A" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Kategori</label>
                                <select class="form-select" name="category_id" required>
                                    <option value="" selected disabled>Pilih Kategori...</option>
                                    <?php while($c = mysqli_fetch_assoc($categories)): ?>
                                        <option value="<?= $c['id']; ?>"><?= $c['category_name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Merk</label>
                                <select class="form-select" name="brand_id" required>
                                    <option value="" selected disabled>Pilih Merk...</option>
                                    <?php while($b = mysqli_fetch_assoc($brands)): ?>
                                        <option value="<?= $b['id']; ?>"><?= $b['brand_name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Model / Tipe</label>
                                <input type="text" class="form-control" name="model" placeholder="Contoh: Cisco Catalyst 2960">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Serial Number</label>
                                <input type="text" class="form-control" name="serial_number" placeholder="Nomor seri perangkat" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">MAC Address</label>
                                <input type="text" class="form-control" name="mac_address" placeholder="XX:XX:XX:XX:XX:XX">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">IP Address</label>
                                <input type="text" class="form-control" name="ip_address" placeholder="192.168.1.1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Hostname</label>
                                <input type="text" class="form-control" name="hostname" placeholder="router-pusat-a">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Lokasi Fisik</label>
                                <select class="form-select" name="location_id" required>
                                    <option value="" selected disabled>Pilih Lokasi...</option>
                                    <?php while($l = mysqli_fetch_assoc($locations)): ?>
                                        <option value="<?= $l['id']; ?>"><?= htmlspecialchars($l['unit']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Status Perangkat</label>
                                <select class="form-select" name="status" required>
                                    <option value="Aktif" selected>Aktif</option>
                                    <option value="Nonaktif">Nonaktif</option>
                                    <option value="Rusak">Rusak</option>
                                    <option value="Maintenance">Maintenance</option>
                                    <option value="Disimpan">Disimpan</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="simpan" class="btn btn-primary w-100 py-2 text-white fw-semibold" style="background-color: var(--pln-blue); border: none;">
                                <i class="bi bi-save me-1"></i> Simpan Data Perangkat
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