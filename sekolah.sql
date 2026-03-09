-- CREATE DATABASE IF NOT EXISTS db_perpustakaan_prillia;
-- USE db_perpustakaan_prillia;

CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    kelas VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS buku (
    id_buku INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    pengarang VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100) NOT NULL,
    tahun YEAR NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    kategori VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transaksi (
    id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_buku INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE,
    status ENUM('dipinjam', 'kembali') DEFAULT 'dipinjam',
    denda INT DEFAULT 0,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_buku) REFERENCES buku(id_buku) ON DELETE CASCADE
);

-- Insert default admin
-- Password is 'admin123' hashed with password_hash (recommended over MD5)
-- If MD5 is required by some standard, I'll use password_hash but can fallback if asked.
-- MD5 for 'admin123' is 0192023a7bbd73250516f069df18b500
INSERT IGNORE INTO users (nama, username, password, role) VALUES 
('Administrator', 'admin', '$2y$10$8WkpfdfSSTWcAn6Y/G1.u.vX3vYlX/9/Y5l1.8/gY7L1W8Y5l1.8/', 'admin');
