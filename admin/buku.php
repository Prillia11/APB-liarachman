<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

checkAuth('admin');

$pageTitle = 'Kelola Buku - Prillia';
$activePage = 'buku';

$search = $_GET['search'] ?? '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM buku WHERE id_buku = ?");
    $stmt->execute([$id]);
    header('Location: buku.php?msg=deleted');
    exit;
}

// Handle Add/Edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM buku WHERE id_buku = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $pengarang = $_POST['pengarang'];
    $penerbit = $_POST['penerbit'];
    $tahun = $_POST['tahun'];
    $stok = $_POST['stok'];
    $kategori = $_POST['kategori'];

    if (isset($_POST['id_buku']) && !empty($_POST['id_buku'])) {
        // Update
        $stmt = $pdo->prepare("UPDATE buku SET judul=?, pengarang=?, penerbit=?, tahun=?, stok=?, kategori=? WHERE id_buku=?");
        $stmt->execute([$judul, $pengarang, $penerbit, $tahun, $stok, $kategori, $_POST['id_buku']]);
        header('Location: buku.php?msg=updated');
    } else {
        // Create
        $stmt = $pdo->prepare("INSERT INTO buku (judul, pengarang, penerbit, tahun, stok, kategori) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$judul, $pengarang, $penerbit, $tahun, $stok, $kategori]);
        header('Location: buku.php?msg=added');
    }
    exit;
}

// Fetch Books
$query = "SELECT * FROM buku WHERE judul LIKE ? OR pengarang LIKE ? OR kategori LIKE ? ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute(["%$search%", "%$search%", "%$search%"]);
$books = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="container" style="margin-top: 2.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.875rem; font-weight: 700;">Kelola Buku</h1>
            <p style="color: var(--text-muted);">Manajemen data koleksi buku perpustakaan.</p>
        </div>
        <button onclick="document.getElementById('bukuFormCard').style.display='block'; window.scrollTo(0,0);" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Buku Baru
        </button>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-check-circle"></i>
            <span>
                <?php 
                    if ($_GET['msg'] == 'added') echo 'Buku berhasil ditambahkan!';
                    elseif ($_GET['msg'] == 'updated') echo 'Data buku berhasil diperbarui!';
                    elseif ($_GET['msg'] == 'deleted') echo 'Buku berhasil dihapus!';
                ?>
            </span>
        </div>
    <?php endif; ?>

    <!-- Form Section (Hidden by default unless editing or adding) -->
    <div id="bukuFormCard" class="card" style="display: <?php echo ($edit_data) ? 'block' : 'none'; ?>; margin-bottom: 2.5rem; padding: 2rem; border-left: 5px solid var(--primary);">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">
            <?php echo ($edit_data) ? 'Edit Data Buku' : 'Tambah Buku Baru'; ?>
        </h2>
        <form action="" method="POST" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <input type="hidden" name="id_buku" value="<?php echo $edit_data['id_buku'] ?? ''; ?>">
            <div class="input-group" style="grid-column: span 2;">
                <label>Judul Buku</label>
                <input type="text" name="judul" class="input-control" value="<?php echo $edit_data['judul'] ?? ''; ?>" required>
            </div>
            <div class="input-group">
                <label>Pengarang</label>
                <input type="text" name="pengarang" class="input-control" value="<?php echo $edit_data['pengarang'] ?? ''; ?>" required>
            </div>
            <div class="input-group">
                <label>Penerbit</label>
                <input type="text" name="penerbit" class="input-control" value="<?php echo $edit_data['penerbit'] ?? ''; ?>" required>
            </div>
            <div class="input-group">
                <label>Tahun Terbit</label>
                <input type="number" name="tahun" class="input-control" value="<?php echo $edit_data['tahun'] ?? ''; ?>" required>
            </div>
            <div class="input-group">
                <label>Kategori</label>
                <input type="text" name="kategori" class="input-control" value="<?php echo $edit_data['kategori'] ?? ''; ?>" placeholder="Karya Ilmiah, Novel, dll">
            </div>
            <div class="input-group">
                <label>Stok</label>
                <input type="number" name="stok" class="input-control" value="<?php echo $edit_data['stok'] ?? ''; ?>" required>
            </div>
            <div style="grid-column: span 2; display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="window.location.href='buku.php'" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Buku <i class="fas fa-save"></i></button>
            </div>
        </form>
    </div>

    <!-- Filter & Search -->
    <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
        <form action="" method="GET" style="display: flex; gap: 0.5rem; flex: 1;">
            <input type="text" name="search" class="input-control" placeholder="Cari berdasarkan judul, pengarang, atau kategori..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
            <?php if ($search): ?>
                <a href="buku.php" class="btn btn-outline">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Pengarang / Penerbit</th>
                    <th>Tahun</th>
                    <th>Stok</th>
                    <th>Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($books)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 3rem;">Data buku tidak ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($books as $row): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($row['judul']); ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">#ID: <?php echo $row['id_buku']; ?></div>
                            </td>
                            <td style="font-size: 0.875rem;">
                                <div><i class="fas fa-user-edit" style="width: 20px;"></i> <?php echo htmlspecialchars($row['pengarang']); ?></div>
                                <div style="color: var(--text-muted); font-size: 0.75rem;"><i class="fas fa-building" style="width: 20px;"></i> <?php echo htmlspecialchars($row['penerbit']); ?></div>
                            </td>
                            <td style="font-size: 0.875rem;"><?php echo $row['tahun']; ?></td>
                            <td style="font-size: 0.875rem;">
                                <span class="badge <?php echo ($row['stok'] > 0) ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo $row['stok']; ?>
                                </span>
                            </td>
                            <td style="font-size: 0.875rem;"><?php echo htmlspecialchars($row['kategori'] ?: '-'); ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="?edit=<?php echo $row['id_buku']; ?>" class="btn btn-outline" style="padding: 0.4rem 0.6rem; font-size: 0.75rem; border-color: #3b82f6; color: #3b82f6;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $row['id_buku']; ?>" class="btn btn-outline" style="padding: 0.4rem 0.6rem; font-size: 0.75rem; border-color: var(--danger); color: var(--danger);" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
