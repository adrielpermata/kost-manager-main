<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Query simple saja ke tabel users
    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password='$password'");
    
    if ($row = mysqli_fetch_assoc($result)) {
        session_start();
        $_SESSION['role'] = $row['role'];
        $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
        $_SESSION['id_user'] = $row['id_user'];
        $_SESSION['id_penghuni'] = $row['id_penghuni'] ?? 0;
        // Ambil id_penghuni yang barusan kita buat di kolom users
        // Pastikan di database kolom ini sudah kamu isi angka (seperti 7), jangan NULL
        $_SESSION['id_penghuni'] = $row['id_penghuni']; 
        $_SESSION['foto_profil'] = $row['foto_profil'];
        if ($row['role'] == 'admin') {
            header("location:admin_dashboard.php");
        } else {
            header("location:user_dashboard.php");
        }
        exit();
    } else {
        $error = "Email atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KostManager</title>
    <link rel="stylesheet" href="login_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="login-card">
        <div class="logo-box">
            <i class="fas fa-building"></i>
        </div>
        <h2>KostManager</h2>
        <p>Sistem Manajemen Kost Modern & Terintegrasi</p>

        <?php if(isset($error)): ?>
            <div style="color: #dc2626; background: #fee2e2; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                <i class="fas fa-exclamation-circle"></i> <?= $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label>Email</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="admin@kost.com" required>
                </div>
            </div>

            <div class="input-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" name="login" class="btn-login">Masuk ke Dashboard</button>
        </form>

        <div class="info-login">INFO LOGIN</div>
        
        <div style="margin-top: 20px; text-align: center; font-size: 14px; color: #64748b;">
    Belum memiliki akun? 
    <a href="register.php" style="color: #4f46e5; text-decoration: none; font-weight: 600;">
        Daftar Sekarang
    </a>
</div>
    </div>

</body>
</html>