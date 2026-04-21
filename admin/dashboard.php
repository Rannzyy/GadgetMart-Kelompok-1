<?php
session_start();
include '../koneksi.php';

// Cek admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses admin!'); window.location='../index.php';</script>";
    exit;
}

// Total produk
$total_produk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM produk"))['total'];

// Total user customer
$total_user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users WHERE role='customer'"))['total'];

// Total transaksi
$total_transaksi = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM transaksi"))['total'];

//  Total pemasukan
$q_pemasukan = mysqli_query($koneksi, "SELECT SUM(jumlah) AS total FROM pemasukan");
$data_pemasukan = mysqli_fetch_assoc($q_pemasukan);
$total_pemasukan = $data_pemasukan['total'] ?? 0;

// Data transaksi bulanan
$chart_transaksi = mysqli_query($koneksi, "
    SELECT DATE_FORMAT(tanggal_pesan, '%M') AS bulan, COUNT(*) AS total
    FROM transaksi 
    GROUP BY MONTH(tanggal_pesan)
    ORDER BY MONTH(tanggal_pesan)
");

$bulan = [];
$jumlah_transaksi = [];
while ($row = mysqli_fetch_assoc($chart_transaksi)) {
    $bulan[] = $row['bulan'];
    $jumlah_transaksi[] = $row['total'];
}

// Data pemasukan bulanan
$chart_pemasukan = mysqli_query($koneksi, "
    SELECT DATE_FORMAT(tanggal, '%M') AS bulan, SUM(jumlah) AS total
    FROM pemasukan 
    GROUP BY MONTH(tanggal)
    ORDER BY MONTH(tanggal)
");

$jumlah_pemasukan = [];
while ($row = mysqli_fetch_assoc($chart_pemasukan)) {
    $jumlah_pemasukan[] = $row['total'] ?? 0;
}

// --- TOP PRODUK TERLARIS (tampilkan gambar utama) ---
$top_produk = mysqli_query($koneksi, "
    SELECT p.id_produk, p.nama_produk, p.gambar, SUM(dt.jumlah) AS total_terjual
    FROM detail_transaksi dt
    JOIN produk p ON dt.id_produk = p.id_produk
    GROUP BY p.id_produk
    ORDER BY total_terjual DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | GadgetMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        /* Style untuk gambar produk */
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s;
        }

        .product-image:hover {
            transform: scale(1.1);
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .product-name {
            font-weight: 600;
            color: #1e293b;
        }

        .sold-count {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }

        .status-badge.sold {
            background: #10b981;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .text-center {
            text-align: center;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
        }

        .data-table tbody tr:hover {
            background: #f1f5f9;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo-area">
                <i class="fas fa-store"></i>
                <h2>Dashboard Admin</h2>
                <span class="badge-admin">GadgetMart</span>
            </div>
            <a class="logout-btn" href="../user/logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-box-open"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Produk</h3>
                    <div class="stat-value"><?= number_format($total_produk) ?></div>
                    <a href="data_produk.php" class="stat-link">
                        Lihat Detail <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon cyan">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Customer</h3>
                    <div class="stat-value"><?= number_format($total_user) ?></div>
                    <a href="data_user.php" class="stat-link">
                        Lihat Detail <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Transaksi</h3>
                    <div class="stat-value"><?= number_format($total_transaksi) ?></div>
                    <a href="data_transaksi.php" class="stat-link">
                        Lihat Detail <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Pemasukan</h3>
                    <div class="stat-value">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></div>
                    <a href="data_pemasukkan.php" class="stat-link">
                        Lihat Detail <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Top Products Section -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-title">
                    <i class="fas fa-trophy"></i>
                    <h3>Top 5 Produk Terlaris</h3>
                </div>
                <span class="section-badge">Best Seller</span>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Gambar</th>
                            <th>Total Terjual</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($top_produk) > 0):
                            while ($produk = mysqli_fetch_assoc($top_produk)):
                                // Cek gambar utama dari berbagai kemungkinan lokasi
                                $image_path = "../gambar/" . $produk['gambar'];

                                // Cek apakah gambar ada di folder gambar/produk/id_produk/
                                if (!file_exists($image_path)) {
                                    // Coba cari di folder produk dengan id
                                    $id_produk = $produk['id_produk'];
                                    $alt_path = "../gambar/produk/" . $id_produk . "/" . $produk['gambar'];
                                    if (file_exists($alt_path)) {
                                        $image_path = $alt_path;
                                    } else {
                                        // Jika tidak ada gambar, gunakan placeholder
                                        $image_path = "https://via.placeholder.com/60x60?text=No+Image";
                                    }
                                }
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?> </td>
                                    <td class="product-name"><?= htmlspecialchars($produk['nama_produk']) ?></td>
                                    <td>
                                        <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($produk['nama_produk']) ?>"
                                            class="product-image">
                                    </td> 
                                    <td class="text-center">
                                        <span class="sold-count"><?= number_format($produk['total_terjual']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="status-badge sold">Terjual</span>
                                    </td>
                                </tr>
                                <?php
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="5" class="text-center" style="padding: 40px;">
                                    <i class="fas fa-box-open" style="font-size: 48px; color: #cbd5e1;"></i>
                                    <p style="margin-top: 10px; color: #64748b;">Belum ada data transaksi</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>