<?php
session_start();
require 'koneksi.php';

// Fungsi format rupiah jika belum didefinisikan di koneksi.php
if (!function_exists('formatRupiah')) {
    function formatRupiah($angka){
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}

if (isset($_POST['simpan_kamar'])) {
    $nomor   = mysqli_real_escape_string($conn, $_POST['nomor_kamar']);
    $tipe    = mysqli_real_escape_string($conn, $_POST['tipe_kamar']);
    $ukuran  = mysqli_real_escape_string($conn, $_POST['ukuran']);
    $harga   = mysqli_real_escape_string($conn, $_POST['harga_per_bulan']);
    $deposit = mysqli_real_escape_string($conn, $_POST['deposit']);
    
    $query = "INSERT INTO kamar (nomor_kamar, tipe_kamar, ukuran, harga_per_bulan, deposit, status) 
              VALUES ('$nomor', '$tipe', '$ukuran', '$harga', '$deposit', 'tersedia')";

    // 2. Update status kamar menjadi Terisi
    $query2 = "UPDATE kamar SET status = 'Terisi' WHERE id_kamar = '$id_kamar'";
    
     if (mysqli_query($conn, $query2)) {
        echo "<script>alert('Kamar berhasil ditambah!'); window.location='kamar.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    } 

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Kamar berhasil ditambah!'); window.location='kamar.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Proteksi Halaman
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:login.php");
    exit();
}

// --- 1. LOGIKA HAPUS DATA ---
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    $query_hapus = "DELETE FROM kamar WHERE id_kamar = '$id'";
    if (mysqli_query($conn, $query_hapus)) {
        echo "<script>alert('Kamar berhasil dihapus!'); window.location='kamar.php';</script>";
    }
}

// --- 2. LOGIKA UPDATE DATA ---
if (isset($_POST['update_kamar'])) {
    $id = $_POST['id_kamar'];
    $nomor = mysqli_real_escape_string($conn, $_POST['nomor_kamar']);
    $tipe = mysqli_real_escape_string($conn, $_POST['tipe_kamar']);
    $ukuran = mysqli_real_escape_string($conn, $_POST['ukuran']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga_per_bulan']);
    $deposit = mysqli_real_escape_string($conn, $_POST['deposit']);
    $listrik = isset($_POST['listrik']) ? 1 : 0;
    $air = isset($_POST['air']) ? 1 : 0;

    $query_upd = "UPDATE kamar SET 
                  nomor_kamar='$nomor', lantai='$lantai', tipe_kamar='$tipe', 
                  ukuran='$ukuran', harga_per_bulan='$harga', deposit='$deposit',
                  listrik_termasuk='$listrik', air_termasuk='$air' 
                  WHERE id_kamar='$id'";
    
    if (mysqli_query($conn, $query_upd)) {
        echo "<script>alert('Data kamar berhasil diperbarui!'); window.location='kamar.php';</script>";
    }
}

// --- 3. PENCARIAN & FILTER ---
$search = $_GET['search'] ?? '';
$status_f = $_GET['status'] ?? '';

$sql = "SELECT * FROM kamar WHERE (nomor_kamar LIKE '%$search%' OR tipe_kamar LIKE '%$search%')";
if ($status_f != '') {
    $sql .= " AND status = '$status_f'";
}
$result = mysqli_query($conn, $sql);
$total_kamar = mysqli_num_rows($result);

// Ambil data dari tabel kamar
$query = mysqli_query($conn, "SELECT * FROM kamar");

while ($kamar = mysqli_fetch_assoc($query)) {
    // Tentukan warna berdasarkan status
    if ($kamar['status'] == 'Tersedia') {
        $bg_color = 'bg-success'; // Hijau (Bootstrap)
        $label = 'Kosong';
    } else {
        $bg_color = 'bg-danger';  // Merah (Bootstrap)
        $label = 'Terisi';
    }
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kamar - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS Tambahan untuk menyatukan Layout */
        :root {
            --sidebar-width: 260px;
            --navbar-height: 70px;
            --primary-bg: #1e293b;
        }

        body {
            margin: 0;
            display: flex;
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Sidebar Tetap Sesuai Struktur Anda */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-bg);
            color: white;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            z-index: 1000;
        }

        /* Container Konten Utama */
        .main-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navbar / Header */
        .header {
            height: var(--navbar-height);
            background: white;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        /* Main Content */
        .main-content {
            padding: 30px;
            flex: 1;
        }

        /* Footer */
        .footer {
            background: white;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }

        /* Penyesuaian Table agar tidak pecah */
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { background: #f8fafc; color: #64748b; }
    </style>
</head>
<body>

 <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        
        <?php 
if (file_exists('includes/header.php')) {
    include_once('includes/header.php');
}
?>

        <main class="main-content">
            <div class="data-section">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <div>
                        <h1 style="margin: 0;">Kamar</h1>
                        <p style="color: #64748b; margin-top: 5px;"><?= $total_kamar; ?> kamar terdaftar</p>
                    </div>
                    <button class="btn-add" onclick="document.getElementById('modalTambahKamar').style.display='flex'">
                        <i class="fas fa-plus"></i> Tambah Kamar
                    </button>
                </div>

                <form method="GET" style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <div style="flex: 1; position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 15px; top: 12px; color: #94a3b8;"></i>
                        <input type="text" name="search" value="<?= $search ?>" placeholder="Cari kode atau tipe..." style="width:100%; padding: 10px 10px 10px 40px; border-radius: 10px; border: 1px solid #ddd; box-sizing: border-box;">
                    </div>
                    <select name="status" onchange="this.form.submit()" style="padding: 10px; border-radius: 10px; border: 1px solid #ddd; cursor: pointer;">
                        <option value="">Semua Status</option>
                        <option value="tersedia" <?= $status_f == 'tersedia' ? 'selected' : '' ?>>Kosong</option>
                        <option value="terisi" <?= $status_f == 'terisi' ? 'selected' : '' ?>>Terisi</option>
                    </select>
                </form>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Tipe</th>
                                <th>Harga/Bulan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><strong><?= $row['nomor_kamar'] ?></strong></td>
                                <td><?= $row['tipe_kamar'] ?></td>
                                <td><?= formatRupiah($row['harga_per_bulan']) ?></td>
                                <td>
                                    <?php $cls = ($row['status'] == 'tersedia') ? 'status-tersedia' : 'status-terisi'; 
                                    $cls = ($row['status'] == 'Tersedia') ? 'status-tersedia' : 'status-terisi'; ?>
                                    <span class="badge <?= $cls ?>"><?= ucfirst($row['status']) ?></span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 15px;">
                                        <button type="button" 
   class="btn btn-warning btn-sm btn-edit-trigger" 
   data-id="<?= $row['id_kamar'] ?>"
   data-nomor="<?= $row['nomor_kamar'] ?>"
   data-tipe="<?= $row['tipe_kamar'] ?>"
   data-ukuran="<?= $row['ukuran'] ?>"
   data-harga="<?= $row['harga_per_bulan'] ?>">
   <i class="fas fa-pencil-alt"></i>
</button>
                                        <a href="kamar.php?hapus=<?= $row['id_kamar'] ?>" onclick="return confirm('Yakin hapus?')">
                                            <i class="fas fa-trash" style="color: #fb7185;"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <footer class="footer">
            &copy; <?= date('Y'); ?> <strong>KostManager</strong>. Sistem Manajemen Kost.
        </footer>
    </div>

    <div id="modalTambahKamar" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <h2 style="margin-top:0; margin-bottom: 20px;">Tambah Kamar Baru</h2>
            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Kode Kamar *</label>
                        <input type="text" name="nomor_kamar" required>
                    </div>
                    <div class="form-group">
                        <label>Tipe</label>
                        <select name="tipe_kamar">
                            <option value="Standard">Standard</option>
                            <option value="Deluxe">Deluxe</option>
                            <option value="Suite">Suite</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Harga/Bulan (Rp) *</label>
                        <input type="number" name="harga_per_bulan" required>
                    </div>
                    <div class="form-group">
                        <label>Ukuran</label>
                        <input type="text" name="ukuran" placeholder="Misal: 3x4">
                    </div>
                    <div class="form-group">
                        <label>Deposit (Rp)</label>
                        <input type="number" name="deposit">
                    </div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" onclick="document.getElementById('modalTambahKamar').style.display='none'" class="btn-cancel" style="flex:1;">Batal</button>
                    <button type="submit" name="simpan_kamar" class="btn-save" style="flex:1;">Simpan Kamar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEditKamar" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; border-radius:8px; width:450px; max-width:90%;">
        <h4>Edit Data Kamar</h4>
        <hr>
        <form action="kamar_update.php" method="POST">
            <input type="hidden" id="edit_id" name="id_kamar">

            <div style="margin-bottom:10px;">
                <label>Nomor Kamar</label>
                <input type="text" id="edit_nomor" name="nomor_kamar" style="width:100%" required>
            </div>

            <div style="margin-bottom:10px;">
                <label>Tipe Kamar</label>
                <input type="text" id="edit_tipe" name="tipe_kamar" style="width:100%">
            </div>

            <div style="margin-bottom:10px;">
                <label>Ukuran</label>
                <input type="text" id="edit_ukuran" name="ukuran" style="width:100%">
            </div>

            <div style="margin-bottom:10px;">
                <label>Harga</label>
                <input type="number" id="edit_harga" name="harga" style="width:100%">
            </div>

            <hr>
            <div style="text-align:right;">
                <button type="button" onclick="document.getElementById('modalEditKamar').style.display='none'">Batal</button>
                <button type="submit" style="background:blue; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
    <script>
document.addEventListener('click', function (e) {
    // Mencari apakah yang diklik adalah tombol dengan class .btn-edit-trigger
    const btn = e.target.closest('.btn-edit-trigger');
    
    if (btn) {
        // PENTING: Hanya panggil ID yang BENAR-BENAR ADA di HTML kamu
        // Jika 'edit_lantai' sudah dihapus di HTML, barisnya harus dihapus di sini juga
        
        document.getElementById('edit_id').value = btn.dataset.id;
        document.getElementById('edit_nomor').value = btn.dataset.nomor;
        document.getElementById('edit_tipe').value = btn.dataset.tipe;
        document.getElementById('edit_ukuran').value = btn.dataset.ukuran;
        document.getElementById('edit_harga').value = btn.dataset.harga;

        // Munculkan Modal
        document.getElementById('modalEditKamar').style.display = 'flex';
    }
});

// Menutup modal jika klik di luar area modal
window.onclick = function(event) {
    const modal = document.getElementById('modalEditKamar');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>
</body>
</html>