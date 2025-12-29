<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

// Statistik umum
$stats = [
    'total_buku' => query("SELECT COUNT(*) as total FROM koleksi")->fetch()['total'],
    'total_eksemplar' => query("SELECT COUNT(*) as total FROM eksemplar")->fetch()['total'],
    'total_anggota' => query("SELECT COUNT(*) as total FROM anggota WHERE status_keanggotaan = 'ACTIVE'")->fetch()['total'],
    'sedang_dipinjam' => query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'ACTIVE'")->fetch()['total'],
    'terlambat' => query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'ACTIVE' AND tanggal_jatuh_tempo < NOW()")->fetch()['total'],
    'total_denda' => query("SELECT COALESCE(SUM(jumlah_denda), 0) as total FROM peminjaman WHERE jumlah_denda > 0")->fetch()['total']
];

// Peminjaman terbaru - DIPERBAIKI: ambil penulis pertama saja
$peminjamanTerbaru = query(
    "SELECT 
        p.id_peminjaman,
        p.tanggal_pinjam,
        p.tanggal_jatuh_tempo,
        p.status,
        a.nama_lengkap as anggota,
        k.judul,
        (SELECT pen.nama_lengkap 
         FROM koleksi_penulis kp 
         JOIN penulis pen ON kp.id_penulis = pen.id_penulis 
         WHERE kp.id_koleksi = k.id_koleksi 
         LIMIT 1) as penulis
     FROM peminjaman p
     JOIN anggota a ON p.id_anggota = a.id_anggota
     JOIN eksemplar e ON p.id_eksemplar = e.id_eksemplar
     JOIN koleksi k ON e.id_koleksi = k.id_koleksi
     WHERE p.status = 'ACTIVE'
     ORDER BY p.tanggal_pinjam DESC
     LIMIT 10"
)->fetchAll();

// Buku paling populer - DIPERBAIKI: ambil penulis pertama saja
$bukuPopuler = query(
    "SELECT 
        k.judul,
        (SELECT pen.nama_lengkap 
         FROM koleksi_penulis kp 
         JOIN penulis pen ON kp.id_penulis = pen.id_penulis 
         WHERE kp.id_koleksi = k.id_koleksi 
         LIMIT 1) as penulis,
        COUNT(p.id_peminjaman) as jumlah_peminjaman
     FROM koleksi k
     LEFT JOIN eksemplar e ON k.id_koleksi = e.id_koleksi
     LEFT JOIN peminjaman p ON e.id_eksemplar = p.id_eksemplar
     GROUP BY k.id_koleksi
     ORDER BY jumlah_peminjaman DESC
     LIMIT 5"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Perpustakaan Digital</title>
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
                <span class="navbar-title">Admin Panel</span>
            </a>
            <ul class="navbar-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="kelola-buku.php">Kelola Buku</a></li>
                <li><a href="kelola-anggota.php">Kelola Anggota</a></li>
                <li><a href="../logout.php" class="btn btn-secondary">Keluar</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard">
        <div class="container">
            <!-- Header -->
            <div class="dashboard-header">
                <div class="dashboard-welcome">
                    <div class="dashboard-title">
                        <h1>Dashboard Administrator</h1>
                        <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $stats['total_buku']; ?></div>
                            <div class="stat-label">Total Koleksi Buku</div>
                        </div>
                        <div class="stat-icon primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $stats['total_eksemplar']; ?></div>
                            <div class="stat-label">Total Eksemplar</div>
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
                            <div class="stat-value"><?php echo $stats['total_anggota']; ?></div>
                            <div class="stat-label">Total Anggota Aktif</div>
                        </div>
                        <div class="stat-icon success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $stats['sedang_dipinjam']; ?></div>
                            <div class="stat-label">Sedang Dipinjam</div>
                        </div>
                        <div class="stat-icon warning">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value" style="color: var(--error);"><?php echo $stats['terlambat']; ?></div>
                            <div class="stat-label">Peminjaman Terlambat</div>
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

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">Rp <?php echo number_format($stats['total_denda'], 0, ',', '.'); ?></div>
                            <div class="stat-label">Total Denda</div>
                        </div>
                        <div class="stat-icon warning">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Peminjaman Aktif -->
            <div class="table-container" style="margin-bottom: 3rem;">
                <div class="table-header">
                    <h3>Peminjaman Aktif Terbaru</h3>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Anggota</th>
                                <th>Judul Buku</th>
                                <th>Penulis</th>
                                <th>Tanggal Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($peminjamanTerbaru as $item): ?>
                                <?php
                                $now = new DateTime();
                                $jatuhTempo = new DateTime($item['tanggal_jatuh_tempo']);
                                $terlambat = $now > $jatuhTempo;
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['anggota']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['judul']); ?></td>
                                    <td><?php echo htmlspecialchars($item['penulis'] ?? 'Tidak Diketahui'); ?></td>
                                    <td><?php echo date('d M Y', strtotime($item['tanggal_pinjam'])); ?></td>
                                    <td>
                                        <?php echo date('d M Y', strtotime($item['tanggal_jatuh_tempo'])); ?>
                                        <?php if ($terlambat): ?>
                                            <span class="badge badge-error">Terlambat</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge badge-info">Aktif</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Buku Populer -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Buku Paling Populer</h3>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Judul Buku</th>
                                <th>Penulis</th>
                                <th>Jumlah Peminjaman</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bukuPopuler as $buku): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($buku['judul']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($buku['penulis'] ?? 'Tidak Diketahui'); ?></td>
                                    <td><span class="badge badge-success"><?php echo $buku['jumlah_peminjaman']; ?> kali</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>