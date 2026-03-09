<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

checkAuth('admin');

$pageTitle = 'Manajemen Transaksi - Prillia';
$activePage = 'transaksi';

$search = $_GET['search'] ?? '';

// Handle Return
if (isset($_GET['return'])) {
    $id = $_GET['return'];
    
    // Get transaction details
    $stmt = $pdo->prepare("SELECT id_buku, status FROM transaksi WHERE id_transaksi = ?");
    $stmt->execute([$id]);
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
        
        header('Location: transaksi.php?msg=returned');
    } else {
        header('Location: transaksi.php?msg=error');
    }
    exit;
}

// Handle Add Transaction (Admin manual entry)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_transaction'])) {
    $id_user = $_POST['id_user'];
    $id_buku = $_POST['id_buku'];
    $tanggal_pinjam = $_POST['tanggal_pinjam'] ?: date('Y-m-d');

    // Check stock
    $stmt = $pdo->prepare("SELECT stok FROM buku WHERE id_buku = ?");
    $stmt->execute([$id_buku]);
    $stok = $stmt->fetchColumn();

    if ($stok > 0) {
        $stmt = $pdo->prepare("INSERT INTO transaksi (id_user, id_buku, tanggal_pinjam, status) VALUES (?, ?, ?, 'dipinjam')");
        $stmt->execute([$id_user, $id_buku, $tanggal_pinjam]);

        $stmt = $pdo->prepare("UPDATE buku SET stok = stok - 1 WHERE id_buku = ?");
        $stmt->execute([$id_buku]);

        header('Location: transaksi.php?msg=added');
    } else {
        header('Location: transaksi.php?msg=no_stock');
    }
    exit;
}

// Fetch Data for Select
$users = $pdo->query("SELECT id_user, nama FROM users WHERE role = 'user' ORDER BY nama")->fetchAll();
$books = $pdo->query("SELECT id_buku, judul, stok FROM buku WHERE stok > 0 ORDER BY judul")->fetchAll();

// Fetch Transaksi
$query = "SELECT t.*, u.nama as nama_user, b.judul 
          FROM transaksi t 
          JOIN users u ON t.id_user = u.id_user 
          JOIN buku b ON t.id_buku = b.id_buku 
          WHERE u.nama LIKE ? OR b.judul LIKE ? OR t.status LIKE ?
          ORDER BY t.tanggal_pinjam DESC";
$stmt = $pdo->prepare($query);
$stmt->execute(["%$search%", "%$search%", "%$search%"]);
$transactions = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="container" style="margin-top: 2.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.875rem; font-weight: 700;">Manajemen Transaksi</h1>
            <p style="color: var(--text-muted);">Pantau dan kelola peminjaman buku.</p>
        </div>
        <button onclick="document.getElementById('transaksiFormCard').style.display='block'; window.scrollTo(0,0);" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Catat Peminjaman Baru
        </button>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div style="background: <?php echo ($_GET['msg'] == 'error' || $_GET['msg'] == 'no_stock') ? '#fee2e2' : '#d1fae5'; ?>; color: <?php echo ($_GET['msg'] == 'error' || $_GET['msg'] == 'no_stock') ? '#991b1b' : '#065f46'; ?>; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas <?php echo ($_GET['msg'] == 'error' || $_GET['msg'] == 'no_stock') ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
            <span>
                <?php 
                    if ($_GET['msg'] == 'added') echo 'Transaksi peminjaman berhasil dicatat!';
                    elseif ($_GET['msg'] == 'returned') echo 'Buku telah berhasil dikembalikan!';
                    elseif ($_GET['msg'] == 'no_stock') echo 'Maaf, stok buku tidak tersedia!';
                    elseif ($_GET['msg'] == 'error') echo 'Terjadi kesalahan sistem!';
                ?>
            </span>
        </div>
    <?php endif; ?>

    <!-- Modal Form (Inline) -->
    <div id="transaksiFormCard" class="card" style="display: none; margin-bottom: 2.5rem; padding: 2rem; border-left: 5px solid var(--primary);">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Catat Peminjaman Baru</h2>
        <form action="" method="POST" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
            <input type="hidden" name="add_transaction" value="1">
            <div class="input-group">
                <label>Pilih Anggota / Siswa</label>
                <select name="id_user" class="input-control" required>
                    <option value="">-- Pilih Anggota --</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?php echo $u['id_user']; ?>"><?php echo htmlspecialchars($u['nama']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Pilih Buku</label>
                <select name="id_buku" class="input-control" required>
                    <option value="">-- Pilih Buku (Stok > 0) --</option>
                    <?php foreach ($books as $b): ?>
                        <option value="<?php echo $b['id_buku']; ?>"><?php echo htmlspecialchars($b['judul']); ?> (Stok: <?php echo $b['stok']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Tanggal Pinjam</label>
                <input type="date" name="tanggal_pinjam" class="input-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div style="grid-column: span 1; display: flex; gap: 1rem; align-items: flex-end; padding-bottom: 1.25rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Proses Pinjam <i class="fas fa-check-double"></i></button>
                <button type="button" onclick="document.getElementById('transaksiFormCard').style.display='none'" class="btn btn-outline">Batal</button>
            </div>
        </form>
    </div>

    <!-- Filter & Search -->
    <div style="margin-bottom: 1.5rem;">
        <form action="" method="GET" style="display: flex; gap: 0.5rem; max-width: 500px;">
            <input type="text" name="search" class="input-control" placeholder="Cari peminjam, judul, atau status..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            <?php if ($search): ?>
                <a href="transaksi.php" class="btn btn-outline">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Peminjam</th>
                    <th>Detail Buku</th>
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
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem;">Belum ada riwayat transaksi.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $row): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($row['nama_user']); ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">#ID Trans: <?php echo $row['id_transaksi']; ?></div>
                            </td>
                            <td>
                                <div style="font-size: 0.875rem; font-weight: 500;"><?php echo htmlspecialchars($row['judul']); ?></div>
                            </td>
                            <td style="font-size: 0.875rem;"><?php echo date('d M Y', strtotime($row['tanggal_pinjam'])); ?></td>
                            <td style="font-size: 0.875rem;">
                                <?php echo ($row['tanggal_kembali']) ? date('d M Y', strtotime($row['tanggal_kembali'])) : '<span style="color: var(--text-muted)">-</span>'; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo ($row['status'] == 'dipinjam') ? 'badge-warning' : 'badge-success'; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.875rem; font-weight: 600; color: <?php echo ($row['denda'] > 0) ? 'var(--danger)' : 'inherit'; ?>">
                                    Rp <?php echo number_format($row['denda'], 0, ',', '.'); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'dipinjam'): ?>
                                    <a href="?return=<?php echo $row['id_transaksi']; ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; border-color: var(--success); color: var(--success);" onclick="return confirm('Konfirmasi pengembalian buku?')">
                                        <i class="fas fa-undo"></i> Kembalikan
                                    </a>
                                <?php else: ?>
                                    <span style="font-size: 0.75rem; color: var(--success); font-weight: 600;"><i class="fas fa-check-circle"></i> Selesai</span>
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
