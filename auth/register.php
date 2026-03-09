<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: ../admin/index.php');
    } else {
        header('Location: ../user/index.php');
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $kelas = $_POST['kelas'];

    // Check if username exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        $error = 'Username sudah digunakan!';
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role, kelas) VALUES (?, ?, ?, 'user', ?)");
        if ($stmt->execute([$nama, $username, $password, $kelas])) {
            $success = 'Pendaftaran berhasil! Silahkan login.';
        } else {
            $error = 'Terjadi kesalahan. Silahkan coba lagi.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Prillia Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="background">
    <div class="auth-wrapper">
        <div class="card auth-card">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div class="logo" style="justify-content: center; font-size: 2rem; margin-bottom: 0.5rem;">
                    <i class="fas fa-book-reader"></i> Prillia
                </div>
                <p style="color: var(--text-muted);">Daftar akun siswa baru.</p>
            </div>

            <?php if ($error): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center; font-size: 0.875rem;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center; font-size: 0.875rem;">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="input-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" class="input-control" placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="input-control" placeholder="Masukkan username" required>
                </div>
                <div class="input-group">
                    <label for="kelas">Kelas</label>
                    <input type="text" id="kelas" name="kelas" class="input-control" placeholder="Contoh: XII RPL 1" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="input-control" placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    Daftar Akun <i class="fas fa-user-plus"></i>
                </button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem; color: var(--text-muted);">
                Sudah punya akun? <a href="login.php" style="color: var(--primary); font-weight: 600;">Login Disini</a>
            </div>
        </div>
    </div>
</body>
</html>
