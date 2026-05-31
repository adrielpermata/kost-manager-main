<?php
session_start();
require 'koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:login.php");
    exit();
}

// --- 1. TAMBAH PENYEWA BARU (SINKRON DENGAN KAMAR & TAGIHAN) ---
if (isset($_POST['tambah_penghuni'])) {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nik      = mysqli_real_escape_string($conn, $_POST['nik']);
    $hp       = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $tanggal  = mysqli_real_escape_string($conn, $_POST['tgl_masuk']);
    $id_kamar = mysqli_real_escape_string($conn, $_POST['id_kamar']); // Mengambil ID kamar dari form

    // A. Insert ke tabel penghuni
    $query_insert = "INSERT INTO penghuni (nama_lengkap, nik, no_hp, email, tgl_masuk, id_kamar, status_pembayaran, status_penyewa) 
                     VALUES ('$nama', '$nik', '$hp', '$email', '$tanggal', '$id_kamar', 'Belum Bayar', 'Aktif')";
    
    if (mysqli_query($conn, $query_insert)) {
        // Ambil ID penghuni yang baru saja di-generate
        $id_penghuni_baru = mysqli_insert_id($conn);

        // B. Update status kamar menjadi Terisi
        mysqli_query($conn, "UPDATE kamar SET status = 'Terisi' WHERE id_kamar = '$id_kamar'");

        // C. Buat Tagihan Pertama secara otomatis (Sesuai Harga Kamar)
        $q_kamar = mysqli_query($conn, "SELECT harga_per_bulan FROM kamar WHERE id_kamar = '$id_kamar'");
        $harga_kamar = mysqli_fetch_assoc($q_kamar)['harga_per_bulan'];
        
        $bulan_tagihan = date('F Y', strtotime($tanggal));
        $jatuh_tempo = date('Y-m-d', strtotime('+10 days', strtotime($tanggal))); 

        mysqli_query($conn, "INSERT INTO tagihan (bulan_tagihan, jatuh_tempo, jumlah_tagihan, status_tagihan, id_penghuni) 
                             VALUES ('$bulan_tagihan', '$jatuh_tempo', '$harga_kamar', 'Belum Lunas', '$id_penghuni_baru')");

        echo "<script>alert('Penyewa berhasil ditambah! Kamar langsung Terisi & Tagihan otomatis dibuat.'); window.location='penghuni.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menambah penyewa: " . mysqli_error($conn) . "');</script>";
    }
}

// --- 2. LOGIKA SIMPAN EDIT PENGHUNI ---
if (isset($_POST['simpan_edit'])) {
    $id    = $_POST['id_penghuni'];
    $nama  = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $hp    = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $nik   = mysqli_real_escape_string($conn, $_POST['nik']);
    $kamar = mysqli_real_escape_string($conn, $_POST['id_kamar']);

    // Ambil ID kamar yang lama
    $q_lama = mysqli_query($conn, "SELECT id_kamar FROM penghuni WHERE id_penghuni = '$id'");
    $kamar_lama = mysqli_fetch_assoc($q_lama)['id_kamar'];

    $sql = "UPDATE penghuni SET 
            nama_lengkap = '$nama', 
            no_hp = '$hp', 
            email = '$email', 
            nik = '$nik', 
            id_kamar = '$kamar' 
            WHERE id_penghuni = '$id'";

    if (mysqli_query($conn, $sql)) {
        // Jika kamarnya dipindah, kamar lama jadi tersedia, kamar baru jadi terisi
        if ($kamar_lama != $kamar) {
            mysqli_query($conn, "UPDATE kamar SET status = 'Tersedia' WHERE id_kamar = '$kamar_lama'");
            mysqli_query($conn, "UPDATE kamar SET status = 'Terisi' WHERE id_kamar = '$kamar'");
        }
        echo "<script>alert('Perubahan Data Berhasil Disimpan!'); window.location='penghuni.php';</script>";
    }
}

// --- 3. CHECKOUT (SELESAI) ---
if (isset($_GET['set_selesai'])) {
    $id_p = mysqli_real_escape_string($conn, $_GET['set_selesai']);
    
    $query_kamar = mysqli_query($conn, "SELECT id_kamar FROM penghuni WHERE id_penghuni = '$id_p'");
    $data_p = mysqli_fetch_assoc($query_kamar);
    $id_kamar = $data_p['id_kamar'];

    mysqli_query($conn, "UPDATE penghuni SET status_penyewa = 'Selesai', status_pembayaran = 'Lunas' WHERE id_penghuni = '$id_p'");
    
    if (!empty($id_kamar)) {
        mysqli_query($conn, "UPDATE kamar SET status = 'Tersedia' WHERE id_kamar = '$id_kamar'");
    }
    echo "<script>alert('Penyewa telah Selesai (Checkout). Kamar kini tersedia kembali!'); window.location='penghuni.php';</script>";
}

// --- 4. FILTER STATUS PENGHUNI ---
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'Aktif';
$sql = "SELECT penghuni.*, kamar.nomor_kamar 
        FROM penghuni 
        LEFT JOIN kamar ON penghuni.id_kamar = kamar.id_kamar 
        WHERE penghuni.status_penyewa = '$filter'
        ORDER BY penghuni.nama_lengkap ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Penyewa - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; --navbar-height: 70px; --primary-bg: #1e293b; }
        body { margin: 0; display: flex; background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        
        .sidebar { width: var(--sidebar-width); background: var(--primary-bg) !important; color: white; position: fixed; height: 100vh; display: flex; flex-direction: column; justify-content: space-between; z-index: 1000; }
        .main-wrapper { flex: 1; margin-left: var(--sidebar-width); display: flex; flex-direction: column; min-height: 100vh; }
        .header { height: var(--navbar-height); background: white; padding: 0 30px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 999; }
        .main-content { padding: 30px; flex: 1; }
        .footer { background: white; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 14px; margin-top: auto; }
        
        .data-section { background: white; border-radius: 12px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f8fafc; color: #64748b; font-weight: 600; text-align: left; padding: 12px; border-bottom: 2px solid #edf2f7; text-transform: uppercase; font-size: 12px; }
        td { padding: 15px 12px; border-bottom: 1px solid #edf2f7; vertical-align: middle; font-size: 14px; }
        .badge-aktif { background: #dcfce7; color: #15803d; padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .badge-selesai { background: #f1f5f9; color: #64748b; padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        <header class="header">
            <div class="header-left">
                <h2 style="margin: 0; font-size: 18px; color: #1e293b;">Manajemen Penyewa</h2>
            </div>
            <div class="header-right" style="display: flex; align-items: center; gap: 20px;">
                <div style="color: #64748b; font-size: 14px;"><i class="far fa-calendar-alt"></i> <?= date('d M Y') ?></div>
            </div>
        </header>

        <main class="main-content">
            <div class="data-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h1 style="margin: 0; font-size: 24px;">Daftar Penghuni</h1>
                        <p style="color: #64748b; margin: 5px 0 0 0;">Total: <?= mysqli_num_rows($result); ?> orang (Status: <?= $filter ?>)</p>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <select onchange="location.href='penghuni.php?filter=' + this.value" style="padding: 10px; border-radius: 8px; border: 1px solid #ddd; cursor:pointer; background: white; font-weight: 600;">
                            <option value="Aktif" <?= $filter == 'Aktif' ? 'selected' : '' ?>>Penyewa Aktif</option>
                            <option value="Selesai" <?= $filter == 'Selesai' ? 'selected' : '' ?>>Sudah Keluar (Riwayat)</option>
                        </select>
                        <button type="button" style="background: #4f46e5; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;" onclick="document.getElementById('modalTambahPenyewa').style.display='flex'">
                            <i class="fas fa-user-plus"></i> Tambah Penyewa
                        </button>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Lengkap</th>
                                <th>Kamar</th>
                                <th>No. Telepon</th>
                                <th>Tgl Masuk</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong></td>
                                    <td>
                                        <?php if($row['nomor_kamar']): ?>
                                            <span style="background:#e0e7ff; color:#4338ca; padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight:600;">Kamar <?= $row['nomor_kamar'] ?></span>
                                        <?php else: ?>
                                            <span style="background:#fee2e2; color:#dc2626; padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600;">Belum Pilih Kamar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><code style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; color: #475569;"><?= $row['no_hp'] ?></code></td>
                                    <td><?= ($row['tgl_masuk'] && $row['tgl_masuk'] != '0000-00-00') ? date('d M Y', strtotime($row['tgl_masuk'])) : '-'; ?></td>
                                    <td>
                                        <?php if($row['status_penyewa'] == 'Aktif'): ?>
                                            <span class="badge-aktif">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge-selesai">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 15px; align-items: center;">
                                            <?php if($row['status_penyewa'] == 'Aktif'): ?>
                                                <a href="penghuni.php?set_selesai=<?= $row['id_penghuni'] ?>" onclick="return confirm('Yakin penyewa ini sudah selesai (Checkout)? Kamar akan otomatis menjadi Tersedia kembali.')" style="color: #ef4444; font-size: 11px; font-weight: 700; text-decoration:none; border: 1px solid #ef4444; padding: 5px 10px; border-radius: 6px; text-transform: uppercase;">Checkout</a>
                                            <?php endif; ?>
                                            <button type="button" style="background:none; border:none;" onclick="bukaModalEdit('<?= $row['id_penghuni'] ?>', '<?= $row['nama_lengkap'] ?>', '<?= $row['no_hp'] ?>', '<?= $row['email'] ?>', '<?= $row['nik'] ?>', '<?= $row['id_kamar'] ?>')">
                                                <i class="fas fa-edit" style="color: #3b82f6; cursor:pointer; font-size: 16px;" title="Edit Data"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center; padding:50px; color:#94a3b8;">Tidak ada data penyewa.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>

    <div id="modalTambahPenyewa" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
        <div style="background:#fff; padding:25px; border-radius:12px; width:500px; max-width:90%;">
            <h3 style="margin-top:0; color:#1e293b; margin-bottom: 20px;">Tambah Penyewa Baru</h3>
            <form action="penghuni.php" method="POST">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 14px;">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box;" required>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 14px;">Pilih Kamar (Wajib) *</label>
                    <select name="id_kamar" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: white;" required>
                        <option value="">-- Pilih Kamar Tersedia --</option>
                        <?php
                        $k_tersedia = mysqli_query($conn, "SELECT id_kamar, nomor_kamar, harga_per_bulan FROM kamar WHERE status = 'Tersedia' ORDER BY nomor_kamar ASC");
                        while($k = mysqli_fetch_assoc($k_tersedia)) {
                            echo "<option value='".$k['id_kamar']."'>Kamar ".$k['nomor_kamar']." (Rp ".number_format($k['harga_per_bulan'],0,',','.').")</option>";
                        }
                        ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 14px;">NIK KTP *</label>
                        <input type="text" name="nik" placeholder="16 Digit NIK" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box;" required>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 14px;">No WhatsApp *</label>
                        <input type="text" name="no_hp" placeholder="0812345..." style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box;" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 14px;">Email</label>
                        <input type="email" name="email" placeholder="email@domain.com" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 14px;">Tgl Masuk *</label>
                        <input type="date" name="tgl_masuk" value="<?= date('Y-m-d'); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box;" required>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="document.getElementById('modalTambahPenyewa').style.display='none'" style="flex: 1; padding: 12px; background-color: #f1f5f9; color: #475569; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Batal</button>
                    <button type="submit" name="tambah_penghuni" style="flex: 1; padding: 12px; background-color: #10b981; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEditPenghuni" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
        <div style="background:#fff; padding:25px; border-radius:12px; width:450px; max-width:95%;">
            <h3 style="margin-top:0; color:#1e293b; margin-bottom:20px;">Edit Data Penghuni</h3>
            <form action="penghuni.php" method="POST">
                <input type="hidden" id="edit_id" name="id_penghuni">

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:5px; font-size: 14px;">Nama Lengkap</label>
                    <input type="text" id="edit_nama" name="nama_lengkap" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; box-sizing: border-box;" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 12px;">
                    <div>
                        <label style="display:block; font-weight:600; margin-bottom:5px; font-size: 14px;">Nomor WA</label>
                        <input type="text" id="edit_hp" name="no_hp" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; box-sizing: border-box;" required>
                    </div>
                    <div>
                        <label style="display:block; font-weight:600; margin-bottom:5px; font-size: 14px;">NIK</label>
                        <input type="text" id="edit_nik" name="nik" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; box-sizing: border-box;" required>
                    </div>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:5px; font-size: 14px;">Alamat Email</label>
                    <input type="email" id="edit_email" name="email" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; box-sizing: border-box;" required>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:600; margin-bottom:5px; font-size: 14px;">Pindah Kamar (Opsional)</label>
                    <select id="edit_kamar" name="id_kamar" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; box-sizing: border-box;" required>
                        <?php
                        $kamar_semua = mysqli_query($conn, "SELECT id_kamar, nomor_kamar, status FROM kamar ORDER BY nomor_kamar ASC");
                        while($k = mysqli_fetch_assoc($kamar_semua)) {
                            $stat = ($k['status'] == 'Terisi') ? ' (Terisi)' : '';
                            echo "<option value='".$k['id_kamar']."'>Kamar ".$k['nomor_kamar'].$stat."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div style="display:flex; gap: 10px;">
                    <button type="button" onclick="document.getElementById('modalEditPenghuni').style.display='none'" style="flex:1; padding:12px; border:none; background:#f1f5f9; cursor:pointer; border-radius:8px; font-weight:600; color: #475569;">Batal</button>
                    <button type="submit" name="simpan_edit" style="flex:1; padding:12px; border:none; background:#3b82f6; color:white; cursor:pointer; border-radius:8px; font-weight:bold;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function bukaModalEdit(id, nama, hp, email, nik, kamar) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_hp').value = hp;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_nik').value = nik;
        document.getElementById('edit_kamar').value = kamar;
        document.getElementById('modalEditPenghuni').style.display = 'flex';
    }
    </script>
</body>
</html>