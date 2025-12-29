<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'perpustakaan_nasional');

// Koneksi Database
function getConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $conn;
    } catch(PDOException $e) {
        die("Koneksi gagal: " . $e->getMessage());
    }
}

// Fungsi helper untuk menjalankan query
function query($sql, $params = []) {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Fungsi untuk mengecek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk mengecek role admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN';
}

// Fungsi untuk redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
