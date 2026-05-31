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

// Query Detail Kamar
$sql = "SELECT p.nama_lengkap, p.tgl_masuk, p.id_kamar, 
               k.nomor_kamar, k.tipe_kamar, k.harga_per_bulan
        FROM penghuni p
        LEFT JOIN kamar k ON p.id_kamar = k.id_kamar 
        WHERE p.id_penghuni = '$id_penghuni'";

$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kamar Saya - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; --navbar-height: 70px; --primary-dark: #1e293b; }
        body { margin: 0; display: flex; background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        
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
        .info-card { background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); max-width: 800px; }
        .info-item { display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #f1f5f9; }
        .info-label { color: #64748b; font-weight: 500; }
        .info-value { color: #1e293b; font-weight: 700; }
    </style>
</head>
<body>
    <?php include 'includes/user_sidebar.php'; ?>

    <div class="main-wrapper">
        <?php include 'includes/user_header.php'; ?>

        <main class="main-content">
            <div class="info-card">
                <div style="border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
                    <h2 style="margin:0; color: #1e293b;"><i class="fas fa-door-open"></i> Detail Kamar Anda</h2>
                </div>

                <?php if($data && !empty($data['id_kamar'])): ?>
                    <div class="info-item">
                        <span class="info-label">Nomor Kamar</span>
                        <span class="info-value">Kamar <?= htmlspecialchars($data['nomor_kamar']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tipe Kamar</span>
                        <span class="info-value"><?= htmlspecialchars($data['tipe_kamar']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Harga Sewa</span>
                        <span class="info-value"><?= formatRupiah($data['harga_per_bulan']) ?> / Bulan</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tanggal Masuk</span>
                        <span class="info-value"><?= date('d M Y', strtotime($data['tgl_masuk'])) ?></span>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 20px; color: #64748b;">
                        <i class="fas fa-bed" style="font-size: 40px; margin-bottom: 15px; color: #cbd5e1;"></i>
                        <p>Data kamar belum tersedia. Silakan hubungi Admin.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <?php include 'includes/user_footer.php'; ?>
    </div>
</body>
</html>