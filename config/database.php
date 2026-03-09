<?php
// Set Timezone agar sesuai dengan Waktu Indonesia (Penting untuk Vercel yang default UTC)
date_default_timezone_set("Asia/Jakarta");

// Pengaturan Database - Menggunakan Environment Variables untuk Tracking Production (Vercel/Aiven)
// Jika tidak ada env, akan menggunakan default localhost
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME') ?: 'db_perpustakaan_prillia';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Opsional: Jika Aiven memerlukan SSL, tambahkan certificate jika ada di env
if (getenv('DB_SSL_CA')) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = getenv('DB_SSL_CA');
}

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     if ($e->getCode() == 1049) {
         die("Database tidak ditemukan secara otomatis. Pastikan database '$db' sudah dibuat.");
     }
     die("Koneksi Database Gagal: " . $e->getMessage());
}
