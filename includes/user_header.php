<?php
// Ambil langsung dari session agar aman di semua halaman
$_header_nama  = $_SESSION['nama_lengkap'] ?? 'Penyewa';
$_header_foto  = $_SESSION['foto_profil']  ?? '';
?>
<header class="header">
    <div class="header-left">
        <h2 style="margin: 0; font-size: 18px; color: #1e293b;">Panel Penghuni</h2>
    </div>
    <div class="header-right" style="display: flex; align-items: center; gap: 20px;">
        <span style="color: #64748b; font-size: 14px;">
            <i class="far fa-calendar-alt"></i> <?= date('d M Y') ?>
        </span>
        <div style="display: flex; align-items: center; gap: 10px; border-left: 1px solid #e2e8f0; padding-left: 20px;">
            <?php if (!empty($_header_foto)): ?>
                <img src="uploads/<?= htmlspecialchars($_header_foto) ?>" 
                     style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
            <?php else: ?>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_header_nama) ?>&background=4f46e5&color=fff" 
                     style="width: 35px; height: 35px; border-radius: 50%;">
            <?php endif; ?>
            <span style="font-weight: 600; font-size: 14px; color: #1e293b;">
                <?= htmlspecialchars($_header_nama) ?>
            </span>
        </div>
    </div>
</header>