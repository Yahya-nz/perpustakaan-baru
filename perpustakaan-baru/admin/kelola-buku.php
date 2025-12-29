<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$message = '';
$error = '';

// Handle tambah/edit buku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add' || $action === 'edit') {
        $judul = trim($_POST['judul']);
        $subjudul = trim($_POST['subjudul']);
        $nama_penulis = trim($_POST['nama_penulis'] ?? '');
        $tahun_terbit = $_POST['tahun_terbit'];
        $isbn = trim($_POST['isbn']);
        $jenis_koleksi = $_POST['jenis_koleksi'];
        $id_kategori = $_POST['id_kategori'];
        $cover_image = '';
        
        // Handle upload cover
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['cover_image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $newname = uniqid() . '_' . time() . '.' . $ext;
                $destination = '../uploads/covers/' . $newname;
                
                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $destination)) {
                    $cover_image = $newname;
                }
            }
        }
        
        if (empty($judul)) {
            $error = 'Judul buku wajib diisi';
        } else {
            try {
                if ($action === 'add') {
                    // Gunakan koneksi yang sama untuk semua operasi
                    $conn = getConnection();

                    $stmt = $conn->prepare(
                        "INSERT INTO koleksi (judul, subjudul, tahun_terbit, isbn, jenis_koleksi, id_kategori, cover_image)
                         VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([$judul, $subjudul, $tahun_terbit, $isbn, $jenis_koleksi, $id_kategori ?: null, $cover_image ?: null]);

                    $id_koleksi = $conn->lastInsertId();

                    if (!empty($nama_penulis)) {
                        $penulis = query(
                            "SELECT id_penulis FROM penulis WHERE nama_lengkap = ?",
                            [$nama_penulis]
                        )->fetch();

                        if (!$penulis) {
                            $stmt = $conn->prepare("INSERT INTO penulis (nama_lengkap) VALUES (?)");
                            $stmt->execute([$nama_penulis]);
                            $id_penulis = $conn->lastInsertId();
                        } else {
                            $id_penulis = $penulis['id_penulis'];
                        }

                        $stmt = $conn->prepare("INSERT INTO koleksi_penulis (id_koleksi, id_penulis) VALUES (?, ?)");
                        $stmt->execute([$id_koleksi, $id_penulis]);
                    }

                    $message = 'Buku berhasil ditambahkan';
                } else {
                    $id_koleksi = $_POST['id_koleksi'];
                    $conn = getConnection();

                    if ($cover_image) {
                        $old = query("SELECT cover_image FROM koleksi WHERE id_koleksi = ?", [$id_koleksi])->fetch();
                        if ($old && $old['cover_image'] && file_exists('../uploads/covers/' . $old['cover_image'])) {
                            unlink('../uploads/covers/' . $old['cover_image']);
                        }

                        query(
                            "UPDATE koleksi SET judul = ?, subjudul = ?, tahun_terbit = ?, isbn = ?, jenis_koleksi = ?, id_kategori = ?, cover_image = ?
                             WHERE id_koleksi = ?",
                            [$judul, $subjudul, $tahun_terbit, $isbn, $jenis_koleksi, $id_kategori ?: null, $cover_image, $id_koleksi]
                        );
                    } else {
                        query(
                            "UPDATE koleksi SET judul = ?, subjudul = ?, tahun_terbit = ?, isbn = ?, jenis_koleksi = ?, id_kategori = ?
                             WHERE id_koleksi = ?",
                            [$judul, $subjudul, $tahun_terbit, $isbn, $jenis_koleksi, $id_kategori ?: null, $id_koleksi]
                        );
                    }

                    if (!empty($nama_penulis)) {
                        query("DELETE FROM koleksi_penulis WHERE id_koleksi = ?", [$id_koleksi]);

                        $penulis = query("SELECT id_penulis FROM penulis WHERE nama_lengkap = ?", [$nama_penulis])->fetch();

                        if (!$penulis) {
                            $stmt = $conn->prepare("INSERT INTO penulis (nama_lengkap) VALUES (?)");
                            $stmt->execute([$nama_penulis]);
                            $id_penulis = $conn->lastInsertId();
                        } else {
                            $id_penulis = $penulis['id_penulis'];
                        }

                        $stmt = $conn->prepare("INSERT INTO koleksi_penulis (id_koleksi, id_penulis) VALUES (?, ?)");
                        $stmt->execute([$id_koleksi, $id_penulis]);
                    }

                    $message = 'Buku berhasil diperbarui';
                }
            } catch (Exception $e) {
                $error = 'Gagal menyimpan buku: ' . $e->getMessage();
            }
        }
    }
    
    if ($action === 'add_eksemplar') {
        $id_koleksi = $_POST['id_koleksi'];
        $jumlah = (int)$_POST['jumlah'];
        
        try {
            for ($i = 0; $i < $jumlah; $i++) {
                $kode_barcode = 'BC' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
                $nomor_akses = 'ACC-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
                
                query(
                    "INSERT INTO eksemplar (id_koleksi, id_perpustakaan, kode_barcode, nomor_akses, status) 
                     VALUES (?, 1, ?, ?, 'AVAILABLE')",
                    [$id_koleksi, $kode_barcode, $nomor_akses]
                );
            }
            $message = "$jumlah eksemplar berhasil ditambahkan";
        } catch (Exception $e) {
            $error = 'Gagal menambah eksemplar: ' . $e->getMessage();
        }
    }
    
    if ($action === 'delete') {
        $id_koleksi = $_POST['id_koleksi'];
        
        try {
            query("DELETE FROM koleksi WHERE id_koleksi = ?", [$id_koleksi]);
            $message = 'Buku berhasil dihapus';
        } catch (Exception $e) {
            $error = 'Gagal menghapus buku: ' . $e->getMessage();
        }
    }
}

$books = query(
    "SELECT 
        k.id_koleksi,
        k.judul,
        k.subjudul,
        k.tahun_terbit,
        k.isbn,
        k.jenis_koleksi,
        k.cover_image,
        kat.nama as kategori,
        (SELECT pen.nama_lengkap 
         FROM koleksi_penulis kp 
         JOIN penulis pen ON kp.id_penulis = pen.id_penulis 
         WHERE kp.id_koleksi = k.id_koleksi 
         LIMIT 1) as penulis,
        COUNT(DISTINCT e.id_eksemplar) as total_eksemplar,
        COUNT(DISTINCT CASE WHEN e.status = 'AVAILABLE' THEN e.id_eksemplar END) as tersedia
     FROM koleksi k
     LEFT JOIN kategori kat ON k.id_kategori = kat.id_kategori
     LEFT JOIN eksemplar e ON k.id_koleksi = e.id_koleksi
     GROUP BY k.id_koleksi
     ORDER BY k.dibuat_pada DESC"
)->fetchAll();

$categories = query("SELECT * FROM kategori ORDER BY nama")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Buku - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .table-filter-header {
            position: relative;
            cursor: pointer;
            user-select: none;
        }
        
        .table-filter-header:hover {
            background-color: var(--bg-secondary);
        }
        
        .filter-icon {
            display: inline-block;
            margin-left: 5px;
            opacity: 0.5;
            font-size: 12px;
        }
        
        .table-filter-header.filtered .filter-icon {
            opacity: 1;
            color: var(--primary);
        }
        
        .filter-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            min-width: 200px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        
        .filter-dropdown.active {
            display: block;
        }
        
        .filter-search {
            padding: 10px;
            border-bottom: 1px solid var(--border);
        }
        
        .filter-search input {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 13px;
        }
        
        .filter-options {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .filter-option {
            padding: 8px 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-option:hover {
            background-color: var(--bg-secondary);
        }
        
        .filter-option input[type="checkbox"] {
            margin: 0;
        }
        
        .filter-actions {
            padding: 8px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 8px;
        }
        
        .filter-actions button {
            flex: 1;
            padding: 6px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .filter-apply {
            background: var(--primary);
            color: white;
        }
        
        .filter-clear {
            background: var(--bg-secondary);
            color: var(--text);
        }
        
        .sort-buttons {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 8px;
            border-bottom: 1px solid var(--border);
        }
        
        .sort-btn {
            padding: 6px 10px;
            border: none;
            background: var(--bg-secondary);
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-align: left;
        }
        
        .sort-btn:hover {
            background: var(--primary);
            color: white;
        }
    </style>
</head>
<body>
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
        <div class="container-fluid">
            <div class="dashboard-header">
                <div class="dashboard-welcome">
                    <div class="dashboard-title">
                        <h1>Kelola Buku</h1>
                        <p>Kelola koleksi buku perpustakaan</p>
                    </div>
                    <button class="btn btn-primary" onclick="openModal('modalTambahBuku')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14"></path>
                            <path d="M5 12h14"></path>
                        </svg>
                        Tambah Buku
                    </button>
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

            <div class="table-container">
                <div class="table-header">
                    <h3>Daftar Buku (<?php echo count($books); ?>)</h3>
                    <input type="text" id="globalSearch" class="search-input" placeholder="Cari di semua kolom..." style="max-width: 300px;">
                </div>
                <div class="table-wrapper">
                    <table id="booksTable">
                        <thead>
                            <tr>
                                <th class="table-filter-header" data-column="cover">
                                    COVER
                                    <span class="filter-icon">▼</span>
                                </th>
                                <th class="table-filter-header" data-column="judul">
                                    JUDUL
                                    <span class="filter-icon">▼</span>
                                </th>
                                <th class="table-filter-header" data-column="penulis">
                                    PENULIS
                                    <span class="filter-icon">▼</span>
                                </th>
                                <th class="table-filter-header" data-column="tahun">
                                    TAHUN
                                    <span class="filter-icon">▼</span>
                                </th>
                                <th class="table-filter-header" data-column="kategori">
                                    KATEGORI
                                    <span class="filter-icon">▼</span>
                                </th>
                                <th class="table-filter-header" data-column="jenis">
                                    JENIS
                                    <span class="filter-icon">▼</span>
                                </th>
                                <th class="table-filter-header" data-column="total">
                                    TOTAL EKSEMPLAR
                                    <span class="filter-icon">▼</span>
                                </th>
                                <th class="table-filter-header" data-column="tersedia">
                                    TERSEDIA
                                    <span class="filter-icon">▼</span>
                                </th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="booksTableBody">
                            <?php foreach ($books as $book): ?>
                                <tr class="book-row">
                                    <td data-column="cover">
                                        <?php if ($book['cover_image'] && file_exists('../uploads/covers/' . $book['cover_image'])): ?>
                                            <img src="../uploads/covers/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($book['judul']); ?>"
                                                 style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <div style="width: 50px; height: 70px; background: var(--bg-secondary); border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td data-column="judul">
                                        <strong><?php echo htmlspecialchars($book['judul']); ?></strong>
                                        <?php if ($book['subjudul']): ?>
                                            <br><small style="color: var(--text-muted);"><?php echo htmlspecialchars($book['subjudul']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td data-column="penulis"><?php echo htmlspecialchars($book['penulis'] ?? 'Tidak Diketahui'); ?></td>
                                    <td data-column="tahun"><?php echo htmlspecialchars($book['tahun_terbit'] ?? '-'); ?></td>
                                    <td data-column="kategori"><span class="badge badge-info"><?php echo htmlspecialchars($book['kategori'] ?? 'Umum'); ?></span></td>
                                    <td data-column="jenis"><?php echo htmlspecialchars($book['jenis_koleksi']); ?></td>
                                    <td data-column="total"><?php echo $book['total_eksemplar']; ?></td>
                                    <td data-column="tersedia">
                                        <?php if ($book['tersedia'] == 0): ?>
                                            <span class="badge badge-error"><?php echo $book['tersedia']; ?></span>
                                        <?php elseif ($book['tersedia'] <= 2): ?>
                                            <span class="badge badge-warning"><?php echo $book['tersedia']; ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-success"><?php echo $book['tersedia']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-secondary" onclick="tambahEksemplar(<?php echo $book['id_koleksi']; ?>, '<?php echo htmlspecialchars($book['judul']); ?>')" title="Tambah Eksemplar">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M12 5v14"></path>
                                                    <path d="M5 12h14"></path>
                                                </svg>
                                            </button>
                                            <form method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Yakin ingin menghapus buku ini?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id_koleksi" value="<?php echo $book['id_koleksi']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus Buku">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Buku -->
    <div id="modalTambahBuku" class="modal">
        <div class="modal-content modal-wide">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Buku Baru</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label class="form-label">Cover Buku (Opsional)</label>
                        <input type="file" name="cover_image" class="form-input" accept="image/*" onchange="previewImage(this)">
                        <small style="color: var(--text-muted);">Format: JPG, PNG, GIF. Max: 2MB</small>
                        <div id="imagePreview" class="upload-preview" style="display: none;">
                            <img id="previewImg" src="" alt="Preview">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Judul Buku <span style="color: var(--error);">*</span></label>
                        <input type="text" name="judul" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nama Penulis</label>
                        <input type="text" name="nama_penulis" class="form-input" placeholder="Masukkan nama penulis">
                        <small style="color: var(--text-muted);">Contoh: Andrea Hirata, Pramoedya Ananta Toer</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Subjudul (Opsional)</label>
                        <input type="text" name="subjudul" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">ISBN</label>
                        <input type="text" name="isbn" class="form-input" placeholder="978-xxx-xxx-xxx-x">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tahun Terbit</label>
                        <input type="number" name="tahun_terbit" class="form-input" min="1000" max="9999" placeholder="2024">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Jenis Koleksi <span style="color: var(--error);">*</span></label>
                        <select name="jenis_koleksi" class="form-select" required>
                            <option value="BOOK">Buku</option>
                            <option value="JOURNAL">Jurnal</option>
                            <option value="MULTIMEDIA">Multimedia</option>
                            <option value="THESIS">Skripsi/Tesis</option>
                            <option value="REPORT">Laporan</option>
                            <option value="DIGITAL">Digital</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select name="id_kategori" class="form-select">
                            <option value="">Pilih kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id_kategori']; ?>">
                                    <?php echo htmlspecialchars($cat['nama']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Buku</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Eksemplar -->
    <div id="modalTambahEksemplar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Eksemplar</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_eksemplar">
                    <input type="hidden" name="id_koleksi" id="eksemplar_id_koleksi">
                    
                    <p id="eksemplar_buku_title" style="margin-bottom: 1.5rem; color: var(--text-secondary);"></p>
                    
                    <div class="form-group">
                        <label class="form-label">Jumlah Eksemplar yang Ditambahkan</label>
                        <input type="number" name="jumlah" class="form-input" min="1" max="100" value="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Eksemplar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        function tambahEksemplar(id, judul) {
            document.getElementById('eksemplar_id_koleksi').value = id;
            document.getElementById('eksemplar_buku_title').textContent = 'Buku: ' + judul;
            openModal('modalTambahEksemplar');
        }
        
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
        
        // Excel-style table filter
        class TableFilter {
            constructor(tableId) {
                this.table = document.getElementById(tableId);
                this.tbody = this.table.querySelector('tbody');
                this.headers = this.table.querySelectorAll('.table-filter-header');
                this.filters = {};
                this.init();
            }
            
            init() {
                this.headers.forEach(header => {
                    header.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.toggleFilter(header);
                    });
                });
                
                document.addEventListener('click', () => {
                    this.closeAllFilters();
                });
                
                // Global search
                const globalSearch = document.getElementById('globalSearch');
                if (globalSearch) {
                    globalSearch.addEventListener('input', (e) => {
                        this.globalSearch(e.target.value);
                    });
                }
            }
            
            toggleFilter(header) {
                this.closeAllFilters();
                const column = header.dataset.column;
                
                if (column === 'cover') return;
                
                const dropdown = this.createFilterDropdown(column, header);
                header.appendChild(dropdown);
                dropdown.classList.add('active');
            }
            
            createFilterDropdown(column, header) {
                const dropdown = document.createElement('div');
                dropdown.className = 'filter-dropdown active';
                
                const values = this.getColumnValues(column);
                
                // Sort buttons
                const sortButtons = document.createElement('div');
                sortButtons.className = 'sort-buttons';
                sortButtons.innerHTML = `
                    <button class="sort-btn" onclick="tableFilter.sortColumn('${column}', 'asc')">↑ Sort A-Z</button>
                    <button class="sort-btn" onclick="tableFilter.sortColumn('${column}', 'desc')">↓ Sort Z-A</button>
                `;
                dropdown.appendChild(sortButtons);
                
                // Search box
                const searchBox = document.createElement('div');
                searchBox.className = 'filter-search';
                searchBox.innerHTML = `<input type="text" placeholder="Cari...">`;
                dropdown.appendChild(searchBox);
                
                // Options container
                const optionsContainer = document.createElement('div');
                optionsContainer.className = 'filter-options';
                
                values.forEach(value => {
                    const option = document.createElement('div');
                    option.className = 'filter-option';
                    const isChecked = !this.filters[column] || this.filters[column].includes(value);
                    option.innerHTML = `
                        <input type="checkbox" value="${value}" ${isChecked ? 'checked' : ''}>
                        <span>${value}</span>
                    `;
                    optionsContainer.appendChild(option);
                });
                
                dropdown.appendChild(optionsContainer);
                
                // Search functionality
                const searchInput = searchBox.querySelector('input');
                searchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase();
                    const options = optionsContainer.querySelectorAll('.filter-option');
                    options.forEach(opt => {
                        const text = opt.textContent.toLowerCase();
                        opt.style.display = text.includes(searchTerm) ? 'flex' : 'none';
                    });
                });
                
                searchInput.addEventListener('click', (e) => e.stopPropagation());
                
                // Actions
                const actions = document.createElement('div');
                actions.className = 'filter-actions';
                actions.innerHTML = `
                    <button class="filter-apply">Terapkan</button>
                    <button class="filter-clear">Reset</button>
                `;
                dropdown.appendChild(actions);
                
                // Apply button
                actions.querySelector('.filter-apply').addEventListener('click', (e) => {
                    e.stopPropagation();
                    const checked = Array.from(optionsContainer.querySelectorAll('input:checked'))
                        .map(cb => cb.value);
                    this.applyFilter(column, checked);
                    header.classList.add('filtered');
                    dropdown.remove();
                });
                
                // Clear button
                actions.querySelector('.filter-clear').addEventListener('click', (e) => {
                    e.stopPropagation();
                    delete this.filters[column];
                    this.applyAllFilters();
                    header.classList.remove('filtered');
                    dropdown.remove();
                });
                
                dropdown.addEventListener('click', (e) => e.stopPropagation());
                
                return dropdown;
            }
            
            getColumnValues(column) {
                const values = new Set();
                this.tbody.querySelectorAll(`td[data-column="${column}"]`).forEach(cell => {
                    let value = cell.textContent.trim();
                    // Remove badge wrappers
                    const badge = cell.querySelector('.badge');
                    if (badge) value = badge.textContent.trim();
                    if (value) values.add(value);
                });
                return Array.from(values).sort();
            }
            
            applyFilter(column, values) {
                this.filters[column] = values;
                this.applyAllFilters();
            }
            
            applyAllFilters() {
                const rows = this.tbody.querySelectorAll('tr');
                rows.forEach(row => {
                    let show = true;
                    for (const [column, values] of Object.entries(this.filters)) {
                        const cell = row.querySelector(`td[data-column="${column}"]`);
                        if (cell) {
                            let cellValue = cell.textContent.trim();
                            const badge = cell.querySelector('.badge');
                            if (badge) cellValue = badge.textContent.trim();
                            
                            if (!values.includes(cellValue)) {
                                show = false;
                                break;
                            }
                        }
                    }
                    row.style.display = show ? '' : 'none';
                });
            }
            
            closeAllFilters() {
                document.querySelectorAll('.filter-dropdown').forEach(d => d.remove());
            }
            
            sortColumn(column, order) {
                const rows = Array.from(this.tbody.querySelectorAll('tr'));
                rows.sort((a, b) => {
                    const aVal = a.querySelector(`td[data-column="${column}"]`).textContent.trim().toLowerCase();
                    const bVal = b.querySelector(`td[data-column="${column}"]`).textContent.trim().toLowerCase();
                    
                    if (order === 'asc') {
                        return aVal.localeCompare(bVal, 'id', { numeric: true });
                    } else {
                        return bVal.localeCompare(aVal, 'id', { numeric: true });
                    }
                });
                
                rows.forEach(row => this.tbody.appendChild(row));
                this.closeAllFilters();
            }
            
            globalSearch(term) {
                term = term.toLowerCase();
                const rows = this.tbody.querySelectorAll('tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            }
        }
        
        const tableFilter = new TableFilter('booksTable');
    </script>
</body>
</html>