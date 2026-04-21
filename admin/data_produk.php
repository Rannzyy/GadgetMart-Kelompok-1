<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses hanya untuk admin!'); window.location='../index.php';</script>";
    exit;
}

// Ambil keyword pencarian jika ada
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

// Query dengan urutan: stok habis (0) di atas, lalu stok > 0, dan pencarian
$query = "SELECT * FROM produk 
          WHERE nama_produk LIKE '%$search%' OR deskripsi LIKE '%$search%'
          ORDER BY 
            CASE WHEN stok <= 0 THEN 0 ELSE 1 END,
            id_produk DESC";

$produk = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Data Produk - Admin | GadgetMart</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/data_produk.css">
</head>

<body>

    <div class="topbar">
        <div>
            <i class="fas fa-user-shield"></i> Hai, <?= htmlspecialchars($_SESSION['username']); ?>
        </div>
        <div>
            <a class="btn btn-primary" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a class="btn btn-danger" href="../user/logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="container">
        <h2><i class="fas fa-boxes"></i> Data Produk</h2>

        <!-- Search dan Add Product dalam satu baris -->
        <div class="header-actions">
            <div class="search-container">
                <form method="GET" action="" class="search-form" id="searchForm">
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" 
                               name="search" 
                               id="searchInput"
                               placeholder="Cari produk berdasarkan nama atau deskripsi..." 
                               value="<?= htmlspecialchars($search); ?>" 
                               autocomplete="off">
                        <?php if (!empty($search)): ?>
                            <a href="data_produk.php" class="clear-search" title="Hapus pencarian">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        <?php endif; ?>
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </form>
            </div>
            <div class="add-product-btn">
                <a href="tambah_produk.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Produk
                </a>
            </div>
        </div>

        <!-- Info hasil pencarian -->
        <?php if (!empty($search)): ?>
            <div class="search-info">
                <i class="fas fa-search"></i> 
                <span>Hasil pencarian untuk:</span> 
                <strong>"<?= htmlspecialchars($search); ?>"</strong>
                <span class="badge-stock-count" style="background: var(--primary);">
                    <?= mysqli_num_rows($produk); ?> produk ditemukan
                </span>
                <a href="data_produk.php" class="btn-link">
                    <i class="fas fa-times"></i> Tampilkan semua produk
                </a>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Deskripsi</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $hasStockHabis = false;
                    $totalProduk = mysqli_num_rows($produk);
                    
                    // Fungsi untuk mendapatkan path gambar
                    function getProductImagePath($id_produk, $gambar) {
                        // Cek apakah gambar ada di folder produk spesifik
                        $folder_path = "../gambar/produk/" . $id_produk . "/" . $gambar;
                        $old_path = "../gambar/" . $gambar;
                        
                        if (!empty($gambar) && file_exists($folder_path)) {
                            return $folder_path;
                        } elseif (!empty($gambar) && file_exists($old_path)) {
                            return $old_path;
                        } else {
                            return "../gambar/default.jpg";
                        }
                    }
                    
                    if ($totalProduk > 0) {
                        while ($row = mysqli_fetch_assoc($produk)) { 
                            $isStockHabis = ($row['stok'] <= 0);
                            $rowClass = $isStockHabis ? 'stock-habis-row' : '';
                            $imagePath = getProductImagePath($row['id_produk'], $row['gambar']);
                            
                            if ($isStockHabis && !$hasStockHabis && empty($search)) {
                                $hasStockHabis = true;
                                echo '<tr class="separator-row"><td colspan="8"><div class="separator">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        Produk dengan Stok Habis 
                                        <span class="badge-stock-count">Perlu Segera Diisi</span>
                                      </div></td></tr>';
                            }
                    ?>
                        <tr class="<?= $rowClass; ?>" data-stok="<?= $row['stok']; ?>">
                            <td><?= $no++; ?></td>
                            <td class="image-cell">
                                <div class="product-img-wrapper">
                                    <img src="<?= $imagePath; ?>" 
                                         class="product-img" 
                                         alt="<?= htmlspecialchars($row['nama_produk']); ?>"
                                         onerror="this.src='../gambar/default.jpg'">
                                    <?php if ($isStockHabis): ?>
                                        <div class="badge-stock-habis">HABIS</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['nama_produk']); ?>
                                <?php if ($isStockHabis): ?>
                                    <i class="fas fa-exclamation-triangle stock-warning-icon" title="Stok Habis!"></i>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(substr($row['deskripsi'], 0, 60)); ?>...</td>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if ($isStockHabis): ?>
                                    <span class="stock-zero">
                                        <i class="fas fa-times-circle"></i> <?= $row['stok']; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="stock-available">
                                        <i class="fas fa-box"></i> <?= $row['stok']; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'aktif'): ?>
                                    <span class="status status-active">
                                        <i class="fas fa-check-circle"></i> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="status status-inactive">
                                        <i class="fas fa-times-circle"></i> Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-links">
                                    <a href="edit_produk.php?id=<?= $row['id_produk']; ?>" class="edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <?php if ($row['status'] == 'aktif'): ?>
                                        <a href="javascript:void(0);" class="toggle" title="Nonaktifkan"
                                            onclick="konfirmasiToggle('<?= $row['id_produk']; ?>', 'nonaktif')">
                                            <i class="fas fa-toggle-on"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="javascript:void(0);" class="toggle" title="Aktifkan"
                                            onclick="konfirmasiToggle('<?= $row['id_produk']; ?>', 'aktif')">
                                            <i class="fas fa-toggle-off"></i>
                                        </a>
                                    <?php endif; ?>

                                    <a href="hapus_produk.php?id=<?= $row['id_produk']; ?>" class="delete" title="Hapus"
                                        onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else { 
                    ?>
                        <tr>
                            <td colspan="8" class="empty-data">
                                <div class="empty-state">
                                    <i class="fas fa-box-open"></i>
                                    <p><?= !empty($search) ? 'Produk "' . htmlspecialchars($search) . '" tidak ditemukan' : 'Belum ada produk' ?></p>
                                    <?php if (!empty($search)): ?>
                                        <a href="data_produk.php" class="btn-link">
                                            <i class="fas fa-arrow-left"></i> Lihat semua produk
                                        </a>
                                    <?php else: ?>
                                        <a href="tambah_produk.php" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus"></i> Tambah produk sekarang
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function konfirmasiToggle(id, aksi) {
            let pesan = aksi === 'nonaktif' ? 'Nonaktifkan produk ini?' : 'Aktifkan produk ini?';
            let ikon = aksi === 'nonaktif' ? 'warning' : 'question';
            let tombolKonfirmasi = aksi === 'nonaktif' ? 'Ya, Nonaktifkan!' : 'Ya, Aktifkan!';

            Swal.fire({
                title: 'Konfirmasi',
                text: pesan,
                icon: ikon,
                showCancelButton: true,
                confirmButtonColor: aksi === 'nonaktif' ? '#d33' : '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: tombolKonfirmasi,
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = "toggle_produk.php?id=" + id + "&aksi=" + aksi;
                }
            });
        }

        // Keyboard shortcut Enter to search
        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('searchForm').submit();
            }
        });
    </script>

</body>

</html>