<?php
// Pastikan file koneksi sudah benar
include 'koneksi.php'; 

// Bungkus dengan IF ISSSET agar tidak error "Undefined array key" saat halaman dibuka
if (isset($_POST['tambah_penghuni'])) {
    
    // Gunakan variabel koneksi yang ada di file koneksi.php kamu
    // Jika di koneksi.php kamu pakai $conn, ganti $koneksi jadi $conn
    $nama  = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $nik   = mysqli_real_escape_string($koneksi, $_POST['nik']);
    $hp    = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);

    $query = "INSERT INTO PENGHUNI (nama_lengkap, nik, no_hp, email) 
              VALUES ('$nama', '$nik', '$hp', '$email')";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data Berhasil Disimpan!'); window.location='penghuni.php';</script>";
        exit; // Hentikan script setelah redirect
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}
?>