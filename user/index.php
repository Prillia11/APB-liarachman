<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

checkAuth('user');

$pageTitle = 'Dashboard Siswa - Prillia';
$activePage = 'dashboard';

$id_user = $_SESSION['user_id'];

// Stats
$countPinjam = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE id_user = $id_user AND status = 'dipinjam'")->fetchColumn();
$countTotal = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE id_user = $id_user")->fetchColumn();

// Latest pinjam
$stmt = $pdo->prepare("SELECT t.*, b.judul 
                      FROM transaksi t 
                      JOIN buku b ON t.id_buku = b.id_buku 
                      WHERE t.id_user = ? 
                      ORDER BY t.tanggal_pinjam DESC LIMIT 3");
$stmt->execute([$id_user]);
$latest = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="container" style="margin-top: 2.5rem;">
    <div style="background: linear-gradient(135deg, var(--primary) 0%, #a855f7 100%); border-radius: 1.5rem; padding: 3rem; color: white; display: flex; align-items: center; justify-content: space-between; margin-bottom: 3rem; box-shadow: var(--shadow-lg);">
        <div>
            <h1 style="font-size: 2.25rem; font-weight: 700; margin-bottom: 0.5rem;">Halo, <?php echo explode(' ', $_SESSION['nama'])[0]; ?>! 👋</h1>
            <p style="opacity: 0.9; font-size: 1.1rem; max-width: 500px;">Ayo temukan ilmu baru hari ini. Cari buku favoritmu di katalog kami.</p>
            <a href="katalog.php" class="btn" style="background: white; color: var(--primary); margin-top: 1.5rem; padding: 0.75rem 1.5rem;">
                Jelajahi Katalog <i class="fas fa-search"></i>
            </a>
        </div>
        <div style="font-size: 5rem; opacity: 0.2;">
            <i class="fas fa-graduation-cap"></i>
        </div>
    </div>

    <div class="grid grid-cols-3" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
        <div class="card stat-card">
            <div class="stat-icon icon-orange">
                <i class="fas fa-book-reader"></i>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: var(--text-muted); font-weight: 500;">Buku Sedang Dipinjam</div>
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main);"><?php echo $countPinjam; ?></div>
            </div>
        </div>
        <div class="card stat-card">
            <div class="stat-icon icon-blue">
                <i class="fas fa-history"></i>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: var(--text-muted); font-weight: 500;">Total Peminjaman</div>
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main);"><?php echo $countTotal; ?></div>
            </div>
        </div>
        <div class="card" style="padding: 1.5rem; display: flex; flex-direction: column; justify-content: center;">
             <div style="font-weight: 600; color: var(--text-main);">Informasi Akun</div>
             <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;">
                 <span class="badge" style="background: #f1f5f9; color: var(--text-muted);"><?php echo $_SESSION['role']; ?></span>
                 <span class="badge" style="background: #f3e8ff; color: #7e22ce;">Siswa Aktif</span>
             </div>
        </div>
    </div>

    <div style="margin-top: 3.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem;">Riwayat Pinjam Terakhir</h2>
        <?php if (empty($latest)): ?>
            <div class="card" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <p>Belum ada riwayat peminjaman. Ayo mulai membaca!</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-3">
                <?php foreach ($latest as $row): ?>
                    <div class="card" style="padding: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <span class="badge <?php echo ($row['status'] == 'dipinjam') ? 'badge-warning' : 'badge-success'; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                            <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo date('d M Y', strtotime($row['tanggal_pinjam'])); ?></span>
                        </div>
                        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-main);"><?php echo htmlspecialchars($row['judul']); ?></h3>
                        <p style="font-size: 0.875rem; color: var(--text-muted);">Status: <?php echo ($row['status'] == 'dipinjam') ? 'Segera kembalikan tepat waktu' : 'Sudah dikembalikan'; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
