<?php
session_start();
require 'koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penyewa') {
    header("location:login.php");
    exit();
}

$nama_login = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : 'Penyewa';
$id_penghuni = isset($_SESSION['id_penghuni']) ? $_SESSION['id_penghuni'] : 0;

$sql = "SELECT * FROM pembayaran WHERE id_penghuni = '$id_penghuni' ORDER BY tgl_bayar DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Saya - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; --navbar-height: 70px; --primary-dark: #1e293b; }
        body { margin: 0; display: flex; background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        
        /* FIX SIDEBAR: Agar muncul dan nempel di kiri */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-dark) !important;
            color: white;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            z-index: 1000;
        }

        .main-wrapper { flex: 1; margin-left: var(--sidebar-width); display: flex; flex-direction: column; min-height: 100vh; }
        .header { height: var(--navbar-height); background: white; padding: 0 30px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 999; }
        .footer { background: white; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 14px; margin-top: auto; }
        
        .main-content { padding: 30px; flex: 1; }
        .data-section { background: white; border-radius: 12px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #f8fafc; color: #64748b; font-weight: 600; text-align: left; padding: 15px; border-bottom: 2px solid #edf2f7; text-transform: uppercase; font-size: 13px;}
        td { padding: 15px; border-bottom: 1px solid #edf2f7; vertical-align: middle; color: #1e293b; font-size: 14px;}
    </style>
</head>
<body>
    <?php include 'includes/user_sidebar.php'; ?>

    <div class="main-wrapper">
        <?php include 'includes/user_header.php'; ?>
        
        <main class="main-content">
            <div class="data-section">
                <h1 style="margin-top:0; font-size: 24px; color: #1e293b;">Riwayat Pembayaran</h1>
                <p style="color: #64748b; margin-bottom: 20px;">Daftar bukti pembayaran yang telah diverifikasi admin.</p>
                
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>No. Invoice</th>
                                <th>Tanggal</th>
                                <th>Metode</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><code style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; color: #475569;"><?= htmlspecialchars($row['no_invoice']) ?></code></td>
                                    <td><?= date('d M Y', strtotime($row['tgl_bayar'])) ?></td>
                                    <td><?= ucfirst(htmlspecialchars($row['metode_bayar'])) ?></td>
                                    <td><strong><?= formatRupiah($row['jumlah_bayar']) ?></strong></td>
                                    <td><span style="background:#dcfce7; color:#16a34a; padding:5px 10px; border-radius:6px; font-size:12px; font-weight:bold;">Berhasil</span></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:40px; color:#94a3b8;">
                                        <i class="fas fa-receipt" style="font-size:30px; margin-bottom:10px; display:block;"></i>
                                        Belum ada riwayat pembayaran.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        
        <?php include 'includes/user_footer.php'; ?>
    </div>
</body>
</html>