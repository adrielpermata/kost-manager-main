<?php
session_start();
require 'koneksi.php'; 

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:login.php");
    exit();
}

// Mengambil data dari tabel tagihan, di-join dengan penghuni dan kamar
// Ditambahkan pemanggilan kolom "no_hp" untuk keperluan WhatsApp
$sql = "SELECT tagihan.*, penghuni.nama_lengkap, penghuni.no_hp, kamar.nomor_kamar 
        FROM tagihan 
        JOIN penghuni ON tagihan.id_penghuni = penghuni.id_penghuni
        LEFT JOIN kamar ON penghuni.id_kamar = kamar.id_kamar
        WHERE penghuni.status_penyewa = 'Aktif'
        ORDER BY tagihan.jatuh_tempo ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tagihan - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS Untuk Menyesuaikan Layout Sesuai Gambar */
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
        
        /* Badge Status Sesuai Desain */
        .badge-lunas { background: #dcfce7; color: #16a34a; padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .badge-belum { background: #fee2e2; color: #dc2626; padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .badge-kamar { background: #e0e7ff; color: #4338ca; padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: 600; }
        
        /* CSS Tambahan untuk Tombol WhatsApp */
        .btn-wa { background: #22c55e; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; transition: 0.2s; }
        .btn-wa:hover { background: #16a34a; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        <header class="header">
            <div class="header-left">
                <h2 style="margin:0; font-size: 18px; color: #1e293b;">Manajemen Tagihan</h2>
            </div>
            <div class="header-right">
                <span style="color: #64748b; font-size: 14px;"><i class="far fa-calendar-alt"></i> <?= date('d M Y') ?></span>
            </div>
        </header>

        <main class="main-content">
            <div class="data-section">
                <div style="margin-bottom: 25px;">
                    <h1 style="margin: 0; font-size: 24px; color: #1e293b;">Daftar Tagihan</h1>
                    <p style="color: #64748b; margin-top: 5px;">Total tagihan aktif: <?= mysqli_num_rows($result); ?></p>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Penghuni</th>
                                <th>Kamar</th>
                                <th>Bulan</th>
                                <th>Jumlah</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th>Aksi</th> </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong></td>
                                    <td>
                                        <?php if($row['nomor_kamar']): ?>
                                            <span class="badge-kamar">Kamar <?= htmlspecialchars($row['nomor_kamar']) ?></span>
                                        <?php else: ?>
                                            <span style="color: #ef4444; font-size: 12px; font-weight: 600;">Belum Pilih Kamar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['bulan_tagihan']) ?></td>
                                    <td><strong>Rp <?= number_format($row['jumlah_tagihan'], 0, ',', '.') ?></strong></td>
                                    <td><?= date('d M Y', strtotime($row['jatuh_tempo'])) ?></td>
                                    <td>
                                        <?php if($row['status_tagihan'] == 'Lunas'): ?>
                                            <span class="badge-lunas">Lunas</span>
                                        <?php else: ?>
                                            <span class="badge-belum"><i class="fas fa-exclamation-circle"></i> Belum Bayar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($row['status_tagihan'] != 'Lunas'): ?>
                                            <?php 
                                                // Generate Pesan WhatsApp Otomatis
                                                $pesan = "Halo " . $row['nama_lengkap'] . ",\n\nMengingatkan bahwa tagihan sewa Kost Kamar " . $row['nomor_kamar'] . " untuk bulan *" . $row['bulan_tagihan'] . "* sebesar *Rp " . number_format($row['jumlah_tagihan'], 0, ',', '.') . "* akan jatuh tempo pada *" . date('d M Y', strtotime($row['jatuh_tempo'])) . "*.\n\nMohon untuk segera melakukan pelunasan pembayaran. Terima kasih.";
                                                $link_wa = "https://wa.me/" . $row['no_hp'] . "?text=" . urlencode($pesan);
                                            ?>
                                            <a href="<?= $link_wa ?>" target="_blank" class="btn-wa">
                                                <i class="fab fa-whatsapp"></i> Tagih
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #94a3b8; font-size: 12px; font-style: italic;"><i class="fas fa-check"></i> Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                                        Belum ada data tagihan aktif yang tercatat.
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