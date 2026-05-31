<?php
session_start();
// Menghapus semua data session
session_unset();
// Menghancurkan session
session_destroy();

// Mengarahkan kembali ke halaman login
echo "<script>
    alert('Anda telah berhasil logout.');
    window.location.href = 'login.php';
</script>";
exit();
?>