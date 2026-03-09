<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

checkAuth('user');

$pageTitle = 'Pinjaman Saya - Prillia';
$activePage = 'transaksi';

$id_user = $_SESSION['user_id'];

// Handle Return (Student side)
if (isset($_GET['return'])) {
    $id = $_GET['return'];
    
    // Safety check: ensure transaction belongs to this user and is currently borrowed
    $stmt = $pdo->prepare("SELECT id_buku, status FROM transaksi WHERE id_transaksi = ? AND id_user = ?");
    $stmt->execute([$id, $id_user]);
    $trans = $stmt->fetch();
    
    if ($trans && $trans['status'] == 'dipinjam') {
        // Calculate denda (Optional)
        // Rule: 500 per day after 7 days
        $date_pinjam = $pdo->prepare("SELECT tanggal_pinjam FROM transaksi WHERE id_transaksi = ?");
        $date_pinjam->execute([$id]);
        $pinjam_date = new DateTime($date_pinjam->fetchColumn());
        $now = new DateTime();
        $diff = $pinjam_date->diff($now)->days;
        
        $denda = 0;
        if ($diff > 7) {
            $denda = ($diff - 7) * 500;
        }

        // Update transaction
        $stmt = $pdo->prepare("UPDATE transaksi SET status = 'kembali', tanggal_kembali = CURDATE(), denda = ? WHERE id_transaksi = ?");
        $stmt->execute([$denda, $id]);
        
        // Increase stock
        $stmt = $pdo->prepare("UPDATE buku SET stok = stok + 1 WHERE id_buku = ?");
        $stmt->execute([$trans['id_buku']]);
        
        header('Location: transaksi_saya.php?msg=returned');
    } else {
        header('Location: transaksi_saya.php?msg=error');
    }
    exit;
}

// Fetch user transactions
$stmt = $pdo->prepare("SELECT t.*, b.judul, b.pengarang 
                      FROM transaksi t 
                      JOIN buku b ON t.id_buku = b.id_buku 
                      WHERE t.id_user = ? 
                      ORDER BY t.tanggal_pinjam DESC");
$stmt->execute([$id_user]);
$transactions = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="container" style="margin-top: 2.5rem;">
    <header style="margin-bottom: 2.5rem;">
        <h1 style="font-size: 1.875rem; font-weight: 700;">Pinjaman Saya</h1>
        <p style="color: var(--text-muted);">Daftar buku yang pernah dan sedang Anda pinjam.</p>
    </header>

    <?php if (isset($_GET['msg'])): ?>
        <div style="background: <?php echo ($_GET['msg'] == 'success' || $_GET['msg'] == 'returned') ? '#d1fae5' : '#fee2e2'; ?>; color: <?php echo ($_GET['msg'] == 'success' || $_GET['msg'] == 'returned') ? '#065f46' : '#991b1b'; ?>; padding: 1rem; border-radius: 0.75rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas <?php echo ($_GET['msg'] == 'error') ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
            <span>
                <?php 
                    if ($_GET['msg'] == 'returned') echo 'Terima kasih! Buku telah dikembalikan.';
                    elseif ($_GET['msg'] == 'error') echo 'Terjadi kesalahan sistem.';
                ?>
            </span>
        </div>
    <?php endif; ?>

    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Judul Buku</th>
                    <th>Penulis</th>
                    <th>Tgl Pinjam</th>
                    <th>Tgl Kembali</th>
                    <th>Status</th>
                    <th>Denda</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 4rem;">
                            <div style="margin-bottom: 1rem; font-size: 2rem; opacity: 0.3;"><i class="fas fa-history"></i></div>
                            Anda belum memiliki riwayat peminjaman.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $row): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($row['judul']); ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">#T-<?php echo str_pad($row['id_transaksi'], 5, '0', STR_PAD_LEFT); ?></div>
                            </td>
                            <td style="font-size: 0.875rem;"><?php echo htmlspecialchars($row['pengarang']); ?></td>
                            <td style="font-size: 0.875rem; font-weight: 500;"><?php echo date('d M Y', strtotime($row['tanggal_pinjam'])); ?></td>
                            <td style="font-size: 0.875rem;">
                                <?php echo ($row['tanggal_kembali']) ? date('d M Y', strtotime($row['tanggal_kembali'])) : '<span style="color: var(--text-muted)">- Belum -</span>'; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo ($row['status'] == 'dipinjam') ? 'badge-warning' : 'badge-success'; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td style="font-size: 0.875rem; font-weight: 600; color: <?php echo ($row['denda'] > 0) ? 'var(--danger)' : 'inherit'; ?>">
                                Rp <?php echo number_format($row['denda'], 0, ',', '.'); ?>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'dipinjam'): ?>
                                    <a href="?return=<?php echo $row['id_transaksi']; ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; border-color: var(--primary); color: var(--primary);" onclick="return confirm('Konfirmasi pengembalian buku?')">
                                        <i class="fas fa-undo"></i> Kembalikan
                                    </a>
                                <?php else: ?>
                                    <span style="font-size: 0.75rem; color: var(--success); font-weight: 600;"><i class="fas fa-check-circle"></i> Dikembalikan</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
