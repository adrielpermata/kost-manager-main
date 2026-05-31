<?php
session_start();
require 'koneksi.php';

// --- 1. LOGIKA UBAH STATUS JADI SELESAI ---
if (isset($_GET['set_selesai'])) {
    $id_p = mysqli_real_escape_string($conn, $_GET['set_selesai']);

    // Ambil ID Kamar yang dipakai penghuni ini sebelum statusnya diubah
    $query_kamar = mysqli_query($conn, "SELECT id_kamar FROM penghuni WHERE id_penghuni = '$id_p'");
    $data_p = mysqli_fetch_assoc($query_kamar);
    $id_kamar = $data_p['id_kamar'];

    // Update status penghuni jadi 'Selesai'
    mysqli_query($conn, "UPDATE penghuni SET status_penyewa = 'Selesai' WHERE id_penghuni = '$id_p'");
    
    // OTOMATIS: Update status kamar tersebut jadi 'tersedia' kembali
    mysqli_query($conn, "UPDATE kamar SET status = 'tersedia' WHERE id_kamar = '$id_kamar'");

    echo "<script>alert('Penyewa telah selesai. Kamar kini tersedia kembali!'); window.location='penghuni.php';</script>";
}

// --- 2. FILTER STATUS (Default: Aktif) ---
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'Aktif';

// Query Ambil Data
$sql = "SELECT penghuni.*, kamar.nomor_kamar 
        FROM penghuni 
        LEFT JOIN kamar ON penghuni.id_kamar = kamar.id_kamar 
        WHERE penghuni.status_penyewa = '$filter'";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penghuni - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS untuk menyamakan layout dengan Dashboard Admin */
        :root {
            --sidebar-width: 260px;
            --navbar-height: 70px;
            --primary-dark: #1e293b;
        }

        body { 
            margin: 0; 
            display: flex; 
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }

        /* Sidebar Tetap (Warna Dark sesuai Admin) */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-dark);
            color: white;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            z-index: 1000;
        }

        /* Wrapper Utama */
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

        /* Styling Tabel & Card agar bersih */
        .data-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f8fafc; color: #64748b; font-weight: 600; text-align: left; padding: 12px; border-bottom: 2px solid #edf2f7; }
        td { padding: 15px 12px; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div>
            <div class="logo"><i class="fas fa-building"></i> KostManager</div>
            <ul class="nav-links">
                <li onclick="window.location='admin_dashboard.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : '' ?>"><i class="fas fa-th-large"></i> Dashboard</li>
                <li onclick="window.location='kamar.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'kamar.php' ? 'active' : '' ?>"><i class="fas fa-bed"></i> Kamar</li>
                <li onclick="window.location='penghuni.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'penghuni.php' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Penyewa</li>
                <li onclick="window.location='pembayaran.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'pembayaran.php' ? 'active' : '' ?>"><i class="fas fa-wallet"></i> Pembayaran</li>
                <li onclick="window.location='tagihan.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'tagihan.php' ? 'active' : '' ?>"><i class="fas fa-file-alt"></i> Tagihan</li>
                <li onclick="window.location='pengeluaran.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'pengeluaran.php' ? 'active' : '' ?>"><i class="fas fa-wallet"></i> Pengeluaran</li>
                <li onclick="window.location='laporan.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> Laporan</li>
                <li><i class="fab fa-whatsapp"></i> WhatsApp</li>
            </ul>
        </div>
        <div class="user-profile">
            <div style="display: flex; align-items: center; gap: 10px; padding: 0 20px 10px;">
                <i class="fas fa-user-circle" style="font-size: 24px;"></i>
                <span style="font-size: 14px;">Admin</span>
            </div>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-wrapper">
        
        <header class="header">
            <div class="header-left">
                <h2 style="margin: 0; font-size: 18px; color: #1e293b;">Manajemen Penyewa</h2>
            </div>
            <div class="header-right" style="display: flex; align-items: center; gap: 20px;">
                <div class="date" style="color: #64748b; font-size: 14px;">
                    <i class="far fa-calendar-alt"></i> <?= date('d M Y') ?>
                </div>
                <div style="width: 1px; height: 20px; background: #e2e8f0;"></div>
                <div style="font-weight: 600; font-size: 14px; color: #1e293b;">Super Admin</div>
            </div>
        </header>

        <main class="main-content">
            <div class="data-section">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h1 style="margin: 0; font-size: 24px;">Daftar Penghuni</h1>
                        <p style="color: #64748b; margin: 5px 0 0 0;">Total: <?= mysqli_num_rows($result); ?> orang (Status: <?= $filter ?>)</p>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <select onchange="location.href='penghuni.php?filter=' + this.value" style="padding: 10px; border-radius: 10px; border: 1px solid #ddd; cursor:pointer; background: white;">
                            <option value="Aktif" <?= $filter == 'Aktif' ? 'selected' : '' ?>>Penyewa Aktif</option>
                            <option value="Selesai" <?= $filter == 'Selesai' ? 'selected' : '' ?>>Sudah Keluar</option>
                        </select>
                        <button class="btn-add" onclick="alert('Buka Modal Tambah')" style="background: #4f46e5; color: white; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 600;">
                            <i class="fas fa-plus"></i> Tambah
                        </button>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Kamar</th>
                                <th>No. HP</th>
                                <th>Tgl Masuk</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><strong><?= $row['nama_lengkap'] ?></strong></td>
                                    <td><span class="badge status-terisi">Kamar <?= $row['nomor_kamar'] ?></span></td>
                                    <td><?= $row['no_hp'] ?></td>
                                    <td><?= date('d M Y', strtotime($row['tgl_masuk'])) ?></td>
                                    <td>
                                        <?php if($row['status_penyewa'] == 'Aktif'): ?>
                                            <span class="badge" style="background:#dcfce7; color:#15803d; padding: 5px 10px; border-radius: 6px; font-size: 12px;">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge" style="background:#f1f5f9; color:#64748b; padding: 5px 10px; border-radius: 6px; font-size: 12px;">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 12px; align-items: center;">
                                            <?php if($row['status_penyewa'] == 'Aktif'): ?>
                                                <a href="penghuni.php?set_selesai=<?= $row['id_penghuni'] ?>" 
                                                   onclick="return confirm('Yakin penyewa ini sudah selesai? Kamar akan otomatis kosong.')"
                                                   style="color: #4f46e5; font-size: 11px; font-weight: 700; text-decoration:none; border: 1px solid #4f46e5; padding: 4px 8px; border-radius: 6px; text-transform: uppercase;">
                                                   Checkout
                                                </a>
                                            <?php endif; ?>
                                            <i class="fas fa-pencil-alt" style="color: #94a3b8; cursor:pointer;" title="Edit"></i>
                                            <i class="fas fa-trash" style="color: #fb7185; cursor:pointer;" title="Hapus"></i>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center; padding:40px; color:#94a3b8;">Tidak ada data penyewa <?= $filter ?>.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
    </div> </body>
</html>