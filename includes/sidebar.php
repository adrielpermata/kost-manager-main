<div class="sidebar">
    <div>
        <div class="logo"><i class="fas fa-building"></i> KostManager</div>
        <ul class="nav-links">
            <li onclick="window.location='admin_dashboard.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : '' ?>"><i class="fas fa-th-large"></i> Dashboard</li>
            <li onclick="window.location='kamar.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'kamar.php' ? 'active' : '' ?>"><i class="fas fa-bed"></i> Kamar</li>
            <li onclick="window.location='penghuni.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'penghuni.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Penyewa</li>
            <li onclick="window.location='pembayaran.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'pembayaran.php' ? 'active' : '' ?>"><i class="fas fa-wallet"></i> Pembayaran</li>
            <li onclick="window.location='tagihan.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'tagihan.php' ? 'active' : '' ?>"><i class="fas fa-file-alt"></i> Tagihan</li>
            <li onclick="window.location='pengeluaran.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'pengeluaran.php' ? 'active' : '' ?>"><i class="fas fa-wallet"></i> Pengeluaran</li>
            <li onclick="window.location='laporan.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> Laporan</li>
            <li onclick="window.location='whatsapp.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'whatsapp.php' ? 'active' : '' ?>"><i class="fab fa-whatsapp"></i> WhatsApp</li>
            
            <li onclick="window.location='profil.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : '' ?>"><i class="fas fa-user-cog"></i> Profil Akun</li>
        </ul>   
    </div>
    
    <div class="user-profile" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
        <div style="display: flex; align-items: center; gap: 10px; padding: 0 10px 10px 10px;">
            <?php if(!empty($_SESSION['foto_profil'])): ?>
                <img src="uploads/<?= htmlspecialchars($_SESSION['foto_profil']) ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0;">
            <?php else: ?>
                <i class="fas fa-user-circle" style="font-size: 40px; color: #e2e8f0;"></i>
            <?php endif; ?>
            
            <div>
                <div style="font-size: 14px; font-weight: 600; color: white;">
                    <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin'); ?>
                </div>
                <div style="font-size: 12px; color: #cbd5e1;">Administrator</div>
            </div>
        </div>
        <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?')" class="logout-btn" style="color: #ef4444; text-decoration: none; font-weight: 600; margin-top: 10px; display: block; text-align: center;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
    <div class="user-profile">
        <div style="display: flex; align-items: center; gap: 10px; padding: 0 20px 10px 20px;">
            <?php if(!empty($_SESSION['foto_profil'])): ?>
                <img src="uploads/<?= $_SESSION['foto_profil'] ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
            <?php else: ?>
                <i class="fas fa-user-circle" style="font-size: 35px;"></i>
            <?php endif; ?>
            
            <div>
                <span style="font-size: 14px; font-weight: 600; display: block; color: white;">
                    <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin'); ?>
                </span>
                <span style="font-size: 12px; color: #cbd5e1;">Administrator</span>
            </div>
        </div>
        <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?')" class="logout-btn" style="color: #ef4444; font-weight: 600; text-align: center;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>