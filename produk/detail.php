<?php
session_start();
include '../koneksi.php';

$foto_profil = 'default.jpg';

if (isset($_SESSION['id_user'])) {
    $id_user = $_SESSION['id_user'];
    $getUser = mysqli_query($koneksi, "SELECT foto FROM users WHERE id_user = '$id_user'");
    $user = mysqli_fetch_assoc($getUser);
    if ($user && !empty($user['foto'])) {
        $foto_profil = $user['foto'];
    }
}

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit();
}

if (isset($_SESSION['id_user']) && $_SESSION['role'] == 'customer') {
    include '../cek_profil.php';
}

$id_produk = $_GET['id'];
$produk = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk = '$id_produk'");
$row = mysqli_fetch_assoc($produk);

// ========== AMBIL SEMUA GAMBAR DARI TABEL produk_gambar ==========
$query_gambar = "SELECT * FROM produk_gambar WHERE id_produk = '$id_produk' ORDER BY is_primary DESC, id_gambar ASC";
$result_gambar = mysqli_query($koneksi, $query_gambar);
$list_gambar = [];
while ($gbr = mysqli_fetch_assoc($result_gambar)) {
    $list_gambar[] = $gbr;
}

// Jika tidak ada gambar di tabel produk_gambar, gunakan gambar dari tabel produk
if (empty($list_gambar) && !empty($row['gambar'])) {
    $list_gambar[] = [
        'id_gambar' => 0,
        'gambar' => $row['gambar'],
        'is_primary' => 1
    ];
}
// ================================================================

$in_wishlist = false;
if (isset($_SESSION['id_user']) && $_SESSION['role'] == 'customer') {
    $id_user = $_SESSION['id_user'];
    $cek = mysqli_query($koneksi, "SELECT * FROM wishlist WHERE id_user = '$id_user' AND id_produk = '$id_produk'");
    if (mysqli_num_rows($cek) > 0) {
        $in_wishlist = true;
    }
}

if (!$row) {
    header("Location: ../index.php");
    exit();
}

// Fungsi untuk mendapatkan path gambar yang benar
function getImagePath($gambar, $id_produk = null)
{
    if (empty($gambar)) {
        return "../gambar/default.jpg";
    }

    // Cek di folder produk/id_produk/
    if ($id_produk && file_exists("../gambar/produk/" . $id_produk . "/" . $gambar)) {
        return "../gambar/produk/" . $id_produk . "/" . $gambar;
    }

    // Cek di folder gambar langsung
    if (file_exists("../gambar/" . $gambar)) {
        return "../gambar/" . $gambar;
    }

    return "../gambar/default.jpg";
}

// Hitung diskon
$discount = isset($row['harga_asli']) && $row['harga_asli'] > $row['harga']
    ? round((($row['harga_asli'] - $row['harga']) / $row['harga_asli'] * 100))
    : 0;

// Ambil rating rata-rata
$query_rating = mysqli_query($koneksi, "SELECT AVG(rating) as avg_rating, COUNT(*) as total_rating FROM rating WHERE id_produk = '$id_produk'");
$data_rating = mysqli_fetch_assoc($query_rating);
$rating = $data_rating['avg_rating'] ?? 0;
$total_rating = $data_rating['total_rating'] ?? 0;

// Hitung distribusi rating
$rating_counts = [];
for ($i = 1; $i <= 5; $i++) {
    $count_query = mysqli_query($koneksi, "SELECT COUNT(*) as count FROM rating WHERE id_produk = '$id_produk' AND rating = $i");
    $count_data = mysqli_fetch_assoc($count_query);
    $rating_counts[$i] = $count_data['count'] ?? 0;
}

$filter_rating = isset($_GET['rating']) ? (int) $_GET['rating'] : 0;

$query_ulasan = mysqli_query($koneksi, "
    SELECT r.*, u.nama as nama_user, u.foto as foto_user
    FROM rating r
    JOIN users u ON r.id_user = u.id_user
    WHERE r.id_produk = '$id_produk'
    ORDER BY r.tanggal DESC
");

$ulasan_filtered = [];
while ($ulasan = mysqli_fetch_assoc($query_ulasan)) {
    if ($filter_rating == 0 || $ulasan['rating'] == $filter_rating) {
        $ulasan_filtered[] = $ulasan;
    }
}

$produk_terkait = mysqli_query($koneksi, "SELECT * FROM produk WHERE kategori = '{$row['kategori']}' AND id_produk != '$id_produk' LIMIT 4");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $row['nama_produk']; ?> | GadgetMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #93c5fd;
            --secondary: #f59e0b;
            --danger: #ef4444;
            --success: #10b981;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        header {
            background: white;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 1000;
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
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .logo i {
            font-size: 1.8rem;
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
            background: var(--primary);
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

        .btn-outline {
            border: 1px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        .btn-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: white;
            box-shadow: var(--shadow-sm);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 1.2rem;
            height: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }

        .dropdown {
            position: relative;
        }

        .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            border: 1px solid var(--gray-light);
            border-radius: 2rem;
            padding: 0.4rem 1rem;
            cursor: pointer;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 0.5rem;
            box-shadow: var(--shadow-lg);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
            z-index: 1000;
        }

        .dropdown.open .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(5px);
        }

        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--dark);
            transition: var(--transition);
        }

        .dropdown-menu a:hover {
            background: var(--light);
            color: var(--primary);
        }

        .dropdown-divider {
            height: 1px;
            background: var(--gray-light);
            margin: 0.25rem 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin: 2rem 0;
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-md);
        }

        .product-gallery {
            position: sticky;
            top: 100px;
        }

        .main-image {
            width: 100%;
            height: 400px;
            border-radius: 1rem;
            overflow: hidden;
            background: var(--light);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .thumbnail-container {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            border-radius: 0.5rem;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: var(--transition);
            background: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .thumbnail.active {
            border-color: var(--primary);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            flex: 1;
        }

        .product-title {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .product-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            color: var(--gray);
            font-size: 0.9rem;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .rating-stars {
            color: var(--secondary);
        }

        .rating-count {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .price-container {
            margin-bottom: 1rem;
        }

        .current-price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .original-price {
            text-decoration: line-through;
            color: var(--gray);
            margin-left: 0.5rem;
        }

        .discount-badge {
            background: var(--danger);
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.8rem;
            margin-right: 0.5rem;
        }

        .stock-status {
            margin-bottom: 1rem;
            padding: 0.5rem;
            border-radius: 0.5rem;
            display: inline-block;
        }

        .in-stock {
            color: var(--success);
            background: #d1fae5;
        }

        .out-of-stock {
            color: var(--danger);
            background: #fee2e2;
        }

        .section-title {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .product-description {
            margin-bottom: 1.5rem;
        }

        .product-description p {
            color: var(--gray);
            line-height: 1.8;
        }

        .product-specs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
            background: var(--light);
            padding: 1rem;
            border-radius: 0.5rem;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .spec-icon {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 0.5rem;
            color: var(--primary);
        }

        .spec-label {
            font-size: 0.75rem;
            color: var(--gray);
        }

        .spec-value {
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .rating-reviews {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            margin: 2rem 0;
            overflow: hidden;
        }

        .rating-reviews-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 1.5rem;
            color: white;
        }

        .rating-reviews-header h2 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .rating-summary {
            display: flex;
            align-items: center;
            gap: 2rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid var(--gray-light);
            flex-wrap: wrap;
        }

        .rating-average {
            text-align: center;
            min-width: 120px;
        }

        .rating-average-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
        }

        .rating-average-stars {
            margin: 0.5rem 0;
        }

        .rating-average-stars i {
            color: var(--secondary);
            font-size: 1rem;
        }

        .rating-average-count {
            font-size: 0.8rem;
            color: var(--gray);
        }

        .rating-bars {
            flex: 1;
            min-width: 200px;
        }

        .rating-bar-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .rating-bar-label {
            width: 35px;
            font-size: 0.8rem;
            color: var(--gray);
        }

        .rating-bar-bg {
            flex: 1;
            height: 8px;
            background: var(--gray-light);
            border-radius: 4px;
            overflow: hidden;
        }

        .rating-bar-fill {
            height: 100%;
            background: var(--secondary);
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        .rating-bar-percent {
            width: 45px;
            font-size: 0.8rem;
            color: var(--gray);
        }

        .filter-rating {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid var(--gray-light);
        }

        .filter-rating-label {
            font-weight: 500;
            font-size: 0.85rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-rating-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .filter-star-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.35rem 0.8rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 500;
            background: white;
            border: 1px solid var(--gray-light);
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .filter-star-btn:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary-dark);
        }

        .filter-star-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .filter-star-btn i {
            font-size: 0.7rem;
        }

        .filter-star-btn .star-count {
            margin-left: 0.2rem;
        }

        .reviews-list {
            padding: 1.5rem;
        }

        .review-item {
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-light);
            animation: fadeIn 0.3s ease;
        }

        .review-item:last-child {
            border-bottom: none;
        }

        .review-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .review-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            overflow: hidden;
        }

        .review-user-info {
            flex: 1;
        }

        .review-user-name {
            font-weight: 600;
            color: var(--dark);
        }

        .review-date {
            font-size: 0.75rem;
            color: var(--gray);
        }

        .review-stars {
            color: var(--secondary);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .review-comment {
            color: var(--gray);
            line-height: 1.6;
            margin-left: 3.5rem;
            padding: 0.5rem;
            background: var(--light);
            border-radius: 0.5rem;
        }

        .empty-reviews {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }

        .empty-reviews i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray-light);
        }

        .related-products {
            margin: 2rem 0;
        }

        .related-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .product-card {
            background: white;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .product-image-container {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: var(--light);
        }

        .product-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .product-card .product-info {
            padding: 1rem;
        }

        .product-brand {
            font-size: 0.75rem;
            color: var(--gray);
            margin-bottom: 0.25rem;
        }

        .product-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        footer {
            background: var(--dark);
            color: white;
            padding: 3rem 0 1rem;
            margin-top: 3rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-column h3 {
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: var(--gray);
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: white;
        }

        .copyright {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--gray);
            font-size: 0.8rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                padding: 1rem;
            }

            .product-gallery {
                position: static;
            }

            .navbar {
                flex-direction: column;
            }

            .nav-links {
                margin-left: 0;
                justify-content: center;
                flex-wrap: wrap;
            }

            .rating-summary {
                flex-direction: column;
                text-align: center;
            }

            .review-comment {
                margin-left: 0;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                justify-content: center;
            }

            .filter-rating {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        #notif-global {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            z-index: 9999;
            display: none;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>

<body>
    <header>
        <div class="navbar">
            <a href="../index.php" class="logo">
                <i class="fas fa-laptop-code"></i>
                <span>GadgetMart</span>
            </a>

            <div class="nav-links">
                <a href="../index.php">Beranda</a>
                <a href="../index.php?kategori=Laptop">Laptop</a>
                <a href="../index.php?kategori=HP">Smartphone</a>
                <a href="../index.php?kategori=Tablet">Tablet</a>
                <a href="../index.php?kategori=Aksesoris">Aksesoris</a>
            </div>

            <div class="user-actions">
                <?php if (isset($_SESSION['username'])): ?>
                    <?php if ($_SESSION['role'] == 'customer'): ?>
                        <a href="../cart/keranjang.php" class="btn-icon" title="Keranjang">
                            <i class="fas fa-shopping-cart"></i>
                            <?php
                            $user_id = $_SESSION['id_user'];
                            $cart_count = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM keranjang WHERE id_user = '$user_id'");
                            $count = mysqli_fetch_assoc($cart_count);
                            if ($count['total'] > 0): ?>
                                <span class="cart-count"><?= $count['total']; ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>

                    <div class="dropdown">
                        <button class="dropdown-toggle">
                            <div class="btn-icon" style="margin-right: 0; overflow: hidden; width: 2.2rem; height: 2.2rem;">
                                <img src="../gambar pp/<?= $foto_profil; ?>" alt="Profil"
                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            </div>
                            <span><?= $_SESSION['username']; ?></span>
                            <i class="fas fa-chevron-down" style="font-size: 0.8rem;"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="../user/profil.php">
                                <i class="fas fa-user"></i>
                                Profil Saya
                            </a>
                            <a href="../customer/pesanan_saya.php">
                                <i class="fas fa-shopping-bag"></i>
                                Pesanan Saya
                            </a>
                            <?php if ($_SESSION['role'] == 'customer'): ?>
                                <a href="../cart/keranjang.php">
                                    <i class="fas fa-shopping-cart"></i>
                                    Keranjang Belanja
                                </a>
                                <a href="wishlist_saya.php">
                                    <i class="fas fa-heart"></i>
                                    Wishlist Saya
                                </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="../user/logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                Keluar
                            </a>
                        </div>
                    </div>

                <?php else: ?>
                    <a href="../user/login.php" class="btn btn-outline">Masuk</a>
                    <a href="../user/register.php" class="btn btn-primary">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="product-detail">
            <div class="product-gallery">
                <div class="main-image">
                    <img src="<?= getImagePath($row['gambar'], $row['id_produk']); ?>" alt="<?= $row['nama_produk']; ?>"
                        id="mainImage">
                </div>
                <div class="thumbnail-container" id="thumbnailContainer">
                    <?php foreach ($list_gambar as $index => $gambar): ?>
                        <div class="thumbnail <?= $index == 0 ? 'active' : ''; ?>"
                            data-img="<?= getImagePath($gambar['gambar'], $row['id_produk']); ?>"
                            onclick="changeThumbnail(this, '<?= getImagePath($gambar['gambar'], $row['id_produk']); ?>')">
                            <img src="<?= getImagePath($gambar['gambar'], $row['id_produk']); ?>"
                                alt="Thumbnail <?= $index + 1 ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="product-info">
                <h1 class="product-title"><?= htmlspecialchars($row['nama_produk']); ?></h1>

                <div class="product-meta">
                    <span class="product-brand"><?= $row['merek'] ?? 'Premium Brand'; ?></span>
                    <span class="product-sku">SKU: <?= $row['id_produk']; ?></span>
                </div>

                <div class="product-rating">
                    <div class="rating-stars">
                        <?php
                        $fullStars = floor($rating);
                        $hasHalfStar = ($rating - $fullStars) >= 0.5;

                        for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= $fullStars): ?>
                                <i class="fas fa-star"></i>
                            <?php elseif ($hasHalfStar && $i == $fullStars + 1): ?>
                                <i class="fas fa-star-half-alt"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-count"><?= number_format($rating, 1); ?> dari 5 (<?= $total_rating ?>
                        ulasan)</span>
                </div>

                <div class="price-container">
                    <?php if ($discount > 0): ?>
                        <span class="discount-badge">-<?= $discount; ?>%</span>
                        <span class="original-price">Rp <?= number_format($row['harga_asli'], 0, ',', '.'); ?></span>
                    <?php endif; ?>
                    <span class="current-price">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></span>
                </div>

                <div class="stock-status <?= $row['stok'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                    <i class="fas fa-<?= $row['stok'] > 0 ? 'check-circle' : 'times-circle'; ?>"></i>
                    <?= $row['stok'] > 0 ? 'Stok Tersedia (' . $row['stok'] . ' unit)' : 'Stok Habis'; ?>
                </div>

                <div class="product-description">
                    <h3 class="section-title">Deskripsi Produk</h3>
                    <p><?= nl2br(htmlspecialchars($row['deskripsi'])); ?></p>
                </div>

                <div class="product-specs">
                    <h3 class="section-title" style="grid-column: 1 / -1; margin-bottom: 1rem;">Spesifikasi</h3>

                    <?php if ($row['kategori'] === 'Laptop' || $row['kategori'] === 'HP'): ?>
                        <?php if (!empty($row['prosesor'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-microchip spec-icon"></i>
                                <div>
                                    <div class="spec-label">Prosesor</div>
                                    <div class="spec-value"><?= htmlspecialchars($row['prosesor']); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($row['ram'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-memory spec-icon"></i>
                                <div>
                                    <div class="spec-label">RAM</div>
                                    <div class="spec-value"><?= htmlspecialchars($row['ram']); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($row['penyimpanan'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-hdd spec-icon"></i>
                                <div>
                                    <div class="spec-label">Penyimpanan</div>
                                    <div class="spec-value"><?= htmlspecialchars($row['penyimpanan']); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php elseif ($row['kategori'] === 'Tablet'): ?>
                        <?php if (!empty($row['layar'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-expand spec-icon"></i>
                                <div>
                                    <div class="spec-label">Ukuran Layar</div>
                                    <div class="spec-value"><?= htmlspecialchars($row['layar']); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($row['baterai'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-battery-full spec-icon"></i>
                                <div>
                                    <div class="spec-label">Kapasitas Baterai</div>
                                    <div class="spec-value"><?= htmlspecialchars($row['baterai']); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php elseif ($row['kategori'] === 'Aksesoris'): ?>
                        <?php if (!empty($row['berat'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-weight spec-icon"></i>
                                <div>
                                    <div class="spec-label">Berat</div>
                                    <div class="spec-value"><?= htmlspecialchars($row['berat']); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="action-buttons">
                    <?php if ($row['stok'] > 0): ?>
                        <?php if (isset($_SESSION['username']) && $_SESSION['role'] == 'customer'): ?>
                            <a href="../cart/tambah_keranjang.php?id=<?= $row['id_produk']; ?>" class="btn btn-primary">
                                <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                            </a>
                        <?php else: ?>
                            <a href="../user/login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Login untuk Beli
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;">
                            <i class="fas fa-times-circle"></i> Stok Habis
                        </button>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['id_user']) && $_SESSION['role'] == 'customer'): ?>
                        <button onclick="toggleWishlist(<?= $id_produk; ?>, this)"
                            class="btn <?= $in_wishlist ? 'btn-primary' : 'btn-outline'; ?>">
                            <i class="<?= $in_wishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                            <span><?= $in_wishlist ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist'; ?></span>
                        </button>
                    <?php else: ?>
                        <a href="../user/login.php" class="btn btn-outline">
                            <i class="far fa-heart"></i> Tambah ke Wishlist
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <div id="notif-global"></div>

        <section class="rating-reviews">
            <div class="rating-reviews-header">
                <h2>
                    <i class="fas fa-star"></i>
                    Ulasan & Rating Produk
                </h2>
                <p>Lihat apa kata pembeli tentang produk ini</p>
            </div>

            <?php if ($total_rating > 0): ?>
                <div class="rating-summary">
                    <div class="rating-average">
                        <div class="rating-average-number"><?= number_format($rating, 1); ?></div>
                        <div class="rating-average-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= floor($rating)): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif ($i == ceil($rating) && ($rating - floor($rating)) >= 0.5): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <div class="rating-average-count"><?= $total_rating ?> ulasan</div>
                    </div>
                    <div class="rating-bars">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <?php $percent = $total_rating > 0 ? ($rating_counts[$i] / $total_rating * 100) : 0; ?>
                            <div class="rating-bar-item">
                                <div class="rating-bar-label"><?= $i ?> ★</div>
                                <div class="rating-bar-bg">
                                    <div class="rating-bar-fill" style="width: <?= $percent ?>%;"></div>
                                </div>
                                <div class="rating-bar-percent"><?= round($percent) ?>%</div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="filter-rating">
                <div class="filter-rating-label">
                    <i class="fas fa-filter"></i> Filter:
                </div>
                <div class="filter-rating-buttons">
                    <a href="?id=<?= $id_produk ?>&rating=0"
                        class="filter-star-btn <?= $filter_rating == 0 ? 'active' : '' ?>">
                        <i class="fas fa-star"></i> Semua
                        <span class="star-count">(<?= $total_rating ?>)</span>
                    </a>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <a href="?id=<?= $id_produk ?>&rating=<?= $i ?>"
                            class="filter-star-btn <?= $filter_rating == $i ? 'active' : '' ?>">
                            <?php for ($j = 1; $j <= $i; $j++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                            <span class="star-count">(<?= $rating_counts[$i] ?>)</span>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="reviews-list">
                <?php if (count($ulasan_filtered) > 0): ?>
                    <?php foreach ($ulasan_filtered as $ulasan): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-avatar">
                                    <?php if (!empty($ulasan['foto_user']) && file_exists("../gambar pp/" . $ulasan['foto_user'])): ?>
                                        <img src="../gambar pp/<?= $ulasan['foto_user']; ?>"
                                            alt="<?= htmlspecialchars($ulasan['nama_user']); ?>"
                                            style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle" style="font-size: 2rem;"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="review-user-info">
                                    <div class="review-user-name"><?= htmlspecialchars($ulasan['nama_user']); ?></div>
                                    <div class="review-date"><?= date('d M Y H:i', strtotime($ulasan['tanggal'])); ?></div>
                                </div>
                            </div>
                            <div class="review-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $ulasan['rating']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <?php if (!empty($ulasan['ulasan'])): ?>
                                <div class="review-comment">
                                    <i class="fas fa-quote-left" style="color: var(--gray); margin-right: 0.5rem;"></i>
                                    <?= nl2br(htmlspecialchars($ulasan['ulasan'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php elseif ($filter_rating > 0): ?>
                    <div class="empty-reviews">
                        <i class="fas fa-filter"></i>
                        <h3>Tidak Ada Ulasan</h3>
                        <p>Belum ada ulasan dengan rating <?= $filter_rating ?> ★</p>
                        <a href="?id=<?= $id_produk ?>&rating=0" class="btn btn-outline btn-sm">
                            <i class="fas fa-undo"></i> Lihat Semua Ulasan
                        </a>
                    </div>
                <?php else: ?>
                    <div class="empty-reviews">
                        <i class="fas fa-comment-dots"></i>
                        <h3>Belum Ada Ulasan</h3>
                        <p>Jadilah yang pertama memberikan ulasan untuk produk ini!</p>
                        <?php if (isset($_SESSION['username']) && $_SESSION['role'] == 'customer'): ?>
                            <a href="../customer/pesanan_saya.php" class="btn btn-outline" style="margin-top: 1rem;">
                                <i class="fas fa-star"></i> Beri Rating
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <?php if (mysqli_num_rows($produk_terkait) > 0): ?>
            <section class="related-products">
                <h2 class="related-title">Produk Terkait</h2>
                <div class="related-grid">
                    <?php while ($related = mysqli_fetch_assoc($produk_terkait)): ?>
                        <div class="product-card">
                            <div class="product-image-container">
                                <img src="<?= getImagePath($related['gambar'], $related['id_produk']); ?>"
                                    alt="<?= htmlspecialchars($related['nama_produk']); ?>" class="product-image">
                            </div>
                            <div class="product-info">
                                <div class="product-brand"><?= $related['merek'] ?? 'Premium Brand'; ?></div>
                                <h3 class="product-name"><?= htmlspecialchars($related['nama_produk']); ?></h3>
                                <div class="product-price">
                                    Rp <?= number_format($related['harga'], 0, ',', '.'); ?>
                                </div>
                                <a href="detail.php?id=<?= $related['id_produk']; ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Tentang GadgetMart</h3>
                    <p style="color: var(--gray); margin-bottom: 1.5rem; line-height: 1.6;">
                        GadgetMart.id adalah toko online terpercaya untuk laptop, smartphone, dan aksesoris gadget
                        dengan garansi resmi dan harga terbaik.
                    </p>
                </div>

                <div class="footer-column">
                    <h3>Belanja</h3>
                    <ul class="footer-links">
                        <li><a href="../index.php?kategori=Laptop"><i class="fas fa-chevron-right"></i> Laptop</a></li>
                        <li><a href="../index.php?kategori=HP"><i class="fas fa-chevron-right"></i> Smartphone</a></li>
                        <li><a href="../index.php?kategori=Tablet"><i class="fas fa-chevron-right"></i> Tablet</a></li>
                        <li><a href="../index.php?kategori=Aksesoris"><i class="fas fa-chevron-right"></i> Aksesoris</a>
                        </li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Kontak</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-phone-alt"></i> (021) 1234-5678</a></li>
                        <li><a href="#"><i class="fas fa-envelope"></i> cs@gadgetmart.id</a></li>
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> Jakarta, Indonesia</a></li>
                        <li><a href="#"><i class="fas fa-clock"></i> Buka 09:00 - 21:00 WIB</a></li>
                    </ul>
                </div>
            </div>

            <div class="copyright">
                &copy; <?= date('Y'); ?> GadgetMart.id - All Rights Reserved
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const dropdownToggle = document.querySelector('.dropdown-toggle');
        const dropdown = document.querySelector('.dropdown');

        if (dropdownToggle) {
            dropdownToggle.addEventListener('click', function (e) {
                e.stopPropagation();
                dropdown.classList.toggle('open');
            });
        }

        document.addEventListener('click', function (e) {
            if (dropdown && !dropdown.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });

        // ========== MULTIPLE IMAGE THUMBNAIL ==========
        const thumbnails = document.querySelectorAll('.thumbnail');
        const mainImage = document.getElementById('mainImage');

        if (thumbnails.length > 0 && mainImage) {
            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    // Ambil gambar dari dalam thumbnail
                    const imgElement = this.querySelector('img');
                    if (imgElement && imgElement.src) {
                        mainImage.src = imgElement.src;
                    }
                    
                    // Update active class
                    thumbnails.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        }
        // ==============================================
    });

    function changeImage(element) {
        const mainImage = document.getElementById('mainImage');
        if (mainImage) {
            if (element.tagName === 'IMG') {
                mainImage.src = element.src;
            } else if (element.querySelector('img')) {
                mainImage.src = element.querySelector('img').src;
            }
        }
        
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.remove('active');
        });
        
        if (element.classList) {
            element.classList.add('active');
        } else if (element.parentElement) {
            element.parentElement.classList.add('active');
        }
    }

    function showNotif(pesan, warna = '#10b981') {
        let n = document.getElementById('notif-global');
        if (!n) {
            const div = document.createElement('div');
            div.id = 'notif-global';
            div.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;display:none;padding:12px 20px;border-radius:10px;color:white;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
            document.body.appendChild(div);
            n = div;
        }

        let icon = '';
        if (warna === '#10b981') icon = 'fa-check-circle';
        else if (warna === '#ef4444') icon = 'fa-exclamation-circle';
        else icon = 'fa-info-circle';

        n.style.background = warna;
        n.innerHTML = '<i class="fas ' + icon + '"></i> ' + pesan;
        n.style.display = 'block';

        setTimeout(() => {
            n.style.display = 'none';
        }, 2000);
    }

    function toggleWishlist(productId, buttonElement) {
        const btn = buttonElement;
        const originalHtml = btn.innerHTML;

        btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i>';
        btn.disabled = true;

        let path = 'toggle_wishlist.php';

        fetch(path, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id_produk=' + productId
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (data.action === 'added') {
                        btn.innerHTML = '<i class="fas fa-heart"></i> Hapus dari Wishlist';
                        btn.classList.remove('btn-outline');
                        btn.classList.add('btn-primary');
                        showNotif('❤️ Ditambahkan ke wishlist', '#10b981');
                    } else {
                        btn.innerHTML = '<i class="far fa-heart"></i> Tambah ke Wishlist';
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-outline');
                        showNotif('💔 Dihapus dari wishlist', '#f59e0b');
                    }
                    btn.disabled = false;
                } else if (data.status === 'login') {
                    showNotif('Silakan login dulu!', '#f59e0b');
                    setTimeout(() => {
                        window.location.href = '../user/login.php';
                    }, 1500);
                } else {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    showNotif(data.message || 'Terjadi kesalahan!', '#ef4444');
                }
            })
            .catch(error => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                showNotif('Kesalahan koneksi!', '#ef4444');
            });
    }
</script>
</body>

</html>