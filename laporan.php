<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:login.php");
    exit();
}

// 1. DATA REAL-TIME (Tidak terpengaruh filter bulan karena ini status saat ini)
$total_kamar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kamar"))['total'];
$kamar_terisi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT id_kamar) as total FROM penghuni WHERE status_penyewa = 'Aktif'"))['total'];
$kamar_tersedia = $total_kamar - $kamar_terisi;

$penghuni_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penghuni WHERE status_penyewa = 'Aktif'"))['total'];
$belum_bayar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penghuni WHERE status_pembayaran = 'Belum Bayar' AND status_penyewa = 'Aktif'"))['total'];

// 2. LOGIKA FILTER BULAN UNTUK KEUANGAN
// Default ke bulan & tahun saat ini jika tidak ada filter
$bulan_ini = date('m');
$tahun_ini = date('Y');

// Cek jika admin menekan tombol filter
if (isset($_GET['filter_bulan']) && !empty($_GET['filter_bulan'])) {
    $filter = $_GET['filter_bulan']; // Format dari input month adalah YYYY-MM
    $parts = explode('-', $filter);
    $tahun_ini = $parts[0];
    $bulan_ini = $parts[1];
}

// Menerjemahkan angka bulan ke nama bulan bahasa Indonesia untuk tampilan
$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
    '04' => 'April', '05' => 'Mei', '06' => 'Juni',
    '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
    '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$label_bulan = $nama_bulan[$bulan_ini] . ' ' . $tahun_ini;

// 3. QUERY KEUANGAN (Berdasarkan bulan yang dipilih)
$pemasukan_bln_ini = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah_bayar) as total FROM pembayaran WHERE MONTH(tgl_bayar) = '$bulan_ini' AND YEAR(tgl_bayar) = '$tahun_ini'"))['total'] ?? 0;
$pengeluaran_bln_ini = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran WHERE MONTH(tanggal) = '$bulan_ini' AND YEAR(tanggal) = '$tahun_ini'"))['total'] ?? 0;
$laba_bersih = $pemasukan_bln_ini - $pengeluaran_bln_ini;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Sistem - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; --navbar-height: 70px; --primary-dark: #1e293b; }
        body { margin: 0; display: flex; background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        
        .sidebar { width: var(--sidebar-width); background: var(--primary-dark) !important; color: white; position: fixed; height: 100vh; display: flex; flex-direction: column; justify-content: space-between; z-index: 1000; }
        .main-wrapper { flex: 1; margin-left: var(--sidebar-width); display: flex; flex-direction: column; min-height: 100vh; }
        .header { height: var(--navbar-height); background: white; padding: 0 30px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 999; }
        .footer { background: white; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 14px; margin-top: auto; }
        
        .main-content { padding: 30px; flex: 1; }
        .report-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .report-card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .report-card h3 { margin: 0 0 15px 0; font-size: 16px; color: #64748b; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; }
        .report-item { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; }
        
        .btn-print { background: #4f46e5; color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px; }
        
        /* Menyembunyikan elemen tertentu saat dicetak */
        @media print { 
            .sidebar, .btn-print, .header, .no-print { display: none !important; } 
            .main-wrapper { margin-left: 0 !important; } 
            .report-card { box-shadow: none; border: 1px solid #eee; } 
        }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        <header class="header">
            <div class="header-left">
                <h2 style="margin:0; font-size: 18px; color: #1e293b;">Laporan Operasional</h2>
            </div>
            <div class="header-right">
                <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Cetak Laporan</button>
            </div>
        </header>

        <main class="main-content">
            
            <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h1 style="margin: 0; font-size: 24px; color: #1e293b;">Ringkasan Kost - <?= $label_bulan ?></h1>
                    <p style="color: #64748b; margin-top: 5px;">Data keuangan berdasarkan bulan yang dipilih.</p>
                </div>
                
                <form method="GET" style="display: flex; gap: 10px; align-items: center;" class="no-print">
                    <label style="font-weight: 600; font-size: 14px; color: #1e293b;">Filter Bulan:</label>
                    <input type="month" name="filter_bulan" value="<?= $tahun_ini . '-' . $bulan_ini ?>" style="padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-family: inherit;">
                    <button type="submit" style="background: #10b981; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-weight: 600; transition: 0.3s;">Tampilkan</button>
                    <?php if(isset($_GET['filter_bulan'])): ?>
                        <a href="laporan.php" style="background: #ef4444; color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; font-weight: 600; font-size: 13px;">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="report-grid">
                <div class="report-card">
                    <h3><i class="fas fa-bed"></i> Status Hunian Kamar (Saat Ini)</h3>
                    <div class="report-item"><span style="color: #64748b;">Total Kapasitas</span><strong><?= $total_kamar ?> Kamar</strong></div>
                    <div class="report-item"><span style="color: #64748b;">Kamar Terisi</span><strong style="color: #4f46e5;"><?= $kamar_terisi ?> Kamar</strong></div>
                    <div class="report-item"><span style="color: #64748b;">Kamar Kosong</span><strong style="color: #10b981;"><?= $kamar_tersedia ?> Kamar</strong></div>
                </div>

                <div class="report-card">
                    <h3><i class="fas fa-users"></i> Status Penyewa (Saat Ini)</h3>
                    <div class="report-item"><span style="color: #64748b;">Penyewa Aktif</span><strong><?= $penghuni_aktif ?> Orang</strong></div>
                    <div class="report-item"><span style="color: #64748b;">Menunggu Pembayaran</span><strong style="color: #ef4444;"><?= $belum_bayar ?> Orang</strong></div>
                </div>

                <div class="report-card">
                    <h3><i class="fas fa-wallet"></i> Keuangan <?= $label_bulan ?></h3>
                    <div class="report-item"><span style="color: #64748b;">Pemasukan</span><strong style="color: #10b981;">Rp <?= number_format($pemasukan_bln_ini, 0, ',', '.') ?></strong></div>
                    <div class="report-item"><span style="color: #64748b;">Pengeluaran</span><strong style="color: #ef4444;">Rp <?= number_format($pengeluaran_bln_ini, 0, ',', '.') ?></strong></div>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 2px dashed #e2e8f0; display: flex; justify-content: space-between;">
                        <span style="font-weight: bold;">Laba Bersih</span>
                        <strong style="color: #4f46e5; font-size: 16px;">Rp <?= number_format($laba_bersih, 0, ',', '.') ?></strong>
                    </div>
                </div>
            </div>
            
            <div style="background: #e0e7ff; color: #4338ca; padding: 15px; border-radius: 12px; font-size: 14px; border: 1px solid #c7d2fe;">
                <i class="fas fa-info-circle"></i> Filter hanya merubah data <strong>Keuangan</strong>. Data Status Kamar dan Penyewa menampilkan angka riil saat ini (Real-time).
            </div>
        </main>

        <?php include 'includes/footer.php'; ?>
    </div>
</body>
</html>