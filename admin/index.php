<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

checkAuth('admin');

$pageTitle = 'Dashboard Admin - Prillia';
$activePage = 'dashboard';

// Fetch stats
$countBuku = $pdo->query("SELECT COUNT(*) FROM buku")->fetchColumn();
$countUser = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$countPinjam = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE status = 'dipinjam'")->fetchColumn();
$countTotalTransaksi = $pdo->query("SELECT COUNT(*) FROM transaksi")->fetchColumn();

// Fetch latest transactions
$stmt = $pdo->query("SELECT t.*, u.nama as nama_user, b.judul 
                     FROM transaksi t 
                     JOIN users u ON t.id_user = u.id_user 
                     JOIN buku b ON t.id_buku = b.id_buku 
                     ORDER BY t.tanggal_pinjam DESC LIMIT 5");
$recentTransactions = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="container" style="margin-top: 2.5rem;">
    <header style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.875rem; font-weight: 700;">Dashboard Overview</h1>
        <p style="color: var(--text-muted);">Selamat datang, <?php echo $_SESSION['nama']; ?>. Berikut adalah ringkasan sistem saat ini.</p>
    </header>

    <div class="grid grid-cols-3" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
        <div class="card stat-card">
            <div class="stat-icon icon-blue">
                <i class="fas fa-book"></i>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: var(--text-muted); font-weight: 500;">Total Buku</div>
                <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $countBuku; ?></div>
            </div>
        </div>
        <div class="card stat-card">
            <div class="stat-icon icon-purple">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: var(--text-muted); font-weight: 500;">Total Anggota</div>
                <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $countUser; ?></div>
            </div>
        </div>
        <div class="card stat-card">
            <div class="stat-icon icon-orange">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: var(--text-muted); font-weight: 500;">Buku Dipinjam</div>
                <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $countPinjam; ?></div>
            </div>
        </div>
        <div class="card stat-card">
            <div class="stat-icon icon-green">
                <i class="fas fa-history"></i>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: var(--text-muted); font-weight: 500;">Total Transaksi</div>
                <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $countTotalTransaksi; ?></div>
            </div>
        </div>
    </div>

    <div style="margin-top: 3rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h2 style="font-size: 1.25rem; font-weight: 600;">Transaksi Terbaru</h2>
            <a href="transaksi.php" style="color: var(--primary); font-weight: 600; font-size: 0.875rem;">Lihat Semua <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Peminjam</th>
                        <th>Judul Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentTransactions)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">Belum ada transaksi</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentTransactions as $row): ?>
                            <tr>
                                <td style="font-weight: 500; font-size: 0.875rem;"><?php echo htmlspecialchars($row['nama_user']); ?></td>
                                <td style="font-size: 0.875rem;"><?php echo htmlspecialchars($row['judul']); ?></td>
                                <td style="font-size: 0.875rem;"><?php echo $row['tanggal_pinjam']; ?></td>
                                <td>
                                    <span class="badge <?php echo ($row['status'] == 'dipinjam') ? 'badge-warning' : 'badge-success'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
