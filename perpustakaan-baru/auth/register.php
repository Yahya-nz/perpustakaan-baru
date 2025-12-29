<?php
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = trim($_POST['nik'] ?? '');
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? 'Other';
    $alamat = trim($_POST['alamat'] ?? '');
    
    // Validasi
    if (empty($nik) || empty($nama_lengkap) || empty($email)) {
        $error = 'NIK, Nama Lengkap, dan Email wajib diisi';
    } elseif (strlen($nik) < 10) {
        $error = 'NIK tidak valid';
    } else {
        // Cek apakah NIK atau email sudah terdaftar
        $stmt = query("SELECT id_anggota FROM anggota WHERE nik = ? OR email = ?", [$nik, $email]);
        if ($stmt->fetch()) {
            $error = 'NIK atau Email sudah terdaftar';
        } else {
            // Generate kode barcode unik
            $kode_barcode = 'MBR' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            
            // Insert anggota baru
            try {
                query(
                    "INSERT INTO anggota (nik, nama_lengkap, email, telepon, tanggal_lahir, jenis_kelamin, alamat, kode_barcode, terverifikasi, status_keanggotaan) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE, 'ACTIVE')",
                    [$nik, $nama_lengkap, $email, $telepon, $tanggal_lahir, $jenis_kelamin, $alamat, $kode_barcode]
                );
                
                $success = 'Pendaftaran berhasil! Silakan login menggunakan email Anda. Password default adalah email Anda.';
                
                // Reset form
                $nik = $nama_lengkap = $email = $telepon = $tanggal_lahir = $alamat = '';
            } catch (Exception $e) {
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Perpustakaan Digital</title>
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
                <h2>Daftar Anggota Baru</h2>
                <p>Bergabunglah untuk mengakses koleksi lengkap</p>
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
                    <label for="nik" class="form-label">NIK (Nomor Induk Kependudukan)</label>
                    <input 
                        type="text" 
                        id="nik" 
                        name="nik" 
                        class="form-input" 
                        placeholder="3172010101010001"
                        value="<?php echo htmlspecialchars($nik ?? ''); ?>"
                        required
                        maxlength="20"
                    >
                </div>

                <div class="form-group">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                    <input 
                        type="text" 
                        id="nama_lengkap" 
                        name="nama_lengkap" 
                        class="form-input" 
                        placeholder="Nama lengkap sesuai KTP"
                        value="<?php echo htmlspecialchars($nama_lengkap ?? ''); ?>"
                        required
                    >
                </div>

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
                    <label for="telepon" class="form-label">Nomor Telepon</label>
                    <input 
                        type="tel" 
                        id="telepon" 
                        name="telepon" 
                        class="form-input" 
                        placeholder="+628123456789"
                        value="<?php echo htmlspecialchars($telepon ?? ''); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                    <input 
                        type="date" 
                        id="tanggal_lahir" 
                        name="tanggal_lahir" 
                        class="form-input"
                        value="<?php echo htmlspecialchars($tanggal_lahir ?? ''); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" class="form-select">
                        <option value="Other">Tidak ingin menyebutkan</option>
                        <option value="M">Laki-laki</option>
                        <option value="F">Perempuan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea 
                        id="alamat" 
                        name="alamat" 
                        class="form-input" 
                        rows="3"
                        placeholder="Alamat lengkap"
                    ><?php echo htmlspecialchars($alamat ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <line x1="19" y1="8" x2="19" y2="14"></line>
                        <line x1="22" y1="11" x2="16" y2="11"></line>
                    </svg>
                    Daftar Sekarang
                </button>
            </form>

            <div class="auth-footer">
                <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
                <p style="margin-top: 1rem; font-size: 0.85rem; color: var(--text-muted);">
                    Catatan: Password default adalah email Anda
                </p>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
