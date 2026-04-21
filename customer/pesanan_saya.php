<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    echo "<script>alert('Akses hanya untuk customer!'); window.location='../index.php';</script>";
    exit;
}

$id_user = $_SESSION['id_user'];

$pesanan = mysqli_query($koneksi, "
    SELECT t.*, dt.id_produk, p.nama_produk, p.gambar 
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    JOIN produk p ON dt.id_produk = p.id_produk
    WHERE t.id_user = '$id_user' 
    ORDER BY t.id_transaksi DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya | GadgetMart.id</title>
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
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);

            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
            color: var(--dark);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            border: 1px solid transparent;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-outline {
            background-color: transparent;
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            transition: var(--transition);
            position: relative;
        }

        .btn-icon:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .cart-count {
            position: absolute;
            top: -0.25rem;
            right: -0.25rem;
            background-color: var(--danger);
            color: white;
            border-radius: 50%;
            width: 1.25rem;
            height: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Header Styles */
        header {
            background-color: white;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
        }

        .logo i {
            font-size: 1.5rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex: 1;
            margin-left: 2rem;
        }

        .nav-links a {
            font-weight: 500;
            transition: var(--transition);
            padding: 0.5rem 0;
            position: relative;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary);
            transition: var(--transition);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Container Styles */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark);
        }

        .back-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Orders Card */
        .orders-card {
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: var(--shadow-sm);
            overflow-x: auto;
            overflow-y: hidden;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .orders-table th,
        .orders-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
            vertical-align: top;
        }

        .orders-table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: var(--dark);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        .orders-table tr:last-child td {
            border-bottom: none;
        }

        .orders-table tr:hover td {
            background-color: #f8fafc;
        }

        /* Empty Orders */
        .empty-orders {
            padding: 3rem 1rem;
            text-align: center;
        }

        .empty-orders i {
            font-size: 3rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .empty-orders h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .empty-orders p {
            color: var(--gray);
            margin-bottom: 1.5rem;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-processing {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-shipped {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-completed {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-sedang-diantar {
            background-color: #fef9c3;
            color: #854d0e;
        }

        .status-selesai {
            background-color: #dcfce7;
            color: #166534;
        }

        /* Product Image Styles */
        .product-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .product-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem;
            background: #f9fafb;
            border-radius: 0.5rem;
            transition: var(--transition);
        }

        .product-item:hover {
            background: #f3f4f6;
        }

        .product-item-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 0.5rem;
            border: 1px solid var(--gray-light);
            background: white;
        }

        .product-item-name {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--dark);
            flex: 1;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .action-buttons .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
            width: 100%;
            text-align: center;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .action-buttons .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .action-buttons .btn-outline:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .action-buttons .btn-primary {
            background-color: var(--primary);
            border: 1px solid var(--primary);
            color: white;
        }

        .action-buttons .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .action-buttons .btn-warning {
            background-color: var(--warning);
            border: 1px solid var(--warning);
            color: white;
        }

        .action-buttons .btn-warning:hover {
            background-color: #d97706;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .action-buttons .rating-done {
            padding: 0.5rem 1rem;
            background-color: #f0fdf4;
            border-radius: 0.375rem;
            text-align: center;
            color: var(--secondary);
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: 1px solid #bbf7d0;
            width: 100%;
        }

        .action-buttons .rating-done i {
            font-size: 1rem;
        }

        .action-buttons form {
            margin: 0;
            width: 100%;
        }

        .action-buttons button {
            width: 100%;
            cursor: pointer;
        }

        .action-buttons .btn:active {
            transform: scale(0.98);
        }

        .action-divider {
            position: relative;
            text-align: center;
            margin: 0.5rem 0;
        }

        .action-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gray-light), transparent);
        }

        .action-divider span {
            position: relative;
            background: white;
            padding: 0 0.75rem;
            font-size: 0.7rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .realtime-time {
            font-family: monospace;
            font-size: 0.9rem;
            color: var(--gray);
        }

        .time-ago {
            font-size: 0.75rem;
            color: var(--gray);
            display: block;
            margin-top: 0.25rem;
        }

        @media (max-width: 768px) {
            .action-buttons .btn,
            .action-buttons .rating-done {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }

            .orders-table th,
            .orders-table td {
                padding: 0.75rem;
            }

            .product-item-img {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <header>
        <div class="navbar">
            <a href="../index.php" class="logo">
                <i class="fas fa-laptop-code"></i>
                <span>GadgetMart</span>
            </a>

            <div class="nav-links">
                <a href="../index.php">Beranda</a>
                <a href="../index.php?kategori=Laptop">Laptop</a>
                <a href="../index.php?kategori=HP">HP</a>
                <a href="../index.php?kategori=Tablet">Tablet</a>
                <a href="../index.php?kategori=Aksesoris">Aksesoris</a>
            </div>

            <div class="user-actions">
                <a href="../cart/keranjang.php" class="btn-icon" title="Keranjang">
                    <i class="fas fa-shopping-cart"></i>
                    <?php
                    $cart_count = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM keranjang WHERE id_user = '$id_user'");
                    $count = mysqli_fetch_assoc($cart_count);
                    if ($count['total'] > 0): ?>
                        <span class="cart-count"><?= $count['total']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="../user/logout.php" class="btn btn-outline">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Orders Content -->
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Pesanan Saya</h1>
            <a href="../index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>

        <div class="orders-card">
            <?php if (mysqli_num_rows($pesanan) > 0): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Pesan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Produk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $processed_ids = [];
                        while ($row = mysqli_fetch_assoc($pesanan)):
                            // Skip jika id_transaksi sudah diproses
                            if (in_array($row['id_transaksi'], $processed_ids)) {
                                continue;
                            }
                            $processed_ids[] = $row['id_transaksi'];

                            // Get all products for this transaction
                            $products_query = mysqli_query($koneksi, "
                                SELECT dt.*, p.nama_produk, p.gambar, p.id_produk
                                FROM detail_transaksi dt
                                JOIN produk p ON dt.id_produk = p.id_produk
                                WHERE dt.id_transaksi = '{$row['id_transaksi']}'
                            ");
                            
                            $products = [];
                            while ($product = mysqli_fetch_assoc($products_query)) {
                                // Cari path gambar yang benar
                                $image_path = "../gambar/" . $product['gambar'];
                                
                                // Cek apakah gambar ada di folder produk dengan id
                                if (!file_exists($image_path)) {
                                    $alt_path = "../gambar/produk/" . $product['id_produk'] . "/" . $product['gambar'];
                                    if (file_exists($alt_path)) {
                                        $image_path = $alt_path;
                                    } else {
                                        // Jika tetap tidak ada, coba cari di produk_gambar
                                        $img_query = mysqli_query($koneksi, "
                                            SELECT gambar FROM produk_gambar 
                                            WHERE id_produk = '{$product['id_produk']}' 
                                            AND is_primary = 1 
                                            LIMIT 1
                                        ");
                                        $primary_img = mysqli_fetch_assoc($img_query);
                                        if ($primary_img) {
                                            $alt_path2 = "../gambar/produk/" . $product['id_produk'] . "/" . $primary_img['gambar'];
                                            if (file_exists($alt_path2)) {
                                                $image_path = $alt_path2;
                                            } else {
                                                $image_path = "https://via.placeholder.com/50x50?text=No+Image";
                                            }
                                        } else {
                                            $image_path = "https://via.placeholder.com/50x50?text=No+Image";
                                        }
                                    }
                                }
                                
                                $product['image_path'] = $image_path;
                                $products[] = $product;
                            }

                            // Convert to timestamp untuk perhitungan waktu
                            $timestamp = strtotime($row['tanggal_pesan']);
                            $date_formatted = date('d M Y', $timestamp);
                            $time_formatted = date('H:i:s', $timestamp);
                            $timezone = date('T', $timestamp);

                            // Hitung waktu yang telah berlalu
                            $now = time();
                            $diff = $now - $timestamp;

                            if ($diff < 60) {
                                $time_ago = "Baru saja";
                            } elseif ($diff < 3600) {
                                $minutes = floor($diff / 60);
                                $time_ago = $minutes . " menit yang lalu";
                            } elseif ($diff < 86400) {
                                $hours = floor($diff / 3600);
                                $time_ago = $hours . " jam yang lalu";
                            } elseif ($diff < 604800) {
                                $days = floor($diff / 86400);
                                $time_ago = $days . " hari yang lalu";
                            } else {
                                $weeks = floor($diff / 604800);
                                $time_ago = $weeks . " minggu yang lalu";
                            }
                            ?>
                            <tr data-timestamp="<?= $timestamp; ?>">
                                <td><?= $no++; ?></td>
                                <td>
                                    <div class="realtime-time">
                                        <i class="fas fa-calendar-alt"></i> <?= $date_formatted; ?>
                                        <br>
                                        <i class="fas fa-clock"></i> <?= $time_formatted; ?> <?= $timezone; ?>
                                    </div>
                                    <div class="time-ago">
                                        <i class="fas fa-history"></i> <?= $time_ago; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($row['total_harga'] > 0): ?>
                                        Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?>
                                    <?php else: ?>
                                        <span style="color: var(--danger);">Rp 0 (Belum diproses)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['status'])); ?>">
                                        <?= $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="product-list">
                                        <?php foreach ($products as $product): ?>
                                            <div class="product-item">
                                                <img src="<?= $product['image_path']; ?>" 
                                                     alt="<?= htmlspecialchars($product['nama_produk']); ?>"
                                                     class="product-item-img"
                                                     onerror="this.src='https://via.placeholder.com/50x50?text=No+Image'">
                                                <span class="product-item-name">
                                                    <?= htmlspecialchars($product['nama_produk']); ?>
                                                    <small style="color: #6b7280; display: block;">
                                                        Qty: <?= $product['jumlah']; ?>
                                                    </small>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="detail_pesanan.php?id=<?= $row['id_transaksi']; ?>" class="btn btn-outline">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>

                                        <div class="action-divider">
                                            <span>Rating & Ulasan</span>
                                        </div>

                                        <?php if (strtolower($row['status']) == 'selesai'): ?>
                                            <?php
                                            $cek_rating = mysqli_query($koneksi, "
                                                SELECT * FROM rating 
                                                WHERE id_user = '$id_user' 
                                                AND id_transaksi = '{$row['id_transaksi']}'
                                            ");
                                            ?>

                                            <?php if (mysqli_num_rows($cek_rating) > 0): ?>
                                                <div class="rating-done">
                                                    <i class="fas fa-check-circle"></i> Sudah Dinilai
                                                </div>
                                            <?php else: ?>
                                                <a href="beri_rating.php?id=<?= $row['id_transaksi']; ?>" class="btn btn-warning">
                                                    <i class="fas fa-star"></i> Beri Rating & Ulasan
                                                </a>
                                            <?php endif; ?>
                                        <?php elseif (strtolower($row['status']) == 'sedang diantar'): ?>
                                            <form action="terima_pesanan.php" method="POST">
                                                <input type="hidden" name="id_transaksi" value="<?= $row['id_transaksi']; ?>">
                                                <button type="submit" class="btn btn-primary"
                                                    onclick="return confirm('Apakah Anda yakin telah menerima pesanan ini?')">
                                                    <i class="fas fa-check-circle"></i> Terima Pesanan
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-orders">
                    <i class="fas fa-box-open"></i>
                    <h3>Belum Ada Pesanan</h3>
                    <p>Anda belum melakukan pemesanan apapun</p>
                    <a href="../index.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Mulai Belanja
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Fungsi untuk update waktu realtime setiap detik
        function updateRealtimeTimes() {
            const rows = document.querySelectorAll('tbody tr');
            const now = Math.floor(Date.now() / 1000);

            rows.forEach(row => {
                const timestampAttr = row.getAttribute('data-timestamp');
                if (timestampAttr) {
                    const timestamp = parseInt(timestampAttr);
                    const diff = now - timestamp;

                    let timeAgo;
                    if (diff < 60) {
                        timeAgo = "Baru saja";
                    } else if (diff < 3600) {
                        const minutes = Math.floor(diff / 60);
                        timeAgo = minutes + " menit yang lalu";
                    } else if (diff < 86400) {
                        const hours = Math.floor(diff / 3600);
                        timeAgo = hours + " jam yang lalu";
                    } else if (diff < 604800) {
                        const days = Math.floor(diff / 86400);
                        timeAgo = days + " hari yang lalu";
                    } else {
                        const weeks = Math.floor(diff / 604800);
                        timeAgo = weeks + " minggu yang lalu";
                    }

                    const timeAgoElement = row.querySelector('.time-ago');
                    if (timeAgoElement) {
                        timeAgoElement.innerHTML = '<i class="fas fa-history"></i> ' + timeAgo;
                    }
                }
            });
        }

        // Update waktu setiap 30 detik
        setInterval(updateRealtimeTimes, 30000);
        
        // Panggil pertama kali saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            updateRealtimeTimes();
        });
    </script>
</body>

</html>