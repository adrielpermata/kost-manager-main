<?php
include 'koneksi.php';

if (isset($_POST['register'])) {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role     = 'penyewa';

    // STEP 1: Cek apakah email terdaftar di tabel penghuni oleh admin
    $cek_penghuni = mysqli_query($conn, "SELECT * FROM penghuni WHERE email = '$email'");
    if (mysqli_num_rows($cek_penghuni) == 0) {
        $error = "Email ini belum terdaftar sebagai penghuni. Hubungi admin terlebih dahulu.";
    } else {
        // STEP 2: Cek apakah email sudah punya akun
        $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
        if (mysqli_num_rows($cek_user) > 0) {
            $error = "Email ini sudah memiliki akun. Silakan login.";
        } else {
            // STEP 3: Ambil data penghuni untuk disimpan ke tabel users
            $data_penghuni = mysqli_fetch_assoc($cek_penghuni);
            $id_penghuni   = $data_penghuni['id_penghuni'];
            $nama          = $data_penghuni['nama_lengkap'];

            $query = "INSERT INTO users (nama_lengkap, email, password, role, id_penghuni) 
                      VALUES ('$nama', '$email', '$password', '$role', '$id_penghuni')";

            if (mysqli_query($conn, $query)) {
                echo "<script>alert('Registrasi Berhasil! Silakan Login.'); window.location='login.php';</script>";
                exit;
            } else {
                $error = "Gagal Registrasi: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun - KostManager</title>
    <link rel="stylesheet" href="login_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8fafc; font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .reg-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); width: 100%; max-width: 400px; border: 1px solid #e2e8f0; }
        .logo-box { width: 60px; height: 60px; background: #4f46e5; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; }
        .logo-box i { color: white; font-size: 24px; }
        h2 { margin: 0 0 5px 0; color: #1e293b; text-align: center; font-size: 22px; }
        p.subtitle { text-align: center; color: #64748b; font-size: 14px; margin: 0 0 25px 0; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; color: #475569; font-size: 14px; font-weight: 600; }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px; }
        .input-wrapper input { width: 100%; padding: 11px 12px 11px 36px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: 0.2s; }
        .input-wrapper input:focus { border-color: #4f46e5; outline: none; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
        .alert-error { color: #dc2626; background: #fee2e2; padding: 12px; border-radius: 8px; margin-bottom: 18px; font-size: 14px; text-align: center; }
        .alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; padding: 12px; border-radius: 8px; margin-bottom: 18px; font-size: 13px; }
        .btn-reg { width: 100%; padding: 12px; background: #4f46e5; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 15px; transition: 0.2s; }
        .btn-reg:hover { background: #4338ca; }
    </style>
</head>
<body>

<div class="reg-card">
    <div class="logo-box"><i class="fas fa-building"></i></div>
    <h2>Buat Akun</h2>
    <p class="subtitle">KostManager — Sistem Manajemen Kost</p>

    <div class="alert-info">
        <i class="fas fa-info-circle"></i>
        Gunakan email yang telah didaftarkan oleh admin. Nama akun akan diambil otomatis.
    </div>

    <?php if (isset($error)): ?>
        <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email</label>
            <div class="input-wrapper">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email yang didaftarkan admin" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Password</label>
            <div class="input-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Buat password kamu" required minlength="6">
            </div>
        </div>

        <button type="submit" name="register" class="btn-reg">
            <i class="fas fa-user-check"></i> Daftar Sekarang
        </button>

        <p style="text-align:center; font-size:14px; color:#64748b; margin-top:20px; margin-bottom:0;">
            Sudah punya akun? 
            <a href="login.php" style="color:#4f46e5; text-decoration:none; font-weight:600;">Login</a>
        </p>
    </form>
</div>

</body>
</html>