<?php
session_start();
require 'koneksi.php'; 

// Fungsi format rupiah jika belum didefinisikan di koneksi.php
if (!function_exists('formatRupiah')) {
    function formatRupiah($angka){
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:login.php");
    exit();
}

// --- 1. LOGIKA SIMPAN PEMBAYARAN ---
if (isset($_POST['simpan_pembayaran'])) {
    $id_penghuni = mysqli_real_escape_string($conn, $_POST['id_penghuni']);
    $tgl = mysqli_real_escape_string($conn, $_POST['tgl_bayar']);
    $metode = mysqli_real_escape_string($conn, $_POST['metode_bayar']);
    $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $tipe_bayar = mysqli_real_escape_string($conn, $_POST['tipe_pembayaran']);
    
    // Penentuan No. Invoice dan Status berdasarkan Tipe Pembayaran
    if ($tipe_bayar == 'DP') {
        $no_invoice = "DP-" . date('Ym') . "-" . rand(100, 999);
        $status_baru = 'DP';
    } else {
        $no_invoice = "INV-" . date('Ym') . "-" . rand(100, 999);
        $status_baru = 'Lunas';
    }

    $query_ins = "INSERT INTO pembayaran (id_penghuni, tgl_bayar, no_invoice, metode_bayar, jumlah_bayar) 
                  VALUES ('$id_penghuni', '$tgl', '$no_invoice', '$metode', '$jumlah')";
    
    if (mysqli_query($conn, $query_ins)) {
        // 1. Update status pembayaran di tabel penghuni (bisa Lunas atau DP)
        mysqli_query($conn, "UPDATE penghuni SET status_pembayaran = '$status_baru' WHERE id_penghuni = '$id_penghuni'");
        
        // 2. Update status di tabel tagihan menjadi sesuai tipe sewa
        mysqli_query($conn, "UPDATE tagihan SET status_tagihan = '$status_baru' WHERE id_penghuni = '$id_penghuni'");
        
        echo "<script>alert('Pembayaran Berhasil Dicatat!'); window.location='pembayaran.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal Menyimpan Data! Error: " . mysqli_error($conn) . "');</script>";
    }
}

// --- 2. QUERY DATA UNTUK TABEL RIWAYAT ---
$sql = "SELECT pembayaran.*, penghuni.nama_lengkap 
        FROM pembayaran 
        JOIN penghuni ON pembayaran.id_penghuni = penghuni.id_penghuni 
        ORDER BY tgl_bayar DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS Untuk Menyesuaikan Layout Sidebar, Header, Body, dan Footer */
        :root { --sidebar-width: 260px; --navbar-height: 70px; --primary-dark: #1e293b; }
        body { margin: 0; display: flex; background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        
        .sidebar { width: var(--sidebar-width); background: var(--primary-dark) !important; color: white; position: fixed; height: 100vh; display: flex; flex-direction: column; justify-content: space-between; z-index: 1000; top: 0; left: 0; }
        .main-wrapper { flex: 1; margin-left: var(--sidebar-width); display: flex; flex-direction: column; min-height: 100vh; }
        .header { height: var(--navbar-height); background: white; padding: 0 30px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 999; }
        .footer { background: white; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 14px; margin-top: auto; }
        
        .main-content { padding: 30px; flex: 1; }
        .data-section { background: white; border-radius: 12px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #f8fafc; color: #64748b; font-weight: 600; text-align: left; padding: 15px; border-bottom: 2px solid #edf2f7; text-transform: uppercase; font-size: 12px;}
        td { padding: 15px; border-bottom: 1px solid #edf2f7; vertical-align: middle; color: #1e293b; font-size: 14px;}
        
        .btn-add { background: #4f46e5; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        
        <header class="header">
            <div class="header-left">
                <h2 style="margin: 0; font-size: 18px; color: #1e293b;">Manajemen Transaksi</h2>
            </div>
            <div class="header-right">
                <span style="color: #64748b; font-size: 14px;"><i class="far fa-calendar-alt"></i> <?= date('d M Y') ?></span>
            </div>
        </header>

        <main class="main-content">
            <div class="data-section">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <div>
                        <h1 style="margin: 0; font-size: 24px; color: #1e293b;">Pembayaran</h1>
                        <p style="color: #64748b; margin-top: 5px;">Riwayat pencatatan pembayaran sewa kost</p>
                    </div>
                    <button class="btn-add" onclick="document.getElementById('modalBayar').style.display='flex'">
                        <i class="fas fa-plus"></i> Catat Pembayaran
                    </button>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Penyewa</th>
                                <th>No. Invoice</th>
                                <th>Tipe</th>
                                <th>Metode</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($row['tgl_bayar'])) ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong></td>
                                    <td style="color: #64748b; font-family: monospace; font-size: 13px;"><?= htmlspecialchars($row['no_invoice']) ?></td>
                                    <td>
                                        <?php if(strpos($row['no_invoice'], 'DP-') === 0): ?>
                                            <span style="background: #fef3c7; color: #d97706; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700;">DP</span>
                                        <?php else: ?>
                                            <span style="background: #e0e7ff; color: #4338ca; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700;">Lunas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $m = $row['metode_bayar'];
                                            $badge_bg = ($m == 'Transfer') ? '#e0e7ff' : (($m == 'Tunai') ? '#dcfce7' : '#f1f5f9');
                                            $badge_color = ($m == 'Transfer') ? '#4338ca' : (($m == 'Tunai') ? '#16a34a' : '#475569');
                                        ?>
                                        <span style="background: <?= $badge_bg ?>; color: <?= $badge_color ?>; padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: 600;">
                                            <?= htmlspecialchars($m) ?>
                                        </span>
                                    </td>
                                    <td><strong><?= formatRupiah($row['jumlah_bayar']) ?></strong></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center; padding:40px; color:#94a3b8;">Belum ada riwayat pembayaran.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <?php include 'includes/footer.php'; ?>
    </div>

    <div id="modalBayar" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
        <div style="background:white; padding:25px; border-radius:12px; width:450px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0; color: #1e293b; margin-bottom: 20px; font-size: 20px;">Catat Pembayaran Baru</h2>
            <form method="POST" action="pembayaran.php">
                
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Pilih Penghuni *</label>
                    <select name="id_penghuni" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background: white;" required>
                        <option value="">-- Pilih Nama --</option>
                        <?php 
                        $list_penghuni = mysqli_query($conn, "SELECT id_penghuni, nama_lengkap FROM penghuni WHERE status_penyewa = 'Aktif'");
                        while($p = mysqli_fetch_assoc($list_penghuni)): ?>
                            <option value="<?= $p['id_penghuni'] ?>"><?= htmlspecialchars($p['nama_lengkap']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Tipe Pembayaran *</label>
                    <select name="tipe_pembayaran" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background: white;" required>
                        <option value="Lunas">Sewa Penuh / Lunas</option>
                        <option value="DP">DP (Down Payment)</option>
                    </select>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Tanggal Pembayaran *</label>
                    <input type="date" name="tgl_bayar" value="<?= date('Y-m-d') ?>" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;" required>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Metode Pembayaran *</label>
                    <select name="metode_bayar" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background: white;">
                        <option value="Transfer">Transfer</option>
                        <option value="QRIS">QRIS</option>
                        <option value="Tunai">Tunai</option>
                    </select>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Jumlah Bayar (Rp) *</label>
                    <input type="number" name="jumlah" placeholder="Contoh: 500000" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;" required>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" onclick="document.getElementById('modalBayar').style.display='none'" style="flex:1; padding:12px; border:none; background:#f1f5f9; cursor:pointer; border-radius:8px; font-weight:600; color:#475569;">Batal</button>
                    <button type="submit" name="simpan_pembayaran" style="flex:1; padding:12px; border:none; background:#4f46e5; color:white; cursor:pointer; border-radius:8px; font-weight:bold;">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.onclick = function(event) {
            const modal = document.getElementById('modalBayar');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>