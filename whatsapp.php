<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:login.php");
    exit();
}

$sql = "SELECT p.nama_lengkap, p.no_hp, p.status_pembayaran, k.nomor_kamar 
        FROM penghuni p
        LEFT JOIN kamar k ON p.id_kamar = k.id_kamar
        WHERE p.status_penyewa = 'Aktif'
        ORDER BY p.nama_lengkap ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak WhatsApp - KostManager</title>
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
        .data-section { background: white; border-radius: 12px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #f8fafc; color: #64748b; font-weight: 600; text-align: left; padding: 15px; border-bottom: 2px solid #edf2f7; text-transform: uppercase; font-size: 12px;}
        td { padding: 15px; border-bottom: 1px solid #edf2f7; vertical-align: middle; color: #1e293b; font-size: 14px;}
        
        .btn-wa { background: #22c55e; color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; transition: 0.3s; }
        .btn-wa:hover { background: #16a34a; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        <header class="header">
            <div class="header-left">
                <h2 style="margin:0; font-size: 18px; color: #1e293b;">Buku Kontak WA</h2>
            </div>
            <div class="header-right">
                <span style="color: #64748b; font-size: 14px;"><i class="far fa-calendar-alt"></i> <?= date('d M Y') ?></span>
            </div>
        </header>

        <main class="main-content">
            <div class="data-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <div>
                        <h1 style="margin: 0; font-size: 24px; color: #1e293b;">Kontak Penyewa</h1>
                        <p style="color: #64748b; margin-top: 5px;">Daftar nomor WhatsApp penghuni aktif.</p>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Penyewa</th>
                                <th>Kamar</th>
                                <th>Nomor WA</th>
                                <th>Status Pembayaran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): 
                                    $no_wa = preg_replace('/^0/', '62', trim($row['no_hp']));
                                    $no_wa = preg_replace('/\D/', '', $no_wa); 
                                ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong></td>
                                    <td><span style="background:#e0e7ff; color:#4338ca; padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight:600;">Kamar <?= htmlspecialchars($row['nomor_kamar']) ?></span></td>
                                    <td style="font-family: monospace; font-size: 14px;"><?= htmlspecialchars($row['no_hp']) ?></td>
                                    <td>
                                        <?php if($row['status_pembayaran'] == 'Lunas'): ?>
                                            <span style="background: #dcfce7; color: #16a34a; padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight:600;">Lunas</span>
                                        <?php else: ?>
                                            <span style="background: #fee2e2; color: #dc2626; padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight:600;">Belum Bayar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="https://wa.me/<?= $no_wa ?>?text=Halo%20<?= urlencode($row['nama_lengkap']) ?>,%20ini%20dari%20pengurus%20KostManager." target="_blank" class="btn-wa">
                                            <i class="fab fa-whatsapp"></i> Chat WA
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">
                                        Belum ada kontak penghuni.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>
</body>
</html>