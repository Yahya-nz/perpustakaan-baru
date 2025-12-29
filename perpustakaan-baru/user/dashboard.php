<?php
require_once '../config/database.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle pengembalian buku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kembalikan'])) {
    $id_peminjaman = $_POST['id_peminjaman'];

    try {
        // Ambil id_eksemplar dari peminjaman
        $peminjaman = query(
            "SELECT id_eksemplar FROM peminjaman WHERE id_peminjaman = ? AND id_anggota = ?",
            [$id_peminjaman, $user_id]
        )->fetch();

        if ($peminjaman) {
            // Update status peminjaman menjadi RETURNED
            query(
                "UPDATE peminjaman
                 SET status = 'RETURNED', tanggal_kembali = NOW()
                 WHERE id_peminjaman = ? AND id_anggota = ? AND status = 'ACTIVE'",
                [$id_peminjaman, $user_id]
            );

            // Update status eksemplar kembali ke AVAILABLE
            query(
                "UPDATE eksemplar SET status = 'AVAILABLE' WHERE id_eksemplar = ?",
                [$peminjaman['id_eksemplar']]
            );

            $message = 'Buku berhasil dikembalikan! Terima kasih.';
        }
    } catch (Exception $e) {
        $error = 'Gagal mengembalikan buku: ' . $e->getMessage();
    }
}

// Statistik user
$stats = query(
    "SELECT 
        COUNT(CASE WHEN status = 'ACTIVE' THEN 1 END) as sedang_dipinjam,
        COUNT(CASE WHEN status = 'RETURNED' THEN 1 END) as total_dikembalikan,
        COUNT(CASE WHEN status = 'OVERDUE' THEN 1 END) as terlambat,
        COALESCE(SUM(jumlah_denda), 0) as total_denda
     FROM peminjaman 
     WHERE id_anggota = ?",
    [$user_id]
)->fetch();

// Peminjaman aktif
$peminjamanAktif = query(
    "SELECT
        p.id_peminjaman,
        p.tanggal_pinjam,
        p.tanggal_jatuh_tempo,
        p.status,
        p.jumlah_denda,
        k.judul,
        (SELECT pen.nama_lengkap
         FROM koleksi_penulis kp
         JOIN penulis pen ON kp.id_penulis = pen.id_penulis
         WHERE kp.id_koleksi = k.id_koleksi
         LIMIT 1) as penulis,
        e.kode_barcode,
        e.nomor_panggil
     FROM peminjaman p
     JOIN eksemplar e ON p.id_eksemplar = e.id_eksemplar
     JOIN koleksi k ON e.id_koleksi = k.id_koleksi
     WHERE p.id_anggota = ? AND p.status = 'ACTIVE'
     ORDER BY p.tanggal_jatuh_tempo ASC",
    [$user_id]
)->fetchAll();

// Riwayat peminjaman
$riwayat = query(
    "SELECT
        p.id_peminjaman,
        p.tanggal_pinjam,
        p.tanggal_kembali,
        p.status,
        p.jumlah_denda,
        k.judul,
        (SELECT pen.nama_lengkap
         FROM koleksi_penulis kp
         JOIN penulis pen ON kp.id_penulis = pen.id_penulis
         WHERE kp.id_koleksi = k.id_koleksi
         LIMIT 1) as penulis
     FROM peminjaman p
     JOIN eksemplar e ON p.id_eksemplar = e.id_eksemplar
     JOIN koleksi k ON e.id_koleksi = k.id_koleksi
     WHERE p.id_anggota = ? AND p.status = 'RETURNED'
     ORDER BY p.tanggal_kembali DESC
     LIMIT 10",
    [$user_id]
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengunjung - Perpustakaan Digital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="../index.php" class="navbar-brand">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <span class="navbar-title">Perpustakaan Digital</span>
            </a>
            <ul class="navbar-menu">
                <li><a href="../index.php">Beranda</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="pinjam-buku.php">Pinjam Buku</a></li>
                <li><a href="../logout.php" class="btn btn-secondary">Keluar</a></li>
            </ul>
        </div>
    </nav>

    <!-- Dashboard -->
    <div class="dashboard">
        <div class="container">
            <!-- Header -->
            <div class="dashboard-header">
                <div class="dashboard-welcome">
                    <div class="dashboard-title">
                        <h1>Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?></h1>
                        <p>Kelola peminjaman buku Anda di sini</p>
                    </div>
                    <a href="pinjam-buku.php" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14"></path>
                            <path d="M5 12h14"></path>
                        </svg>
                        Pinjam Buku Baru
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

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

            <!-- Stats -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $stats['sedang_dipinjam']; ?></div>
                            <div class="stat-label">Sedang Dipinjam</div>
                        </div>
                        <div class="stat-icon primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $stats['total_dikembalikan']; ?></div>
                            <div class="stat-label">Total Dikembalikan</div>
                        </div>
                        <div class="stat-icon success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">Rp <?php echo number_format($stats['total_denda'], 0, ',', '.'); ?></div>
                            <div class="stat-label">Total Denda</div>
                        </div>
                        <div class="stat-icon warning">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Peminjaman Aktif -->
            <?php if (count($peminjamanAktif) > 0): ?>
                <div class="table-container" style="margin-bottom: 3rem;">
                    <div class="table-header">
                        <h3>Buku yang Sedang Dipinjam</h3>
                    </div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Judul Buku</th>
                                    <th>Penulis</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Status</th>
                                    <th>Denda</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($peminjamanAktif as $item): ?>
                                    <?php
                                    $now = new DateTime();
                                    $jatuhTempo = new DateTime($item['tanggal_jatuh_tempo']);
                                    $sisaHari = $now->diff($jatuhTempo)->days;
                                    $terlambat = $now > $jatuhTempo;
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($item['judul']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($item['penulis'] ?? '-'); ?></td>
                                        <td><?php echo date('d M Y', strtotime($item['tanggal_pinjam'])); ?></td>
                                        <td>
                                            <?php echo date('d M Y', strtotime($item['tanggal_jatuh_tempo'])); ?>
                                            <?php if ($terlambat): ?>
                                                <span class="badge badge-error">Terlambat <?php echo $sisaHari; ?> hari</span>
                                            <?php elseif ($sisaHari <= 3): ?>
                                                <span class="badge badge-warning"><?php echo $sisaHari; ?> hari lagi</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">Dipinjam</span>
                                        </td>
                                        <td>
                                            <?php if ($item['jumlah_denda'] > 0): ?>
                                                <span class="badge badge-error">Rp <?php echo number_format($item['jumlah_denda'], 0, ',', '.'); ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Tidak ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" style="margin: 0;" onsubmit="return confirm('Yakin ingin mengembalikan buku ini?')">
                                                <input type="hidden" name="id_peminjaman" value="<?php echo $item['id_peminjaman']; ?>">
                                                <button type="submit" name="kembalikan" class="btn btn-sm btn-success" title="Kembalikan Buku">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="9 14 4 9 9 4"></polyline>
                                                        <path d="M20 20v-7a4 4 0 0 0-4-4H4"></path>
                                                    </svg>
                                                    Kembalikan
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info" style="margin-bottom: 3rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    Anda belum meminjam buku apapun. <a href="pinjam-buku.php" style="color: var(--accent); font-weight: 500;">Pinjam sekarang</a>
                </div>
            <?php endif; ?>

            <!-- Riwayat -->
            <?php if (count($riwayat) > 0): ?>
                <div class="table-container">
                    <div class="table-header">
                        <h3>Riwayat Peminjaman</h3>
                    </div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Judul Buku</th>
                                    <th>Penulis</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Tanggal Kembali</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($riwayat as $item): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($item['judul']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($item['penulis'] ?? '-'); ?></td>
                                        <td><?php echo date('d M Y', strtotime($item['tanggal_pinjam'])); ?></td>
                                        <td><?php echo date('d M Y', strtotime($item['tanggal_kembali'])); ?></td>
                                        <td><span class="badge badge-success">Dikembalikan</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
