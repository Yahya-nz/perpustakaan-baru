<?php
require_once 'config/database.php';

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
            (SELECT pen.nama_lengkap 
             FROM koleksi_penulis kp 
             JOIN penulis pen ON kp.id_penulis = pen.id_penulis 
             WHERE kp.id_koleksi = k.id_koleksi 
             LIMIT 1) as penulis,
            COUNT(DISTINCT e.id_eksemplar) as total_eksemplar,
            COUNT(DISTINCT CASE WHEN e.status = 'AVAILABLE' THEN e.id_eksemplar END) as tersedia
        FROM koleksi k
        LEFT JOIN penerbit p ON k.id_penerbit = p.id_penerbit
        LEFT JOIN kategori kat ON k.id_kategori = kat.id_kategori
        LEFT JOIN eksemplar e ON k.id_koleksi = e.id_koleksi
        GROUP BY k.id_koleksi
        ORDER BY k.dibuat_pada DESC
        LIMIT 12";

$books = query($sql)->fetchAll();

// Query untuk kategori filter
$categories = query("SELECT * FROM kategori ORDER BY nama")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan Digital - Jelajahi Koleksi Buku</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <span class="navbar-title">Perpustakaan Digital</span>
            </a>
            <ul class="navbar-menu">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="#koleksi">Koleksi</a></li>
                <li><a href="#tentang">Tentang</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="<?php echo isAdmin() ? 'admin/dashboard.php' : 'user/dashboard.php'; ?>">Dashboard</a></li>
                    <li><a href="logout.php">Keluar</a></li>
                <?php else: ?>
                    <li><a href="auth/login.php" class="btn btn-primary">Masuk</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Jelajahi Dunia Pengetahuan</h1>
                    <p>Temukan ribuan koleksi buku digital dan fisik yang siap memperkaya wawasan Anda. Akses mudah, kapan saja, di mana saja.</p>
                    <div class="hero-actions">
                        <?php if (!isLoggedIn()): ?>
                            <a href="auth/register.php" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <line x1="19" y1="8" x2="19" y2="14"></line>
                                    <line x1="22" y1="11" x2="16" y2="11"></line>
                                </svg>
                                Daftar Sekarang
                            </a>
                            <a href="auth/login.php" class="btn btn-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                    <polyline points="10 17 15 12 10 7"></polyline>
                                    <line x1="15" y1="12" x2="3" y2="12"></line>
                                </svg>
                                Masuk
                            </a>
                        <?php else: ?>
                            <a href="<?php echo isAdmin() ? 'admin/dashboard.php' : 'user/dashboard.php'; ?>" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="14" width="7" height="7"></rect>
                                    <rect x="3" y="14" width="7" height="7"></rect>
                                </svg>
                                Ke Dashboard
                            </a>
                        <?php endif; ?>
                        <a href="#koleksi" class="btn btn-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            Cari Buku
                        </a>
                    </div>
                </div>
                <div class="hero-image">
                    <div class="book-stack">
                        <div class="book-card-mini">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                        </div>
                        <div class="book-card-mini">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                            </svg>
                        </div>
                        <div class="book-card-mini">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="search-section" id="koleksi">
        <div class="container">
            <div class="search-container">
                <div class="search-bar">
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
        </div>
    </section>

    <!-- Books Section -->
    <section class="books-section">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Koleksi Buku Terbaru</h2>
                </div>
            </div>
            
            <div class="books-grid">
                <?php foreach ($books as $book): ?>
                    <div class="book-card" data-category="<?php echo htmlspecialchars($book['kategori'] ?? ''); ?>">
                        <div class="book-cover <?php echo (!$book['cover_image'] || !file_exists('uploads/covers/' . $book['cover_image'])) ? 'no-image' : ''; ?>">
                            <?php if ($book['cover_image'] && file_exists('uploads/covers/' . $book['cover_image'])): ?>
                                <img src="uploads/covers/<?php echo htmlspecialchars($book['cover_image']); ?>" 
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
                            <p class="book-author"><?php echo htmlspecialchars($book['penulis'] ?? 'Tidak Diketahui'); ?></p>
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
                                <?php if (isLoggedIn()): ?>
                                    <?php if ($book['tersedia'] > 0): ?>
                                        <a href="user/pinjam-buku.php?id=<?php echo $book['id_koleksi']; ?>" class="btn btn-primary btn-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                            </svg>
                                            Pinjam
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" disabled>Tidak Tersedia</button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="auth/login.php" class="btn btn-primary btn-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                            <polyline points="10 17 15 12 10 7"></polyline>
                                            <line x1="15" y1="12" x2="3" y2="12"></line>
                                        </svg>
                                        Login untuk Pinjam
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="tentang">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Perpustakaan Digital</h3>
                    <p>Platform digital untuk mengakses ribuan koleksi buku berkualitas. Membaca adalah jendela dunia.</p>
                </div>
                <div class="footer-section">
                    <h3>Tautan Cepat</h3>
                    <a href="index.php">Beranda</a>
                    <a href="#koleksi">Koleksi Buku</a>
                    <a href="auth/login.php">Masuk</a>
                    <a href="auth/register.php">Daftar</a>
                </div>
                <div class="footer-section">
                    <h3>Kontak</h3>
                    <p>Email: info@perpustakaan.id</p>
                    <p>Telp: (021) 1234-5678</p>
                    <p>Alamat: Jl. Salemba Raya 28, Jakarta</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Perpustakaan Digital. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>