<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    echo "<script>alert('Akses hanya untuk customer!'); window.location='../index.php';</script>";
    exit;
}

$id_transaksi = $_GET['id'];
$id_user = $_SESSION['id_user'];

// Pastikan transaksi itu milik user
$cek = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE id_transaksi='$id_transaksi' AND id_user='$id_user'");
if (mysqli_num_rows($cek) == 0) {
    echo "<script>alert('Data tidak ditemukan.'); window.location='pesanan_saya.php';</script>";
    exit;
}

// Ambil detail produk
$detail = mysqli_query($koneksi, "
    SELECT dt.*, p.nama_produk, p.harga, p.gambar
    FROM detail_transaksi dt
    JOIN produk p ON dt.id_produk = p.id_produk
    WHERE dt.id_transaksi = '$id_transaksi'
");

// Ambil info transaksi + user
$info = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT t.catatan, t.status, t.tanggal_pesan, t.metode_pembayaran, t.tanggal_selesai,
           u.nama AS nama_pembeli, u.no_hp, u.alamat
    FROM transaksi t 
    JOIN users u ON t.id_user = u.id_user
    WHERE t.id_transaksi = '$id_transaksi' AND t.id_user = '$id_user'
"));

$alamat_pengirim = $info['alamat'];
$catatan = $info['catatan'];
$status = $info['status'];
$tanggal = $info['tanggal_pesan'];
$nama_pembeli = $info['nama_pembeli'];
$no_telepon = $info['no_hp'];
$metode_pembayaran = $info['metode_pembayaran'] ?? '-';
$tanggal_selesai = $info['tanggal_selesai'] ?? null;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?= $id_transaksi ?> | GadgetMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-light: #93c5fd;
            --primary-dark: #1d4ed8;
            --secondary: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #1f2937;
            --gray: #6b7280;
            --gray-light: #e5e7eb;
            --light: #f9fafb;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9eef5 100%);
            color: var(--dark);
            line-height: 1.6;
            padding: 2rem;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Container */
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Back Link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            padding: 0.6rem 1.2rem;
            border-radius: 0.5rem;
            color: var(--primary);
            font-weight: 500;
            transition: var(--transition);
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .back-link:hover {
            background: var(--primary);
            color: white;
            transform: translateX(-5px);
        }

        /* Page Title */
        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 3px solid var(--primary);
            display: inline-block;
        }

        /* Card */
        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 1rem 1.5rem;
        }

        .card-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            background: var(--light);
            padding: 1rem;
            border-radius: 0.75rem;
            border-left: 3px solid var(--primary);
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--gray);
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-value {
            font-weight: 600;
            color: var(--dark);
            word-break: break-word;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-menunggu {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-diproses {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-sedang_diantar,
        .status-sedang diantar {
            background-color: #fef9c3;
            color: #854d0e;
        }

        .status-selesai {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-dibatalkan {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
        }

        .product-table th,
        .product-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }

        .product-table th {
            background: var(--light);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            color: var(--gray);
        }

        .product-table tr:hover td {
            background: #f8fafc;
        }

        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 0.5rem;
            border: 1px solid var(--gray-light);
        }

        .product-name {
            font-weight: 500;
        }

        /* Summary Row */
        .summary-row {
            background: var(--light);
            font-weight: 600;
        }

        .summary-row td {
            border-bottom: none;
        }

        .grandtotal {
            font-size: 1.1rem;
            color: var(--primary);
        }

        /* Button */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .product-table th,
            .product-table td {
                padding: 0.75rem;
            }

            .product-img {
                width: 45px;
                height: 45px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Back Button -->
        <a href="pesanan_saya.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Pesanan Saya
        </a>
        <div>
        <!-- Page Title -->
        <h1 class="page-title">
            <i class="fas fa-receipt"></i> Detail Pesanan #<?= $id_transaksi ?>
        </h1>
        </div>
        <!-- Informasi Pesanan -->
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fas fa-info-circle"></i> Informasi Pesanan
                </h3>
            </div>
            <div class="card-body">
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
                            <i class="fas fa-calendar"></i> Tanggal Pesan
                        </div>
                        <div class="info-value"><?= date('d M Y H:i', strtotime($tanggal)) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-credit-card"></i> Metode Pembayaran
                        </div>
                        <div class="info-value"><?= htmlspecialchars($metode_pembayaran) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-truck"></i> Status Pesanan
                        </div>
                        <div class="info-value">
                            <span class="status-badge status-<?= strtolower(str_replace(' ', '_', $status)) ?>">
                                <?= htmlspecialchars($status) ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-check-circle"></i> Tanggal Selesai
                        </div>
                        <div class="info-value">
                            <?php if ($status == 'Selesai' && !empty($tanggal_selesai)): ?>
                                <?= date('d M Y H:i', strtotime($tanggal_selesai)) ?>
                            <?php elseif ($status == 'Selesai'): ?>
                                <?= date('d M Y H:i', strtotime($tanggal)) ?>
                            <?php else: ?>
                                <span style="color: var(--gray);">- Belum selesai -</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-map-marker-alt"></i> Alamat Pengiriman
                        </div>
                        <div class="info-value">
                            <?= !empty($alamat_pengirim) ? nl2br(htmlspecialchars($alamat_pengirim)) : '-' ?>
                        </div>
                    </div>
                    <?php if (!empty($catatan)): ?>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-sticky-note"></i> Catatan untuk Penjual
                            </div>
                            <div class="info-value"><?= nl2br(htmlspecialchars($catatan)) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Rincian Produk -->
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fas fa-box"></i> Rincian Produk
                </h3>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Produk</th>
                                <th>Nama Produk</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $grandtotal = 0;
                            $totalItem = 0;
                            while ($row = mysqli_fetch_assoc($detail)) {
                                $subtotal = $row['harga'] * $row['jumlah'];
                                $grandtotal += $subtotal;
                                $totalItem += $row['jumlah'];
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td>
                                        <img src="../gambar/<?= htmlspecialchars($row['gambar']); ?>"
                                            alt="<?= htmlspecialchars($row['nama_produk']); ?>" class="product-img">
                                    </td>
                                    <td class="product-name"><?= htmlspecialchars($row['nama_produk']); ?></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                                    <td><?= number_format($row['jumlah']); ?></td>
                                    <td>Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                </tr>
                            <?php } ?>
                            <tr class="summary-row">
                                <td colspan="4"></td>
                                <td><strong>Total Barang</strong></td>
                                <td><strong><?= number_format($totalItem); ?> item</strong></td>
                            </tr>
                            <tr class="summary-row">
                                <td colspan="4"></td>
                                <td><strong>Grand Total</strong></td>
                                <td class="grandtotal"><strong>Rp
                                        <?= number_format($grandtotal, 0, ',', '.'); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi (opsional) -->
        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem;">
            <a href="pesanan_saya.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Kembali ke Pesanan Saya
            </a>
        </div>
    </div>
</body>

</html>