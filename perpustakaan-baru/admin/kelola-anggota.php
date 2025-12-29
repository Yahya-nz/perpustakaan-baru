<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$message = '';
$error = '';

// Handle aksi anggota
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Ubah status anggota
    if ($action === 'change_status') {
        $id_anggota = $_POST['id_anggota'];
        $status = $_POST['status'];
        
        try {
            query(
                "UPDATE anggota SET status_keanggotaan = ? WHERE id_anggota = ?",
                [$status, $id_anggota]
            );
            $message = 'Status anggota berhasil diubah';
        } catch (Exception $e) {
            $error = 'Gagal mengubah status: ' . $e->getMessage();
        }
    }
    
    // Hapus anggota
    if ($action === 'delete') {
        $id_anggota = $_POST['id_anggota'];
        
        // Cek apakah masih ada peminjaman aktif
        $aktif = query(
            "SELECT COUNT(*) as total FROM peminjaman WHERE id_anggota = ? AND status = 'ACTIVE'",
            [$id_anggota]
        )->fetch()['total'];
        
        if ($aktif > 0) {
            $error = 'Tidak dapat menghapus anggota yang masih memiliki peminjaman aktif';
        } else {
            try {
                query("DELETE FROM anggota WHERE id_anggota = ?", [$id_anggota]);
                $message = 'Anggota berhasil dihapus';
            } catch (Exception $e) {
                $error = 'Gagal menghapus anggota: ' . $e->getMessage();
            }
        }
    }
}

// Ambil semua anggota
$anggota = query(
    "SELECT 
        a.id_anggota,
        a.nik,
        a.nama_lengkap,
        a.email,
        a.telepon,
        a.status_keanggotaan,
        a.terdaftar_pada,
        COUNT(DISTINCT CASE WHEN p.status = 'ACTIVE' THEN p.id_peminjaman END) as sedang_pinjam,
        COUNT(DISTINCT CASE WHEN p.status = 'RETURNED' THEN p.id_peminjaman END) as total_pinjam,
        COALESCE(SUM(p.jumlah_denda), 0) as total_denda
     FROM anggota a
     LEFT JOIN peminjaman p ON a.id_anggota = p.id_anggota
     GROUP BY a.id_anggota
     ORDER BY a.terdaftar_pada DESC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Anggota - Admin</title>
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
        <div class="container-fluid">
            <div class="dashboard-header">
                <div class="dashboard-welcome">
                    <div class="dashboard-title">
                        <h1>Kelola Anggota</h1>
                        <p>Kelola data dan status anggota perpustakaan</p>
                    </div>
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

            <!-- Tabel Anggota -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Daftar Anggota (<?php echo count($anggota); ?>)</h3>
                    <input type="text" id="globalSearch" class="search-input" placeholder="Cari di semua kolom..." style="max-width: 300px;">
                </div>
                <div class="table-wrapper">
                    <table id="anggotaTable">
                        <thead>
                            <tr>
                                <th class="table-filter-header" data-column="nama">
                                    NAMA
                                    <span class="filter-icon">▼</span>
                                </th>
                                <th class="table-filter-header" data-column="email">
                                    EMAIL
                                    <span class="filter-icon">▼</span>
                                </th>
                                <th class="table-filter-header" data-column="telepon">
                                    TELEPON
                                    <span class="filter-icon">▼</span>
                                </th>
                                <th class="table-filter-header" data-column="terdaftar">
                                    TERDAFTAR
                                    <span class="filter-icon">▼</span>
                                </th>
                                <th class="table-filter-header" data-column="sedang_pinjam">
                                    SEDANG PINJAM
                                    <span class="filter-icon">▼</span>
                                </th>
                                <th class="table-filter-header" data-column="total_denda">
                                    TOTAL DENDA
                                    <span class="filter-icon">▼</span>
                                </th>
                                <th class="table-filter-header" data-column="status">
                                    STATUS
                                    <span class="filter-icon">▼</span>
                                </th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="anggotaTableBody">
                            <?php foreach ($anggota as $item): ?>
                                <tr class="anggota-row">
                                    <td data-column="nama">
                                        <strong><?php echo htmlspecialchars($item['nama_lengkap']); ?></strong>
                                        <br><small style="color: var(--text-muted);">NIK: <?php echo htmlspecialchars($item['nik']); ?></small>
                                    </td>
                                    <td data-column="email"><?php echo htmlspecialchars($item['email']); ?></td>
                                    <td data-column="telepon"><?php echo htmlspecialchars($item['telepon'] ?? '-'); ?></td>
                                    <td data-column="terdaftar"><?php echo date('d M Y', strtotime($item['terdaftar_pada'])); ?></td>
                                    <td data-column="sedang_pinjam">
                                        <?php if ($item['sedang_pinjam'] > 0): ?>
                                            <span class="badge badge-warning"><?php echo $item['sedang_pinjam']; ?> buku</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-column="total_denda">
                                        <?php if ($item['total_denda'] > 0): ?>
                                            <span class="badge badge-error">Rp <?php echo number_format($item['total_denda'], 0, ',', '.'); ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Tidak ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-column="status">
                                        <?php
                                        $statusClass = 'badge-success';
                                        if ($item['status_keanggotaan'] === 'SUSPENDED') $statusClass = 'badge-warning';
                                        if ($item['status_keanggotaan'] === 'BANNED') $statusClass = 'badge-error';
                                        if ($item['status_keanggotaan'] === 'EXPIRED') $statusClass = 'badge-info';
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo $item['status_keanggotaan']; ?></span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-secondary" onclick="ubahStatus(<?php echo $item['id_anggota']; ?>, '<?php echo htmlspecialchars($item['nama_lengkap']); ?>', '<?php echo $item['status_keanggotaan']; ?>')" title="Ubah Status">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </button>
                                            <?php if ($item['sedang_pinjam'] == 0): ?>
                                                <form method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Yakin ingin menghapus anggota ini?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id_anggota" value="<?php echo $item['id_anggota']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus Anggota">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
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

    <!-- Modal Ubah Status -->
    <div id="modalUbahStatus" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Ubah Status Anggota</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="change_status">
                    <input type="hidden" name="id_anggota" id="status_id_anggota">
                    
                    <p id="status_anggota_name" style="margin-bottom: 1.5rem; color: var(--text-secondary);"></p>
                    
                    <div class="form-group">
                        <label class="form-label">Status Keanggotaan</label>
                        <select name="status" id="status_select" class="form-select" required>
                            <option value="ACTIVE">Aktif</option>
                            <option value="SUSPENDED">Suspended</option>
                            <option value="EXPIRED">Expired</option>
                            <option value="BANNED">Banned</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        function ubahStatus(id, nama, currentStatus) {
            document.getElementById('status_id_anggota').value = id;
            document.getElementById('status_anggota_name').textContent = 'Anggota: ' + nama;
            document.getElementById('status_select').value = currentStatus;
            openModal('modalUbahStatus');
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
                    // Remove badge wrappers and extra text
                    const badge = cell.querySelector('.badge');
                    if (badge) {
                        value = badge.textContent.trim();
                    }
                    // Remove "buku" text from sedang_pinjam
                    value = value.replace(' buku', '');
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
                            if (badge) {
                                cellValue = badge.textContent.trim();
                            }
                            // Remove "buku" text
                            cellValue = cellValue.replace(' buku', '');
                            
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
                    let aVal = a.querySelector(`td[data-column="${column}"]`).textContent.trim().toLowerCase();
                    let bVal = b.querySelector(`td[data-column="${column}"]`).textContent.trim().toLowerCase();
                    
                    // Remove badge text
                    const aBadge = a.querySelector(`td[data-column="${column}"] .badge`);
                    const bBadge = b.querySelector(`td[data-column="${column}"] .badge`);
                    if (aBadge) aVal = aBadge.textContent.trim().toLowerCase();
                    if (bBadge) bVal = bBadge.textContent.trim().toLowerCase();
                    
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
        
        const tableFilter = new TableFilter('anggotaTable');
    </script>
</body>
</html>