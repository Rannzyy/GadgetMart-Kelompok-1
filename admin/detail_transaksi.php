<?php
session_start();
include '../koneksi.php';

// Cek role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses hanya untuk admin!'); window.location='../index.php';</script>";
    exit;
}

// Cek apakah id_transaksi ada
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID Transaksi tidak ditemukan!'); window.location='data_transaksi.php';</script>";
    exit;
}

$id_transaksi = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil detail produk
$detail = mysqli_query($koneksi, "
    SELECT dt.*, p.id_produk, p.nama_produk, p.harga, p.gambar
    FROM detail_transaksi dt
    JOIN produk p ON dt.id_produk = p.id_produk
    WHERE dt.id_transaksi = '$id_transaksi'
");

// Cek apakah transaksi ada
if (mysqli_num_rows($detail) == 0) {
    echo "<script>alert('Transaksi tidak ditemukan!'); window.location='data_transaksi.php';</script>";
    exit;
}

// Ambil info transaksi utama + user
$query_info = mysqli_query($koneksi, "
    SELECT t.catatan, t.status, t.tanggal_pesan, t.total_harga,
           u.nama AS nama_pembeli, u.no_hp, u.alamat
    FROM transaksi t 
    JOIN users u ON t.id_user = u.id_user
    WHERE t.id_transaksi = '$id_transaksi'
");

if (!$query_info || mysqli_num_rows($query_info) == 0) {
    echo "<script>alert('Data transaksi tidak ditemukan!'); window.location='data_transaksi.php';</script>";
    exit;
}

$info = mysqli_fetch_assoc($query_info);

$alamat_pengirim = $info['alamat'];
$catatan = $info['catatan'];
$status = $info['status'];
$tanggal = $info['tanggal_pesan'];
$nama_pembeli = $info['nama_pembeli'];
$no_telepon = $info['no_hp'];
$total_harga = $info['total_harga'];

// Status class dan icon
$status_class = '';
$status_icon = '';
switch($status) {
    case 'Menunggu':
        $status_class = 'status-menunggu';
        $status_icon = 'fa-clock';
        break;
    case 'Diproses':
        $status_class = 'status-diproses';
        $status_icon = 'fa-spinner';
        break;
    case 'Dikirim':
        $status_class = 'status-dikirim';
        $status_icon = 'fa-truck';
        break;
    case 'Selesai':
        $status_class = 'status-selesai';
        $status_icon = 'fa-check-circle';
        break;
    default:
        $status_class = 'status-menunggu';
        $status_icon = 'fa-clock';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Transaksi #<?= $id_transaksi ?> - Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/detail_trans.css">
    <style>
        /* Tambahan style untuk gambar */
        .product-img-wrapper {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            overflow: hidden;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>

<div class="dashboard-header">
    <div class="container">
        <div class="header-content">
            <div class="header-title">
                <h3 class="page-title mb-0">
                    <i class="fas fa-receipt text-success"></i> Detail Transaksi #<?= $id_transaksi ?>
                </h3>
                <p class="text-muted mb-0 mt-1">Informasi lengkap transaksi pelanggan</p>
            </div>
            <div class="header-actions">
                <a href="data_transaksi.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali ke Transaksi
                </a>
                <a href="cetak_invoice.php?id=<?= $id_transaksi ?>" target="_blank" class="btn btn-print" onclick="return confirm('Cetak invoice?')">
                    <i class="fas fa-print"></i> Cetak Invoice
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Status Card -->
    <div class="status-card">
        <div class="status-card-content">
            <div class="status-icon <?= $status_class ?>">
                <i class="fas <?= $status_icon ?>"></i>
            </div>
            <div class="status-info">
                <span class="status-label">Status Transaksi</span>
                <span class="status-value <?= $status_class ?>">
                    <?= htmlspecialchars($status) ?>
                </span>
            </div>
        </div>
        <div class="status-date">
            <i class="far fa-calendar-alt"></i>
            <?= date('d M Y, H:i', strtotime($tanggal)) ?>
        </div>
    </div>

    <!-- Customer Information Card -->
    <div class="data-card">
        <div class="data-card-header">
            <div class="header-left">
                <i class="fas fa-user-circle"></i>
                <span>Informasi Pelanggan</span>
            </div>
        </div>
        <div class="data-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-user"></i> Nama Pemesan
                    </div>
                    <div class="info-value"><?= htmlspecialchars($nama_pembeli) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-phone"></i> No. Telepon
                    </div>
                    <div class="info-value"><?= !empty($no_telepon) ? htmlspecialchars($no_telepon) : '-' ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-map-marker-alt"></i> Alamat Pengiriman
                    </div>
                    <div class="info-value"><?= !empty($alamat_pengirim) ? nl2br(htmlspecialchars($alamat_pengirim)) : '-' ?></div>
                </div>
            </div>

            <?php if (!empty($catatan)): ?>
            <div class="note-section">
                <div class="info-label">
                    <i class="fas fa-sticky-note"></i> Catatan Pelanggan
                </div>
                <div class="note-content">
                    <?= nl2br(htmlspecialchars($catatan)) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Items Card -->
    <div class="data-card">
        <div class="data-card-header">
            <div class="header-left">
                <i class="fas fa-boxes"></i>
                <span>Rincian Produk</span>
            </div>
            <div class="header-right">
                <span class="total-badge">
                    <i class="fas fa-chart-simple"></i>
                    Total: Rp <?= number_format($total_harga, 0, ',', '.') ?>
                </span>
            </div>
        </div>
        <div class="data-card-body p-0">
            <div class="table-responsive-custom">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Harga Satuan</th>
                            <th>Jumlah</th>
                            <th class="text-end">Subtotal</th>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $grandtotal = 0;
                            $totalItem = 0;
                            mysqli_data_seek($detail, 0); // Reset pointer
                            while ($row = mysqli_fetch_assoc($detail)) {
                                $subtotal = $row['harga'] * $row['jumlah'];
                                $grandtotal += $subtotal;
                                $totalItem += $row['jumlah'];
                                
                                // Cari path gambar yang benar untuk multiple images
                                $image_path = "../gambar/" . $row['gambar'];
                                
                                // Cek apakah gambar ada di folder produk dengan id
                                if (!file_exists($image_path)) {
                                    $alt_path = "../gambar/produk/" . $row['id_produk'] . "/" . $row['gambar'];
                                    if (file_exists($alt_path)) {
                                        $image_path = $alt_path;
                                    } else {
                                        // Coba cari gambar primary dari produk_gambar
                                        $img_query = mysqli_query($koneksi, "
                                            SELECT gambar FROM produk_gambar 
                                            WHERE id_produk = '{$row['id_produk']}' 
                                            AND is_primary = 1 
                                            LIMIT 1
                                        ");
                                        $primary_img = mysqli_fetch_assoc($img_query);
                                        if ($primary_img) {
                                            $alt_path2 = "../gambar/produk/" . $row['id_produk'] . "/" . $primary_img['gambar'];
                                            if (file_exists($alt_path2)) {
                                                $image_path = $alt_path2;
                                            }
                                        }
                                    }
                                }
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td class="image-cell">
                                        <div class="product-img-wrapper">
                                            <img src="<?= $image_path; ?>" 
                                                 class="product-img" 
                                                 alt="<?= htmlspecialchars($row['nama_produk']); ?>"
                                                 onerror="this.src='../gambar/default.jpg'">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="product-name">
                                            <?= htmlspecialchars($row['nama_produk']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="price-amount">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></span>
                                    </td>
                                    <td>
                                        <span class="quantity-badge">
                                            <i class="fas fa-times"></i> <?= $row['jumlah']; ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="price-amount">Rp <?= number_format($subtotal, 0, ',', '.'); ?></span>
                                    </td>
                                </tr>
                            <?php } ?>
                            
                            <tr class="summary-row">
                                <td colspan="5" class="summary-label">Total Barang</td>
                                <td class="text-end summary-value"><?= $totalItem ?> item</td>
                            </tr>
                            
                            <tr class="grand-total-row">
                                <td colspan="5" class="summary-label">Grand Total</td>
                                <td class="text-end grand-total-value">Rp <?= number_format($grandtotal, 0, ',', '.'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Mencegah form submit yang tidak disengaja
    document.addEventListener('DOMContentLoaded', function() {
        // Cegah semua form agar tidak submit secara default
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                return false;
            });
        });
        
        // Animasi cards
        const cards = document.querySelectorAll('.data-card, .status-card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
    
    // Tambahkan console log untuk debugging
    console.log('Detail transaksi loaded successfully');
    console.log('ID Transaksi: <?= $id_transaksi ?>');
</script>

</body>
</html>