<?php
session_start();
require 'koneksi.php';

// Proteksi Halaman Penyewa
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penyewa') {
    header("location:login.php");
    exit();
}

// Ambil ID Penghuni dari session login
$id_penghuni = $_SESSION['id_penghuni'] ?? '';

// 1. Query Informasi Kamar & Data Pribadi Penyewa
$query_penghuni = "SELECT penghuni.*, kamar.nomor_kamar, kamar.harga_per_bulan, kamar.tipe_kamar, kamar.ukuran, kamar.listrik_termasuk, kamar.air_termasuk 
                   FROM penghuni 
                   LEFT JOIN kamar ON penghuni.id_kamar = kamar.id_kamar 
                   WHERE penghuni.id_penghuni = '$id_penghuni'";
$res_penghuni = mysqli_query($conn, $query_penghuni);
$user_data = mysqli_fetch_assoc($res_penghuni);

// 2. Query Daftar Tagihan Penyewa
$query_tagihan = "SELECT * FROM tagihan WHERE id_penghuni = '$id_penghuni' ORDER BY jatuh_tempo DESC";
$result_tagihan = mysqli_query($conn, $query_tagihan);

// 3. Query Riwayat Transaksi Pembayaran Penyewa
$query_pembayaran = "SELECT * FROM pembayaran WHERE id_penghuni = '$id_penghuni' ORDER BY tgl_bayar DESC";
$result_pembayaran = mysqli_query($conn, $query_pembayaran);

// 4. Hitung Total Tagihan yang Belum Dibayar
$query_total_tagihan = "SELECT SUM(jumlah_tagihan) as total FROM tagihan WHERE id_penghuni = '$id_penghuni' AND status_tagihan != 'Lunas'";
$total_tagihan_res = mysqli_fetch_assoc(mysqli_query($conn, $query_total_tagihan));
$total_belum_bayar = $total_tagihan_res['total'] ?? 0;

// Fungsi format rupiah
if (!function_exists('formatRupiah')) {
    function formatRupiah($angka){
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penyewa - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Standarisasi Layout */
        :root { --sidebar-width: 260px; --navbar-height: 70px; --primary-dark: #1e293b; }
        body { margin: 0; display: flex; background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        
        .sidebar { width: var(--sidebar-width); background: var(--primary-dark) !important; color: white; position: fixed; height: 100vh; display: flex; flex-direction: column; justify-content: space-between; z-index: 1000; top: 0; left: 0; }
        .main-wrapper { flex: 1; margin-left: var(--sidebar-width); display: flex; flex-direction: column; min-height: 100vh; }
        .header { height: var(--navbar-height); background: white; padding: 0 30px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 999; }
        .footer { background: white; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 14px; margin-top: auto; }
        
        .main-content { padding: 30px; flex: 1; }
        
        /* Grid Widget Atas */
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .summary-card { background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .summary-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        
        /* Konten Sektor */
        .data-section { background: white; border-radius: 12px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8fafc; color: #64748b; font-weight: 600; text-align: left; padding: 12px 15px; border-bottom: 2px solid #edf2f7; text-transform: uppercase; font-size: 11px; }
        td { padding: 12px 15px; border-bottom: 1px solid #edf2f7; color: #1e293b; font-size: 14px; }
        
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-block; }
        .badge-lunas { background: #dcfce7; color: #16a34a; }
        .badge-belum { background: #fee2e2; color: #dc2626; }
        .badge-dp { background: #fef3c7; color: #d97706; }
        
        .sub-title { margin-top: 20px; margin-bottom: 5px; color: #475569; font-size: 14px; font-weight: 600; }
    </style>
</head>
<body>

    <!-- Memanggil Sidebar yang Bersih -->
    <?php include 'includes/user_sidebar.php'; ?>

    <div class="main-wrapper">
        <?php include 'includes/user_header.php'; ?>

        <main class="main-content">
            <!-- Ucapan Selamat Datang -->
            <div style="margin-bottom: 25px;">
                <h1 style="margin: 0; font-size: 24px; color: #1e293b;">Selamat Datang, <?= htmlspecialchars($user_data['nama_lengkap'] ?? 'Penghuni'); ?>!</h1>
                <p style="color: #64748b; margin-top: 5px;">Seluruh rangkuman data hunian dan pembayaran Anda terintegrasi secara terpusat.</p>
            </div>

            <!-- Bagian Atas: Ringkasan Cepat -->
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-icon" style="background: #e0e7ff; color: #4338ca;"><i class="fas fa-bed"></i></div>
                    <div>
                        <div style="font-size: 14px; color: #64748b;">Kamar Anda</div>
                        <div style="font-size: 18px; font-weight: 700; color: #1e293b;">
                            <?= !empty($user_data['nomor_kamar']) ? 'Kamar ' . htmlspecialchars($user_data['nomor_kamar']) : 'Belum Atur'; ?>
                        </div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon" style="background: #fee2e2; color: #dc2626;"><i class="fas fa-exclamation-circle"></i></div>
                    <div>
                        <div style="font-size: 14px; color: #64748b;">Total Tunggakan</div>
                        <div style="font-size: 18px; font-weight: 700; color: #dc2626;"><?= formatRupiah($total_belum_bayar) ?></div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon" style="background: #dcfce7; color: #16a34a;"><i class="fas fa-wallet"></i></div>
                    <div>
                        <div style="font-size: 14px; color: #64748b;">Status Terkini</div>
                        <div style="font-size: 16px; font-weight: 700; margin-top: 2px;">
                            <?php 
                                $st = $user_data['status_pembayaran'] ?? 'Belum Bayar';
                                if($st == 'Lunas') echo '<span class="badge badge-lunas">Lunas</span>';
                                elseif($st == 'DP') echo '<span class="badge badge-dp">DP</span>';
                                else echo '<span class="badge badge-belum">Belum Bayar</span>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian 1: Informasi Kamar Saya -->
            <div class="data-section" style="margin-bottom: 30px;">
                <h3 style="margin-top: 0; color: #1e293b; font-size: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><i class="fas fa-info-circle"></i> Spesifikasi Kamar & Fasilitas</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px; font-size: 14px;">
                    <div><span style="color: #64748b;">Biaya Sewa / Bulan:</span> <br><strong><?= isset($user_data['harga_per_bulan']) ? formatRupiah($user_data['harga_per_bulan']) : '-'; ?></strong></div>
                    <div><span style="color: #64748b;">Batas Jatuh Tempo:</span> <br><strong><?= (!empty($user_data['jatuh_tempo']) && $user_data['jatuh_tempo'] != '0000-00-00') ? date('d M Y', strtotime($user_data['jatuh_tempo'])) : '-'; ?></strong></div>
                    <div style="grid-column: 1 / -1;">
                        <span style="color: #64748b;">Daftar Fasilitas Kamar:</span> <br>
                        <p style="margin: 5px 0 0 0; font-weight: 500; color: #475569;">
                            <?php 
                                $fasilitas_arr = [];
                                if(!empty($user_data['tipe_kamar'])) $fasilitas_arr[] = "Tipe " . htmlspecialchars($user_data['tipe_kamar']);
                                if(!empty($user_data['ukuran'])) $fasilitas_arr[] = "Ukuran " . htmlspecialchars($user_data['ukuran']);
                                if(isset($user_data['listrik_termasuk']) && $user_data['listrik_termasuk'] == 1) $fasilitas_arr[] = "Termasuk Listrik";
                                if(isset($user_data['air_termasuk']) && $user_data['air_termasuk'] == 1) $fasilitas_arr[] = "Termasuk Air";
                                
                                echo !empty($fasilitas_arr) ? implode(' • ', $fasilitas_arr) : 'Fasilitas standar hunian kost.';
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bagian 2: Riwayat Keuangan Disatukan -->
            <div class="data-section">
                <h3 style="margin-top: 0; color: #1e293b; font-size: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><i class="fas fa-history"></i> Riwayat Pembayaran & Tagihan</h3>
                
                <!-- Sub-bagian Tagihan -->
                <div class="sub-title"><i class="fas fa-file-invoice"></i> Daftar Tagihan Anda</div>
                <div style="overflow-x: auto; margin-bottom: 25px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Periode Bulan</th>
                                <th>Jumlah Tagihan</th>
                                <th>Jatuh Tempo</th>
                                <th>Status Konfirmasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result_tagihan) > 0): ?>
                                <?php 
                                mysqli_data_seek($result_tagihan, 0);
                                while($t = mysqli_fetch_assoc($result_tagihan)): 
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($t['bulan_tagihan']) ?></td>
                                    <td><strong><?= formatRupiah($t['jumlah_tagihan']) ?></strong></td>
                                    <td><?= date('d M Y', strtotime($t['jatuh_tempo'])) ?></td>
                                    <td>
                                        <?php if($t['status_tagihan'] == 'Lunas'): ?>
                                            <span class="badge badge-lunas">Lunas</span>
                                        <?php else: ?>
                                            <span class="badge badge-belum">Belum Bayar</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center; padding:15px; color:#94a3b8; font-size: 13px;">Tidak ada catatan tagihan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Sub-bagian Riwayat Pembayaran -->
                <div class="sub-title"><i class="fas fa-wallet"></i> Bukti Pembayaran Masuk</div>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal Transaksi</th>
                                <th>Nomor Invoice</th>
                                <th>Metode Pembayaran</th>
                                <th>Jumlah Terbayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result_pembayaran) > 0): ?>
                                <?php 
                                mysqli_data_seek($result_pembayaran, 0);
                                while($p = mysqli_fetch_assoc($result_pembayaran)): 
                                ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($p['tgl_bayar'])) ?></td>
                                    <td style="font-family: monospace; font-size: 13px; color: #64748b;"><?= htmlspecialchars($p['no_invoice']) ?></td>
                                    <td><span style="background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 500;"><?= htmlspecialchars($p['metode_bayar']) ?></span></td>
                                    <td><strong><?= formatRupiah($p['jumlah_bayar']) ?></strong></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center; padding:15px; color:#94a3b8; font-size: 13px;">Belum ada log pembayaran.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
            </div>
        </main>

        <!-- Menggunakan footer bawaan layout -->
        <?php include 'includes/user_footer.php'; ?>
    </div>
</body>
</html>