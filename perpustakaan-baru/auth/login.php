<?php
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi';
    } else {
        // Cek apakah login sebagai anggota
        $stmt = query(
            "SELECT id_anggota, nama_lengkap, email, status_keanggotaan 
             FROM anggota 
             WHERE email = ? AND terverifikasi = TRUE",
            [$email]
        );
        $anggota = $stmt->fetch();
        
        // Cek apakah login sebagai admin/pegawai
        $stmt = query(
            "SELECT id_pegawai, nama_lengkap, email, peran, sandi_hash, aktif 
             FROM pegawai 
             WHERE email = ?",
            [$email]
        );
        $pegawai = $stmt->fetch();
        
        // Login sebagai anggota (password simple: email untuk demo)
        if ($anggota && $password === $email) {
            if ($anggota['status_keanggotaan'] !== 'ACTIVE') {
                $error = 'Akun Anda tidak aktif. Hubungi administrator.';
            } else {
                $_SESSION['user_id'] = $anggota['id_anggota'];
                $_SESSION['nama'] = $anggota['nama_lengkap'];
                $_SESSION['email'] = $anggota['email'];
                $_SESSION['role'] = 'USER';
                
                redirect('../user/dashboard.php');
            }
        }
        // Login sebagai admin/pegawai
        elseif ($pegawai) {
            // Untuk demo, password admin adalah 'admin123'
            if ($password === 'admin123' || password_verify($password, $pegawai['sandi_hash'])) {
                if (!$pegawai['aktif']) {
                    $error = 'Akun Anda tidak aktif.';
                } else {
                    $_SESSION['user_id'] = $pegawai['id_pegawai'];
                    $_SESSION['nama'] = $pegawai['nama_lengkap'];
                    $_SESSION['email'] = $pegawai['email'];
                    $_SESSION['role'] = $pegawai['peran'];
                    
                    redirect('../admin/dashboard.php');
                }
            } else {
                $error = 'Email atau password salah';
            }
        } else {
            $error = 'Email atau password salah';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Perpustakaan Digital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="../index.php" style="display: flex; align-items: center; justify-content: center; gap: 1rem; text-decoration: none; color: var(--primary); margin-bottom: 1rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </a>
                <h2>Selamat Datang Kembali</h2>
                <p>Masuk ke akun Anda untuk melanjutkan</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" data-validate>
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="nama@email.com"
                        value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Masukkan password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10 17 15 12 10 7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                    Masuk
                </button>
            </form>

            <div class="auth-footer">
                <p>Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
                <p style="margin-top: 1rem; font-size: 0.85rem; color: var(--text-muted);">
                    Demo Login:<br>
                    Admin: admin.perpustakaan@gmail.com / password: admin123
                </p>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
