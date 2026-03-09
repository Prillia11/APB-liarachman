<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

checkAuth('admin');

$pageTitle = 'Kelola Anggota - Prillia';
$activePage = 'anggota';

$search = $_GET['search'] ?? '';
$error_msg = null;

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = ? AND role = 'user'");
    $stmt->execute([$id]);
    header('Location: anggota.php?msg=deleted');
    exit;
}

// Handle Add/Edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ? AND role = 'user'");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $kelas = $_POST['kelas'];
    $id_user = $_POST['id_user'] ?? '';
    
    // Check for duplicate username
    if (!empty($id_user)) {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id_user != ?");
        $checkStmt->execute([$username, $id_user]);
    } else {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $checkStmt->execute([$username]);
    }

    if ($checkStmt->fetchColumn() > 0) {
        $error_msg = "Username '$username' sudah terdaftar. Silakan gunakan username lain.";
        // If it was an edit, we need to keep the edit_data for the form
        if (!empty($id_user)) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
            $stmt->execute([$id_user]);
            $edit_data = $stmt->fetch();
        }
    } else {
        if (!empty($id_user)) {
            // Update
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET nama=?, username=?, password=?, kelas=? WHERE id_user=?");
                $stmt->execute([$nama, $username, $password, $kelas, $id_user]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET nama=?, username=?, kelas=? WHERE id_user=?");
                $stmt->execute([$nama, $username, $kelas, $id_user]);
            }
            header('Location: anggota.php?msg=updated');
            exit;
        } else {
            // Create
            $password = password_hash($_POST['password'] ?: '123456', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role, kelas) VALUES (?, ?, ?, 'user', ?)");
            $stmt->execute([$nama, $username, $password, $kelas]);
            header('Location: anggota.php?msg=added');
            exit;
        }
    }
}

// Fetch Members
$query = "SELECT * FROM users WHERE role = 'user' AND (nama LIKE ? OR username LIKE ? OR kelas LIKE ?) ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute(["%$search%", "%$search%", "%$search%"]);
$members = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="container" style="margin-top: 2.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.875rem; font-weight: 700;">Kelola Anggota</h1>
            <p style="color: var(--text-muted);">Manajemen data siswa / anggota perpustakaan.</p>
        </div>
        <button onclick="document.getElementById('anggotaFormCard').style.display='block'; window.scrollTo(0,0);" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Tambah Anggota
        </button>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div style="background: #9ef0c6ff; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-check-circle"></i>
            <span>
                <?php 
                    if ($_GET['msg'] == 'added') echo 'Anggota berhasil ditambahkan!';
                    elseif ($_GET['msg'] == 'updated') echo 'Data anggota berhasil diperbarui!';
                    elseif ($_GET['msg'] == 'deleted') echo 'Data anggota berhasil dihapus!';
                ?>
            </span>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; border-left: 4px solid #ef4444;">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error_msg; ?></span>
        </div>
    <?php endif; ?>

    <!-- Form Section -->
    <div id="anggotaFormCard" class="card" style="display: <?php echo ($edit_data || $error_msg) ? 'block' : 'none'; ?>; margin-bottom: 2.5rem; padding: 2rem; border-left: 5px solid var(--primary);">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">
            <?php echo ($edit_data) ? 'Edit Data Anggota' : 'Tambah Anggota Baru'; ?>
        </h2>
        <form action="" method="POST" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <input type="hidden" name="id_user" value="<?php echo $edit_data['id_user'] ?? ''; ?>">
            <div class="input-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="input-control" value="<?php echo $edit_data['nama'] ?? ''; ?>" required>
            </div>
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" class="input-control" value="<?php echo $edit_data['username'] ?? ''; ?>" required>
            </div>
            <div class="input-group">
                <label>Kelas</label>
                <input type="text" name="kelas" class="input-control" value="<?php echo $edit_data['kelas'] ?? ''; ?>" required placeholder="Contoh: XII RPL 1">
            </div>
            <div class="input-group">
                <label>Password <?php echo ($edit_data) ? '<span style="font-size: 0.7rem; color: var(--text-muted);">(Kosongkan jika tidak diubah)</span>' : ''; ?></label>
                <input type="password" name="password" class="input-control" <?php echo ($edit_data) ? '' : 'required placeholder="Default: 123456"'; ?>>
            </div>
            <div style="grid-column: span 2; display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="window.location.href='anggota.php'" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Anggota <i class="fas fa-save"></i></button>
            </div>
        </form>
    </div>

    <!-- Filter & Search -->
    <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
        <form action="" method="GET" style="display: flex; gap: 0.5rem; flex: 1;">
            <input type="text" name="search" class="input-control" placeholder="Cari berdasarkan nama, username, atau kelas..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
            <?php if ($search): ?>
                <a href="anggota.php" class="btn btn-outline">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>Kelas</th>
                    <th>Tgl Daftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 3rem;">Belum ada anggota yang terdaftar.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($members as $row): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($row['nama']); ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">#ID: <?php echo $row['id_user']; ?></div>
                            </td>
                            <td style="font-size: 0.875rem; font-family: monospace;"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td style="font-size: 0.875rem;"><span class="badge badge-warning" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe;"><?php echo htmlspecialchars($row['kelas']); ?></span></td>
                            <td style="font-size: 0.875rem; color: var(--text-muted);"><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="?edit=<?php echo $row['id_user']; ?>" class="btn btn-outline" style="padding: 0.4rem 0.6rem; font-size: 0.75rem; border-color: #3b82f6; color: #3b82f6;">
                                        <i class="fas fa-user-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $row['id_user']; ?>" class="btn btn-outline" style="padding: 0.4rem 0.6rem; font-size: 0.75rem; border-color: var(--danger); color: var(--danger);" onclick="return confirm('Apakah Anda yakin ingin menghapus anggota ini?')">
                                        <i class="fas fa-user-minus"></i>
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
