<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:login.php");
    exit();
}

if (isset($_POST['tambah_pengeluaran'])) {
    $nama_p = mysqli_real_escape_string($conn, $_POST['nama_pengeluaran']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $ket = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $query_ins = "INSERT INTO pengeluaran (nama_pengeluaran, kategori, jumlah, tanggal, keterangan) 
                  VALUES ('$nama_p', '$kategori', '$jumlah', '$tanggal', '$ket')";
    
    if (mysqli_query($conn, $query_ins)) {
        echo "<script>alert('Pengeluaran berhasil dicatat!'); window.location='pengeluaran.php';</script>";
    }
}

if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    mysqli_query($conn, "DELETE FROM pengeluaran WHERE id_pengeluaran = '$id'");
    echo "<script>alert('Data dihapus!'); window.location='pengeluaran.php';</script>";
}

$sql = "SELECT * FROM pengeluaran ORDER BY tanggal DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html> 
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengeluaran - KostManager</title>
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
        
        .btn-add { background: #4f46e5; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        <header class="header">
            <div class="header-left">
                <h2 style="margin:0; font-size: 18px; color: #1e293b;">Manajemen Pengeluaran</h2>
            </div>
            <div class="header-right">
                <span style="color: #64748b; font-size: 14px;"><i class="far fa-calendar-alt"></i> <?= date('d M Y') ?></span>
            </div>
        </header>

        <main class="main-content">
            <div class="data-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <div>
                        <h1 style="margin: 0; font-size: 24px; color: #1e293b;">Daftar Pengeluaran</h1>
                        <p style="color: #64748b; margin-top: 5px;">Catatan biaya operasional kost</p>
                    </div>
                    <button class="btn-add" onclick="document.getElementById('modalPengeluaran').style.display='flex'">
                        <i class="fas fa-plus"></i> Catat Pengeluaran
                    </button>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Pengeluaran</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama_pengeluaran']) ?></strong></td>
                                    <td><span style="background:#f1f5f9; padding:5px 10px; border-radius:15px; font-size:12px;"><?= htmlspecialchars($row['kategori']) ?></span></td>
                                    <td><strong>Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></strong></td>
                                    <td>
                                        <a href="pengeluaran.php?hapus=<?= $row['id_pengeluaran'] ?>" onclick="return confirm('Hapus catatan ini?')" style="color: #fb7185; text-decoration: none;">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">Belum ada data pengeluaran.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>

    <div id="modalPengeluaran" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
        <div style="background:white; padding:25px; border-radius:12px; width:450px;">
            <h2 style="margin-top:0; color:#1e293b;">Catat Pengeluaran</h2>
            <form method="POST">
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Nama Pengeluaran</label>
                    <input type="text" name="nama_pengeluaran" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;" required>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Kategori</label>
                    <select name="kategori" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;">
                        <option value="Utilitas">Utilitas (Listrik/Air)</option>
                        <option value="Pemeliharaan">Pemeliharaan</option>
                        <option value="Kebersihan">Kebersihan</option>
                        <option value="SDM">Gaji/SDM</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Jumlah (Rp)</label>
                    <input type="number" name="jumlah" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;" required>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Tanggal</label>
                    <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;" required>
                </div>
                <div style="text-align:right; margin-top:20px;">
                    <button type="button" onclick="document.getElementById('modalPengeluaran').style.display='none'" style="padding:10px 15px; border:none; background:#f1f5f9; border-radius:8px; cursor:pointer;">Batal</button>
                    <button type="submit" name="tambah_pengeluaran" style="padding:10px 15px; background:#4f46e5; color:white; border:none; border-radius:8px; cursor:pointer;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>