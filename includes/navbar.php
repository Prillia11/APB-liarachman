<nav class="navbar">
    <div class="container nav-content">
        <a href="../index.php" class="logo">
            <i class="fas fa-book-reader"></i> Prillia
        </a>
        
        <div class="nav-links">
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="../admin/index.php" class="nav-link <?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
                <a href="../admin/buku.php" class="nav-link <?php echo ($activePage == 'buku') ? 'active' : ''; ?>">Kelola Buku</a>
                <a href="../admin/anggota.php" class="nav-link <?php echo ($activePage == 'anggota') ? 'active' : ''; ?>">Anggota</a>
                <a href="../admin/transaksi.php" class="nav-link <?php echo ($activePage == 'transaksi') ? 'active' : ''; ?>">Transaksi</a>
            <?php else: ?>
                <a href="../user/index.php" class="nav-link <?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
                <a href="../user/katalog.php" class="nav-link <?php echo ($activePage == 'katalog') ? 'active' : ''; ?>">Katalog Buku</a>
                <a href="../user/transaksi_saya.php" class="nav-link <?php echo ($activePage == 'transaksi') ? 'active' : ''; ?>">Pinjaman Saya</a>
            <?php endif; ?>
        </div>

        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="text-align: right; line-height: 1.2;">
                <div style="font-weight: 600; font-size: 0.875rem;"><?php echo $_SESSION['nama']; ?></div>
                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: capitalize;"><?php echo $_SESSION['role']; ?></div>
            </div>
            <a href="../auth/logout.php" class="btn btn-outline" style="padding: 0.5rem 0.75rem;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</nav>
