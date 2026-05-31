<?php
session_start();
require 'koneksi.php';

// 1. PROTEKSI HALAMAN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:login.php");
    exit();
}

$tahun_ini = date('Y');
$bulan_ini = date('m');

// 2. QUERY STATISTIK KARTU ATAS
$total_kamar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kamar"))['total'];
$kamar_terisi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kamar WHERE status = 'Terisi'"))['total'];
$kamar_kosong = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kamar WHERE status = 'Tersedia'"))['total'];
$pemasukan_bln = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah_bayar) as total FROM pembayaran WHERE MONTH(tgl_bayar) = '$bulan_ini' AND YEAR(tgl_bayar) = '$tahun_ini'"))['total'] ?? 0;

// 3. QUERY DATA UNTUK GRAFIK (CHART)
$bulan_labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
$pemasukan_data = array_fill(0, 12, 0);
$pengeluaran_data = array_fill(0, 12, 0);

$res_pem = mysqli_query($conn, "SELECT MONTH(tgl_bayar) as bln, SUM(jumlah_bayar) as total FROM pembayaran WHERE YEAR(tgl_bayar) = '$tahun_ini' GROUP BY MONTH(tgl_bayar)");
while($row = mysqli_fetch_assoc($res_pem)) { $pemasukan_data[$row['bln'] - 1] = $row['total']; }

$res_peng = mysqli_query($conn, "SELECT MONTH(tanggal) as bln, SUM(jumlah) as total FROM pengeluaran WHERE YEAR(tanggal) = '$tahun_ini' GROUP BY MONTH(tanggal)");
while($row = mysqli_fetch_assoc($res_peng)) { $pengeluaran_data[$row['bln'] - 1] = $row['total']; }

// 4. QUERY PENGINGAT PEMBAYARAN
$sql_tagih = "SELECT p.nama_lengkap, p.no_hp, k.nomor_kamar, k.harga_per_bulan 
              FROM penghuni p 
              JOIN kamar k ON p.id_kamar = k.id_kamar 
              WHERE p.status_pembayaran = 'Belum Bayar' AND p.status_penyewa = 'Aktif'";
$res_tagih = mysqli_query($conn, $sql_tagih);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --sidebar-width: 260px; --navbar-height: 70px; --primary-dark: #1e293b; }
        body { margin: 0; display: flex; background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        
        .sidebar { width: var(--sidebar-width); background: var(--primary-dark) !important; color: white; position: fixed; height: 100vh; display: flex; flex-direction: column; justify-content: space-between; z-index: 1000; }
        .main-wrapper { flex: 1; margin-left: var(--sidebar-width); display: flex; flex-direction: column; min-height: 100vh; }
        .header { height: var(--navbar-height); background: white; padding: 0 30px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 999; }
        .footer { background: white; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 14px; margin-top: auto; }
        
        .main-content { padding: 30px; flex: 1; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 20px; }
        .stat-icon { width: 55px; height: 55px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .icon-blue { background: #e0e7ff; color: #4338ca; }
        .icon-green { background: #dcfce7; color: #16a34a; }
        .icon-yellow { background: #fef3c7; color: #d97706; }
        .icon-red { background: #fee2e2; color: #dc2626; }
        .stat-info h3 { margin: 0; font-size: 14px; color: #64748b; font-weight: 500; }
        .stat-info p { margin: 5px 0 0; font-size: 22px; font-weight: 700; color: #1e293b; }

        .content-card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; padding: 12px; border-bottom: 2px solid #f1f5f9; color: #64748b; font-size: 12px; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; }
        .btn-wa-tagih { background: #22c55e; color: white; text-decoration: none; padding: 8px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .btn-wa-tagih:hover { background: #16a34a; }

        .btn-action { padding: 10px 18px; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        <header class="header">
            <h2 style="margin:0; font-size: 18px; color: #1e293b;">Panel Manajemen Kost</h2>
            <span style="color: #64748b; font-size: 14px;"><i class="far fa-calendar-alt"></i> <?= date('d M Y') ?></span>
        </header>

        <main class="main-content">
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-icon icon-blue"><i class="fas fa-building"></i></div><div class="stat-info"><h3>Total Kamar</h3><p><?= $total_kamar ?></p></div></div>
                <div class="stat-card"><div class="stat-icon icon-yellow"><i class="fas fa-user-check"></i></div><div class="stat-info"><h3>Terisi</h3><p><?= $kamar_terisi ?></p></div></div>
                <div class="stat-card"><div class="stat-icon icon-green"><i class="fas fa-door-open"></i></div><div class="stat-info"><h3>Kosong</h3><p><?= $kamar_kosong ?></p></div></div>
                <div class="stat-card"><div class="stat-icon icon-red"><i class="fas fa-chart-line"></i></div><div class="stat-info"><h3>Pemasukan Bln Ini</h3><p>Rp <?= number_format($pemasukan_bln, 0, ',', '.') ?></p></div></div>
            </div>

            <div class="content-card">
                <h3 style="margin-top:0; color:#1e293b; margin-bottom: 20px;"><i class="fas fa-chart-bar"></i> Perbandingan Arus Kas <?= $tahun_ini ?></h3>
                <canvas id="cashflowChart" height="85"></canvas>
            </div>

            <div class="content-card">
                <h3 style="margin-top:0; color:#dc2626;"><i class="fas fa-bell"></i> Pengingat Pembayaran</h3>
                <p style="color: #64748b; font-size: 14px;">Penghuni aktif yang belum membayar sewa bulan ini.</p>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr><th>Nama Penghuni</th><th>Kamar</th><th>Tagihan</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($res_tagih) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($res_tagih)): 
                                    $wa = preg_replace('/^0/', '62', trim($row['no_hp']));
                                ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong></td>
                                    <td><span style="background:#e0e7ff; color:#4338ca; padding:4px 10px; border-radius:15px; font-size:12px; font-weight:600;">Kamar <?= htmlspecialchars($row['nomor_kamar']) ?></span></td>
                                    <td style="color:#dc2626; font-weight:700;">Rp <?= number_format($row['harga_per_bulan'], 0, ',', '.') ?></td>
                                    <td>
                                        <a href="https://wa.me/<?= $wa ?>?text=Halo%20<?= urlencode($row['nama_lengkap']) ?>,%20mengingatkan%20untuk%20pembayaran%20kost%20bulan%20ini.%20Terima%20kasih." target="_blank" class="btn-wa-tagih">
                                            <i class="fab fa-whatsapp"></i> Kirim Tagihan
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center; padding:30px; color:#94a3b8;"><i class="fas fa-check-circle"></i> Semua penghuni sudah melakukan pembayaran.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-card">
                <h3 style="margin-top:0; color:#1e293b;"><i class="fas fa-bolt"></i> Akses Cepat</h3>
                <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 15px;">
                    <a href="penghuni.php" class="btn-action" style="background: #4f46e5;"><i class="fas fa-user-plus"></i> Tambah Penghuni</a>
                    <a href="pembayaran.php" class="btn-action" style="background: #10b981;"><i class="fas fa-money-bill-wave"></i> Catat Pembayaran</a>
                    <a href="pengeluaran.php" class="btn-action" style="background: #ef4444;"><i class="fas fa-file-invoice-dollar"></i> Catat Pengeluaran</a>
                    <a href="laporan.php" class="btn-action" style="background: #6366f1;"><i class="fas fa-file-alt"></i> Lihat Laporan</a>
                </div>
            </div>
        </main>

        <footer class="footer">
            <div>
                &copy; <?= date('Y'); ?> <strong>KostManager</strong>. Sistem Manajemen Kost.
            </div>
            <div style="margin-top: 8px; color: #94a3b8; font-size: 13px;">
                <span style="margin-right: 5px;">Follow Us</span> 
                <a href="https://instagram.com/sumatraview.kost" target="_blank" style="color: #4f46e5; text-decoration: none; font-weight: 600;">
                    <i class="fab fa-instagram"></i> @sumatraview.kost
                </a>
            </div>
        </footer>

    <script>
        const ctx = document.getElementById('cashflowChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($bulan_labels) ?>,
                datasets: [
                    { label: 'Pemasukan (Rp)', data: <?= json_encode($pemasukan_data) ?>, backgroundColor: '#10b981', borderRadius: 5 },
                    { label: 'Pengeluaran (Rp)', data: <?= json_encode($pengeluaran_data) ?>, backgroundColor: '#ef4444', borderRadius: 5 }
                ]
            },
            options: { 
                responsive: true, 
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, ticks: { callback: function(value) { return 'Rp ' + value.toLocaleString(); } } } }
            }
        });
    </script>
</body>
</html>