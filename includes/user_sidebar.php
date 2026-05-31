<div class="sidebar">
    <div>
        <div class="logo"><i class="fas fa-building"></i> KostManager</div>
        <ul class="nav-links">
            <li onclick="window.location='user_dashboard.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'user_dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </li>
            
            <li onclick="window.location='teman_kost.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'teman_kost.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Teman Kost
            </li>

            <li onclick="window.location='profil.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : '' ?>">
                <i class="fas fa-user-edit"></i> Profil Saya
            </li>
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
                    <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Penyewa'); ?>
                </div>
                <div style="font-size: 12px; color: #cbd5e1;">Penghuni Kost</div>
            </div>
        </div>
        <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?')" class="logout-btn" style="color: #ef4444; text-decoration: none; font-weight: 600; margin-top: 10px; display: block; text-align: center;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>