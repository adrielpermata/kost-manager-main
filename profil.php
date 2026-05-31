<?php
session_start();
require 'koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['role'])) {
    header("location:login.php");
    exit();
}

$role        = $_SESSION['role'];
$id_penghuni = $_SESSION['id_penghuni'] ?? 0;

// ============================================================
// FIX: Ambil id_user dari session, atau cari pakai fallback
// Root cause: login.php tidak menyimpan $_SESSION['id_user']
// ============================================================
if (isset($_SESSION['id_user']) && $_SESSION['id_user'] > 0) {
    $id_user = $_SESSION['id_user'];
} elseif (isset($_SESSION['nama_lengkap'])) {
    // Fallback: cari id_user berdasarkan nama di session
    $nama_session = mysqli_real_escape_string($conn, $_SESSION['nama_lengkap']);
    $q_fallback   = mysqli_query($conn, "SELECT id_user FROM users WHERE nama_lengkap = '$nama_session' LIMIT 1");
    $row_fallback = mysqli_fetch_assoc($q_fallback);
    if ($row_fallback) {
        $id_user = $row_fallback['id_user'];
        $_SESSION['id_user'] = $id_user; // Simpan agar tidak fallback terus
    } else {
        session_destroy();
        header("location:login.php");
        exit();
    }
} else {
    session_destroy();
    header("location:login.php");
    exit();
}

// Ambil data user dari database
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$id_user'");
$data_user  = mysqli_fetch_assoc($query_user);

// Jika data user tidak ditemukan, paksa logout
if (!$data_user) {
    session_destroy();
    header("location:login.php");
    exit();
}

$error   = null;
$success = null;

// Proses Update Profil & Foto
if (isset($_POST['update_profil'])) {
    $nama_baru     = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email_baru    = mysqli_real_escape_string($conn, $_POST['email']);
    $password_baru = mysqli_real_escape_string($conn, $_POST['password']);

    // 1. Update Data Teks
    if (!empty($password_baru)) {
        $sql_update = "UPDATE users SET nama_lengkap = '$nama_baru', email = '$email_baru', password = '$password_baru' WHERE id_user = '$id_user'";
    } else {
        $sql_update = "UPDATE users SET nama_lengkap = '$nama_baru', email = '$email_baru' WHERE id_user = '$id_user'";
    }
    mysqli_query($conn, $sql_update);

    // 2. Upload Foto Profil
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
        $folder_uploads = 'uploads/';
        if (!is_dir($folder_uploads)) {
            mkdir($folder_uploads, 0777, true);
        }

        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name   = $_FILES['foto_profil']['name'];
        $file_tmp    = $_FILES['foto_profil']['tmp_name'];
        $file_ext    = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
            $new_name    = 'profil_' . $role . '_' . $id_user . '_' . time() . '.' . $file_ext;
            $destination = $folder_uploads . $new_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                if (!empty($data_user['foto_profil']) && file_exists($folder_uploads . $data_user['foto_profil'])) {
                    unlink($folder_uploads . $data_user['foto_profil']);
                }
                mysqli_query($conn, "UPDATE users SET foto_profil = '$new_name' WHERE id_user = '$id_user'");
                $_SESSION['foto_profil'] = $new_name;
            }
        } else {
            $error = "Format file tidak didukung. Hanya JPG, PNG, dan GIF.";
        }
    }

    if (!$error) {
        if ($role == 'penyewa' && !empty($id_penghuni)) {
            mysqli_query($conn, "UPDATE penghuni SET nama_lengkap = '$nama_baru' WHERE id_penghuni = '$id_penghuni'");
        }
        $_SESSION['nama_lengkap'] = $nama_baru;

        // Refresh $data_user agar tampilan langsung update tanpa redirect
        $query_user = mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$id_user'");
        $data_user  = mysqli_fetch_assoc($query_user);

        $success = "Profil berhasil diperbarui!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Akun - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; --navbar-height: 70px; --primary-dark: #1e293b; }
        body { margin: 0; display: flex; background-color: #f8fafc; font-family: 'Inter', sans-serif; }

        .sidebar { width: var(--sidebar-width); background: var(--primary-dark) !important; color: white; position: fixed; height: 100vh; display: flex; flex-direction: column; justify-content: space-between; z-index: 1000; top: 0; left: 0; }
        .main-wrapper { flex: 1; margin-left: var(--sidebar-width); display: flex; flex-direction: column; min-height: 100vh; }
        .header { height: var(--navbar-height); background: white; padding: 0 30px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 999; }

        .main-content { padding: 30px; flex: 1; display: flex; justify-content: center; }

        .profile-card { background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); width: 100%; max-width: 600px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b; font-size: 14px; }
        .form-group input { width: 100%; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; transition: 0.3s; box-sizing: border-box; }
        .form-group input:focus { border-color: #4f46e5; outline: none; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        .form-group input[type="file"] { padding: 9px; background: #f8fafc; }

        .btn-save { background: #4f46e5; color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; width: 100%; font-size: 15px; transition: 0.3s; }
        .btn-save:hover { background: #4338ca; }

        .avatar-lg { width: 90px; height: 90px; background: #e0e7ff; color: #4f46e5; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 35px; font-weight: bold; margin: 0 auto 20px; border: 3px solid #c7d2fe; overflow: hidden; }
        .avatar-lg img { width: 100%; height: 100%; object-fit: cover; }

        .alert-success { background: #dcfce7; color: #16a34a; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: center; }
        .alert-error   { background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: center; }
    </style>
</head>
<body>

    <?php
        if ($role == 'admin') { include 'includes/sidebar.php'; }
        else { include 'includes/user_sidebar.php'; }
    ?>

    <div class="main-wrapper">
        <header class="header">
            <div class="header-left">
                <h2 style="margin: 0; font-size: 18px; color: #1e293b;">Pengaturan Profil</h2>
            </div>
            <div class="header-right">
                <span style="color: #64748b; font-size: 14px;"><i class="far fa-calendar-alt"></i> <?= date('d M Y') ?></span>
            </div>
        </header>

        <main class="main-content">
            <div class="profile-card">

                <div class="avatar-lg">
                    <?php if (!empty($data_user['foto_profil'])): ?>
                        <img src="uploads/<?= htmlspecialchars($data_user['foto_profil']) ?>" alt="Foto Profil">
                    <?php else: ?>
                        <?= strtoupper(substr($data_user['nama_lengkap'] ?? 'U', 0, 1)) ?>
                    <?php endif; ?>
                </div>

                <h2 style="text-align: center; margin-top: 0; margin-bottom: 5px; color: #1e293b;">Informasi Akun</h2>
                <p style="text-align: center; color: #64748b; margin-bottom: 25px; font-size: 14px;">Kelola data pribadi dan kredensial login Anda.</p>

                <?php if ($success): ?>
                    <div class="alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">

                    <div class="form-group">
                        <label>Foto Profil (Opsional)</label>
                        <input type="file" name="foto_profil" accept="image/png, image/jpeg, image/jpg, image/gif">
                        <small style="color: #94a3b8; display: block; margin-top: 5px;">*Format didukung: JPG, PNG, GIF. Kosongkan jika tidak ingin mengubah foto.</small>
                    </div>

                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($data_user['nama_lengkap'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email Login</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($data_user['email'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Role Akun</label>
                        <input type="text" value="<?= strtoupper($data_user['role'] ?? '') ?>" disabled style="background: #f1f5f9; cursor: not-allowed; color: #64748b; font-weight: 600;">
                    </div>

                    <div style="border-top: 1px solid #e2e8f0; margin: 25px 0;"></div>

                    <div class="form-group">
                        <label>Ganti Password (Opsional)</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                    </div>

                    <button type="submit" name="update_profil" class="btn-save">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </main>

        <?php
            if ($role == 'admin') { include 'includes/footer.php'; }
            else { include 'includes/user_footer.php'; }
        ?>
    </div>
</body>
</html>