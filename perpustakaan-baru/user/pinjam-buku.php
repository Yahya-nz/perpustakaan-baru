<?php
require_once '../config/database.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Proses peminjaman buku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pinjam'])) {
    $id_koleksi = $_POST['id_koleksi'];
    $durasi_hari = (int)($_POST['durasi_hari'] ?? 7); // Default 7 hari

    // Validasi durasi (1-14 hari)
    if ($durasi_hari < 1 || $durasi_hari > 14) {
        $error = 'Durasi peminjaman harus antara 1-14 hari';
    } else {
        // Cek apakah user sudah mencapai batas maksimal peminjaman
        $jumlahAktif = query(
            "SELECT COUNT(*) as total FROM peminjaman WHERE id_anggota = ? AND status = 'ACTIVE'",
            [$user_id]
        )->fetch()['total'];

        if ($jumlahAktif >= 5) {
            $error = 'Anda telah mencapai batas maksimal peminjaman (5 buku)';
        } else {
            // Cari eksemplar yang tersedia
            $eksemplar = query(
                "SELECT id_eksemplar FROM eksemplar
                 WHERE id_koleksi = ? AND status = 'AVAILABLE'
                 LIMIT 1",
                [$id_koleksi]
            )->fetch();

            if (!$eksemplar) {
                $error = 'Buku tidak tersedia untuk dipinjam saat ini';
            } else {
                try {
                    // Buat peminjaman baru dengan durasi yang dipilih
                    $tanggal_jatuh_tempo = date('Y-m-d H:i:s', strtotime("+{$durasi_hari} days"));

                    query(
                        "INSERT INTO peminjaman (id_eksemplar, id_anggota, tanggal_pinjam, tanggal_jatuh_tempo, status)
                         VALUES (?, ?, NOW(), ?, 'ACTIVE')",
                        [$eksemplar['id_eksemplar'], $user_id, $tanggal_jatuh_tempo]
                    );

                    // Update status eksemplar menjadi LOANED
                    query(
                        "UPDATE eksemplar SET status = 'LOANED' WHERE id_eksemplar = ?",
                        [$eksemplar['id_eksemplar']]
                    );

                    $message = "Buku berhasil dipinjam untuk {$durasi_hari} hari! Harap kembalikan sebelum " . date('d M Y', strtotime($tanggal_jatuh_tempo));
                } catch (Exception $e) {
                    $error = 'Gagal meminjam buku: ' . $e->getMessage();
                }
            }
        }
    }
}

// Query untuk mendapatkan koleksi buku dengan informasi lengkap
$sql = "SELECT 
            k.id_koleksi,
            k.judul,
            k.subjudul,
            k.tahun_terbit,
            k.isbn,
            k.jenis_koleksi,
            k.cover_image,
            p.nama as penerbit,
            kat.nama as kategori,
            kat.kode as kategori_kode,
            GROUP_CONCAT(pen.nama_lengkap SEPARATOR ', ') as penulis,
            COUNT(DISTINCT e.id_eksemplar) as total_eksemplar,
            COUNT(DISTINCT CASE WHEN e.status = 'AVAILABLE' THEN e.id_eksemplar END) as tersedia
        FROM koleksi k
        LEFT JOIN penerbit p ON k.id_penerbit = p.id_penerbit
        LEFT JOIN kategori kat ON k.id_kategori = kat.id_kategori
        LEFT JOIN koleksi_penulis kp ON k.id_koleksi = kp.id_koleksi
        LEFT JOIN penulis pen ON kp.id_penulis = pen.id_penulis
        LEFT JOIN eksemplar e ON k.id_koleksi = e.id_koleksi
        GROUP BY k.id_koleksi
        ORDER BY k.dibuat_pada DESC";

$books = query($sql)->fetchAll();

// Query untuk kategori filter
$categories = query("SELECT * FROM kategori ORDER BY nama")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjam Buku - Perpustakaan Digital</title>
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

    <div class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <div class="dashboard-welcome">
                    <div class="dashboard-title">
                        <h1>Pinjam Buku</h1>
                        <p>Pilih buku yang ingin Anda pinjam</p>
                    </div>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Kembali ke Dashboard
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

            <!-- Search Section -->
            <section class="search-section" style="margin-top: 2rem; margin-bottom: 2rem;">
                <div class="search-bar">
                    <div class="search-input-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" id="searchInput" class="search-input" placeholder="Cari buku berdasarkan judul atau penulis...">
                    </div>
                    <div class="search-filters">
                        <button class="filter-btn active" data-category="all">Semua</button>
                        <?php foreach ($categories as $cat): ?>
                            <button class="filter-btn" data-category="<?php echo htmlspecialchars($cat['kode']); ?>">
                                <?php echo htmlspecialchars($cat['nama']); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <div class="books-grid">
                <?php foreach ($books as $book): ?>
                    <div class="book-card" data-category="<?php echo htmlspecialchars($book['kategori_kode'] ?? ''); ?>">
                        <div class="book-cover <?php echo (!$book['cover_image'] || !file_exists('../uploads/covers/' . $book['cover_image'])) ? 'no-image' : ''; ?>">
                            <?php if ($book['cover_image'] && file_exists('../uploads/covers/' . $book['cover_image'])): ?>
                                <img src="../uploads/covers/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($book['judul']); ?>">
                            <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="book-info">
                            <h3 class="book-title"><?php echo htmlspecialchars($book['judul']); ?></h3>
                            <p class="book-author"><?php echo htmlspecialchars($book['penulis'] ?? 'Tidak diketahui'); ?></p>
                            <div class="book-meta">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <?php echo htmlspecialchars($book['tahun_terbit'] ?? '-'); ?>
                                </span>
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                    </svg>
                                    <?php echo htmlspecialchars($book['jenis_koleksi']); ?>
                                </span>
                            </div>
                            <?php
                            $stokClass = 'book-stock';
                            if ($book['tersedia'] == 0) {
                                $stokClass .= ' empty';
                                $stokText = 'Tidak Tersedia';
                            } elseif ($book['tersedia'] <= 2) {
                                $stokClass .= ' low';
                                $stokText = $book['tersedia'] . ' tersedia';
                            } else {
                                $stokText = $book['tersedia'] . ' tersedia';
                            }
                            ?>
                            <span class="<?php echo $stokClass; ?>"><?php echo $stokText; ?></span>
                            <div class="book-actions">
                                <?php if ($book['tersedia'] > 0): ?>
                                    <button class="btn btn-primary btn-sm" style="width: 100%; justify-content: center;" onclick="openPinjamModal(<?php echo $book['id_koleksi']; ?>, '<?php echo htmlspecialchars($book['judul']); ?>')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                        </svg>
                                        Pinjam Buku
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center;" disabled>
                                        Tidak Tersedia
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal Pinjam Buku -->
    <div id="modalPinjam" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Pinjam Buku</h3>
                <button class="modal-close" onclick="closeModal('modalPinjam')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_koleksi" id="pinjam_id_koleksi">

                    <p id="pinjam_book_title" style="margin-bottom: 1.5rem; font-weight: 600; color: var(--primary); font-size: 1.15rem;"></p>

                    <div class="form-group">
                        <label class="form-label">Pilih Durasi Peminjaman</label>
                        <select name="durasi_hari" id="durasi_hari" class="form-select" required onchange="updateJatuhTempo()">
                            <option value="3">3 Hari</option>
                            <option value="7" selected>7 Hari (1 Minggu)</option>
                            <option value="14">14 Hari (2 Minggu)</option>
                        </select>
                        <small style="color: var(--text-muted); margin-top: 0.5rem; display: block;">
                            Durasi maksimal 14 hari untuk menghindari denda
                        </small>
                    </div>

                    <div class="alert alert-info" style="margin-top: 1.5rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <div>
                            <strong>Tanggal Jatuh Tempo:</strong><br>
                            <span id="tanggal_jatuh_tempo" style="font-size: 1.1rem;"></span>
                        </div>
                    </div>

                    <div class="alert alert-warning" style="margin-top: 1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <div>
                            <strong>Penting:</strong> Kembalikan buku tepat waktu untuk menghindari denda Rp 2.000/hari.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalPinjam')">Batal</button>
                    <button type="submit" name="pinjam" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Konfirmasi Pinjam
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        function openPinjamModal(idKoleksi, judulBuku) {
            document.getElementById('pinjam_id_koleksi').value = idKoleksi;
            document.getElementById('pinjam_book_title').textContent = 'Buku: ' + judulBuku;
            updateJatuhTempo();
            openModal('modalPinjam');
        }

        function updateJatuhTempo() {
            const durasi = parseInt(document.getElementById('durasi_hari').value);
            const today = new Date();
            const jatuhTempo = new Date(today);
            jatuhTempo.setDate(today.getDate() + durasi);

            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const formatted = jatuhTempo.toLocaleDateString('id-ID', options);

            document.getElementById('tanggal_jatuh_tempo').textContent = formatted;
        }
    </script>
</body>
</html>
