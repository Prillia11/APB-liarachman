<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

checkAuth('user');

$pageTitle = 'Katalog Buku - Prillia';
$activePage = 'katalog';

$search = $_GET['search'] ?? '';

// Handle Borrow
if (isset($_GET['borrow'])) {
    $id_buku = $_GET['borrow'];
    $id_user = $_SESSION['user_id'];

    // Check if already borrowing this book (and not returned)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE id_user = ? AND id_buku = ? AND status = 'dipinjam'");
    $stmt->execute([$id_user, $id_buku]);
    $alreadyBorrowing = $stmt->fetchColumn();

    if ($alreadyBorrowing > 0) {
        header('Location: katalog.php?msg=already_borrowed');
    } else {
        // Check stock
        $stmt = $pdo->prepare("SELECT stok FROM buku WHERE id_buku = ?");
        $stmt->execute([$id_buku]);
        $stok = $stmt->fetchColumn();

        if ($stok > 0) {
            // Create transaction
            $stmt = $pdo->prepare("INSERT INTO transaksi (id_user, id_buku, tanggal_pinjam, status) VALUES (?, ?, CURDATE(), 'dipinjam')");
            $stmt->execute([$id_user, $id_buku]);

            // Decrease stock
            $stmt = $pdo->prepare("UPDATE buku SET stok = stok - 1 WHERE id_buku = ?");
            $stmt->execute([$id_buku]);

            header('Location: katalog.php?msg=success');
        } else {
            header('Location: katalog.php?msg=out_of_stock');
        }
    }
    exit;
}

// Fetch Books
$query = "SELECT * FROM buku WHERE (judul LIKE ? OR pengarang LIKE ? OR kategori LIKE ?) ORDER BY judul ASC";
$stmt = $pdo->prepare($query);
$stmt->execute(["%$search%", "%$search%", "%$search%"]);
$books = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="container" style="margin-top: 2.5rem;">
    <header style="margin-bottom: 2.5rem;">
        <h1 style="font-size: 1.875rem; font-weight: 700;">Katalog Buku</h1>
        <p style="color: var(--text-muted);">Temukan buku yang ingin Anda baca dan pinjam.</p>
    </header>

    <?php if (isset($_GET['msg'])): ?>
        <div style="background: <?php 
            if ($_GET['msg'] == 'success') echo '#d1fae5'; 
            elseif ($_GET['msg'] == 'already_borrowed' || $_GET['msg'] == 'out_of_stock') echo '#fee2e2'; 
        ?>; color: <?php 
            if ($_GET['msg'] == 'success') echo '#065f46'; 
            elseif ($_GET['msg'] == 'already_borrowed' || $_GET['msg'] == 'out_of_stock') echo '#991b1b'; 
        ?>; padding: 1rem; border-radius: 0.75rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas <?php echo ($_GET['msg'] == 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <span>
                <?php 
                    if ($_GET['msg'] == 'success') echo 'Buku berhasil dipinjam! Silahkan cek menu Pinjaman Saya.';
                    elseif ($_GET['msg'] == 'already_borrowed') echo 'Sobat sudah meminjam buku ini namun belum dikembalikan.';
                    elseif ($_GET['msg'] == 'out_of_stock') echo 'Maaf, stok buku ini sedang habis.';
                ?>
            </span>
        </div>
    <?php endif; ?>

    <!-- Search Box -->
    <div style="margin-bottom: 2.5rem; background: var(--surface); padding: 1.5rem; border-radius: 1rem; box-shadow: var(--shadow);">
        <form action="" method="GET" style="display: flex; gap: 1rem;">
            <div style="position: relative; flex: 1;">
                <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                <input type="text" name="search" class="input-control" placeholder="Cari judul buku, penulis, atau kategori..." style="padding-left: 2.75rem;" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <button type="submit" class="btn btn-primary" style="padding-left: 2rem; padding-right: 2rem;">Cari Buku</button>
            <?php if ($search): ?>
                <a href="katalog.php" class="btn btn-outline">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Books Grid -->
    <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem;">
        <?php if (empty($books)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 4rem;">
                <i class="fas fa-book-open" style="font-size: 4rem; color: var(--border); margin-bottom: 1.5rem;"></i>
                <p style="color: var(--text-muted); font-size: 1.1rem;">Buku yang Anda cari tidak ditemukan.</p>
            </div>
        <?php else: ?>
            <?php foreach ($books as $row): ?>
                <div class="card" style="display: flex; flex-direction: column;">
                    <div style="height: 180px; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-book" style="font-size: 4rem; color: #94a3b8;"></i>
                    </div>
                    <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
                        <span class="badge" style="background: #eef2ff; color: var(--primary); align-self: flex-start; margin-bottom: 0.75rem;"><?php echo htmlspecialchars($row['kategori'] ?: 'Umum'); ?></span>
                        <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-main); line-height: 1.3;"><?php echo htmlspecialchars($row['judul']); ?></h3>
                        <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1rem;"><i class="fas fa-user-edit" style="width: 20px;"></i> <?php echo htmlspecialchars($row['pengarang']); ?></p>
                        
                        <div style="margin-top: auto;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; font-size: 0.875rem;">
                                <span style="color: var(--text-muted);"><i class="fas fa-building" style="width: 20px;"></i> <?php echo htmlspecialchars($row['penerbit']); ?></span>
                                <span style="font-weight: 600; color: <?php echo ($row['stok'] > 0) ? 'var(--success)' : 'var(--danger)'; ?>">
                                    Stok: <?php echo $row['stok']; ?>
                                </span>
                            </div>
                            
                            <?php if ($row['stok'] > 0): ?>
                                <a href="?borrow=<?php echo $row['id_buku']; ?>" class="btn btn-primary" style="width: 100%;" onclick="return confirm('Konfirmasi peminjaman buku ini?')">
                                    Pinjam Sekarang <i class="fas fa-bookmark"></i>
                                </a>
                            <?php else: ?>
                                <button class="btn btn-outline" style="width: 100%; cursor: not-allowed; opacity: 0.6;" disabled>
                                    Stok Habis
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
