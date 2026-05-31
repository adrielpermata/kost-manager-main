<?php
session_start();
require 'koneksi.php';

// Proteksi Halaman Penyewa
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penyewa') {
    header("location:login.php");
    exit();
}

$nama_login = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : 'Penyewa';
$id_penghuni_saya = isset($_SESSION['id_penghuni']) ? $_SESSION['id_penghuni'] : 0;

// Query Teman Kost: Hanya ambil penghuni Aktif & Kecualikan diri sendiri
$sql = "SELECT p.nama_lengkap, p.no_hp, k.nomor_kamar 
        FROM penghuni p
        LEFT JOIN kamar k ON p.id_kamar = k.id_kamar
        WHERE p.status_penyewa = 'Aktif' AND p.id_penghuni != '$id_penghuni_saya'
        ORDER BY k.nomor_kamar ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teman Kost - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; --navbar-height: 70px; --primary-dark: #1e293b; }
        body { margin: 0; display: flex; background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        
        .sidebar { width: var(--sidebar-width); background: var(--primary-dark) !important; color: white; position: fixed; height: 100vh; display: flex; flex-direction: column; justify-content: space-between; z-index: 1000; top: 0; left: 0;}
        .main-wrapper { flex: 1; margin-left: var(--sidebar-width); display: flex; flex-direction: column; min-height: 100vh; }
        .header { height: var(--navbar-height); background: white; padding: 0 30px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 999; }
        .footer { background: white; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 14px; margin-top: auto; }
        
        .main-content { padding: 30px; flex: 1; }
        
        /* Desain Grid Profil */
        .contact-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
        .contact-card { background: white; border-radius: 12px; padding: 25px 20px; border: 1px solid #e2e8f0; text-align: center; transition: 0.3s; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .contact-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-color: #cbd5e1; }
        
        /* Avatar Profil */
        .avatar { width: 65px; height: 65px; background: #e0e7ff; color: #4f46e5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 24px; font-weight: bold; border: 2px solid #c7d2fe; }
        
        /* Tombol WA */
        .btn-wa { background: #22c55e; color: white; text-decoration: none; padding: 10px; border-radius: 8px; display: block; margin-top: 20px; font-weight: 600; font-size: 14px; transition: 0.3s;}
        .btn-wa:hover { background: #16a34a; }
    </style>
</head>
<body>
    <?php include 'includes/user_sidebar.php'; ?>

    <div class="main-wrapper">
        <?php include 'includes/user_header.php'; ?>

        <main class="main-content">
            <div style="margin-bottom: 30px;">
                <h1 style="margin: 0; font-size: 24px; color: #1e293b;">Buku Kontak Teman Kost</h1>
                <p style="color: #64748b; margin-top: 5px;">Kenalan, silaturahmi, dan hubungi sesama penghuni kost dengan mudah.</p>
            </div>
            
            <div class="contact-grid">
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        // Format nomor WA dari 08... menjadi 628...
                        $no_wa = preg_replace('/^0/', '62', trim($row['no_hp']));
                        $no_wa = preg_replace('/\D/', '', $no_wa); // Hapus spasi/karakter non-angka
                        
                        // Ambil huruf pertama dari nama untuk Avatar
                        $inisial = strtoupper(substr($row['nama_lengkap'], 0, 1));
                    ?>
                        <div class="contact-card">
                            <div class="avatar"><?= $inisial ?></div>
                            <h3 style="margin: 0; font-size: 18px; color: #1e293b;"><?= htmlspecialchars($row['nama_lengkap']) ?></h3>
                            
                            <span style="display:inline-block; margin-top: 8px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 12px; border-radius: 20px; font-size: 12px; color: #475569; font-weight: 600;">
                                <?= $row['nomor_kamar'] ? 'Kamar ' . htmlspecialchars($row['nomor_kamar']) : 'Belum Pilih Kamar' ?>
                            </span>
                            
                            <a href="https://wa.me/<?= $no_wa ?>?text=Halo%20<?= urlencode($row['nama_lengkap']) ?>,%20salam%20kenal%20ya!%20Aku%20<?= urlencode($nama_login) ?>%20dari%20KostManager." target="_blank" class="btn-wa">
                                <i class="fab fa-whatsapp" style="font-size: 16px; margin-right: 5px;"></i> Chat WhatsApp
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 50px; background: white; border-radius: 12px; border: 1px solid #e2e8f0; color: #94a3b8;">
                        <i class="fas fa-users-slash" style="font-size: 50px; margin-bottom: 20px; color: #cbd5e1;"></i>
                        <h3 style="margin: 0; color: #64748b;">Belum Ada Penghuni Lain</h3>
                        <p style="margin-top: 5px;">Saat ini hanya Anda yang terdaftar sebagai penyewa aktif di kost ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <?php include 'includes/user_footer.php'; ?>
    </div>
</body>
</html>