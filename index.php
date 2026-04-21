<?php
session_start();
include 'koneksi.php';

$foto_profil = 'default.jpg';

if (isset($_SESSION['id_user'])) {
    $id_user = $_SESSION['id_user'];
    $getUser = mysqli_query($koneksi, "SELECT foto FROM users WHERE id_user = '$id_user'");
    $user = mysqli_fetch_assoc($getUser);
    if ($user && !empty($user['foto'])) {
        $foto_profil = $user['foto'];
    }
}

// Cek kelengkapan profil
if (isset($_SESSION['id_user']) && $_SESSION['role'] == 'customer') {
    include 'cek_profil.php';
}

// Get cart count
$cart_count = 0;
if (isset($_SESSION['id_user']) && $_SESSION['role'] == 'customer') {
    $user_id = $_SESSION['id_user'];
    $cart_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM keranjang WHERE id_user = '$user_id'");
    $cart_data = mysqli_fetch_assoc($cart_query);
    $cart_count = $cart_data['total'];
}

// Proses pencarian
$search_keyword = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$search_filter = '';

if (!empty($search_keyword)) {
    $search_filter = " AND (p.nama_produk LIKE '%$search_keyword%' OR p.deskripsi LIKE '%$search_keyword%')";
}

// Fungsi untuk mendapatkan path gambar produk
function getProductImage($id_produk, $gambar)
{
    if (empty($gambar)) {
        return "gambar/default.jpg";
    }

    // Cek di folder baru (multiple upload)
    $new_path = "gambar/produk/" . $id_produk . "/" . $gambar;
    if (file_exists($new_path)) {
        return $new_path;
    }

    // Cek di folder lama
    $old_path = "gambar/" . $gambar;
    if (file_exists($old_path)) {
        return $old_path;
    }

    return "gambar/default.jpg";
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GadgetMart.id | Beli Laptop & HP Premium</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Wishlist Button Styles */
        .wishlist-btn {
            background: transparent;
            border: 1px solid var(--gray-light);
            color: var(--gray);
            transition: var(--transition);
        }

        .wishlist-btn.active {
            background: #fee2e2;
            border-color: var(--danger);
            color: var(--danger);
        }

        .wishlist-btn:hover {
            background: #fee2e2;
            border-color: var(--danger);
            color: var(--danger);
            transform: scale(1.05);
        }

        .wishlist-count {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            color: var(--gray);
            margin-top: 5px;
        }

        .product-wishlist {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }

        .product-wishlist .wishlist-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            box-shadow: var(--shadow-sm);
        }

        .product-stats {
            display: flex;
            gap: 12px;
            margin-top: 8px;
            font-size: 0.75rem;
            color: var(--gray);
        }

        /* Search Bar Styles */
        .search-section {
            max-width: 1200px;
            margin: 1rem auto;
            padding: 0 1rem;
        }

        .search-container {
            background: white;
            border-radius: 50px;
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            transition: var(--transition);
        }

        .search-container:focus-within {
            box-shadow: 0 0 0 3px var(--primary-light), var(--shadow-md);
        }

        .search-icon {
            color: var(--gray);
            font-size: 1.2rem;
            margin-right: 0.75rem;
        }

        .search-input {
            flex: 1;
            border: none;
            outline: none;
            padding: 0.75rem 0;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            background: transparent;
        }

        .search-clear {
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 1rem;
            padding: 0.5rem;
            transition: var(--transition);
            display: none;
        }

        .search-clear:hover {
            color: var(--danger);
        }

        .search-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 40px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            margin-left: 0.5rem;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .search-result-info {
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background: white;
            border-radius: 10px;
            display: inline-block;
            box-shadow: var(--shadow-sm);
        }

        /* Profile Reminder Banner */
        .profile-reminder {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-left: 4px solid var(--primary);
            padding: 16px 24px;
            margin: 16px auto 0 auto;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            max-width: 1400px;
            width: calc(100% - 32px);
        }

        .profile-reminder-content {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .profile-reminder-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.4rem;
        }

        .profile-reminder-text {
            color: #1e40af;
        }

        .profile-reminder-text strong {
            font-weight: 700;
            font-size: 1rem;
        }

        .profile-reminder-text p {
            margin: 4px 0 0;
            font-size: 0.85rem;
            color: #3b82f6;
        }

        .profile-reminder-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 10px 28px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .profile-reminder-btn:hover {
            background: linear-gradient(135deg, var(--primary-dark), #1e40af);
            transform: translateY(-2px);
        }

        /* Modal Popup */
        .profile-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .profile-modal.active {
            display: flex;
        }

        .profile-modal-content {
            background: white;
            border-radius: 24px;
            max-width: 450px;
            width: 90%;
            padding: 2rem;
            text-align: center;
            animation: modalFadeIn 0.3s ease;
        }

        .profile-modal-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
        }

        .profile-modal h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .profile-modal p {
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .profile-modal-list {
            text-align: left;
            background: #eff6ff;
            border-radius: 12px;
            padding: 1rem;
            margin: 1rem 0;
            list-style: none;
        }

        .profile-modal-list li {
            padding: 0.5rem 0;
            color: #1e40af;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #dbeafe;
        }

        .profile-modal-list li:last-child {
            border-bottom: none;
        }

        .profile-modal-list li i {
            width: 20px;
            color: var(--primary);
        }

        .profile-modal-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .profile-modal-buttons .btn {
            flex: 1;
            justify-content: center;
        }

        @keyframes modalFadeIn {
            from {
                transform: translateY(-30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .profile-reminder {
                flex-direction: column;
                text-align: center;
                margin: 12px 16px 0 16px;
                width: auto;
            }

            .profile-reminder-content {
                flex-direction: column;
            }

            .profile-modal-buttons {
                flex-direction: column;
            }

            .search-container {
                flex-wrap: wrap;
                border-radius: 20px;
            }

            .search-btn {
                width: 100%;
                margin-top: 0.5rem;
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <!-- ==================== HEADER ==================== -->
    <header>
        <div class="navbar">
            <a href="index.php" class="logo">
                <i class="fas fa-laptop-code"></i>
                <span>GadgetMart</span>
            </a>

            <div class="nav-links">
                <a href="index.php">Beranda</a>
                <a href="index.php?kategori=Laptop">Laptop</a>
                <a href="index.php?kategori=HP">Smartphone</a>
                <a href="index.php?kategori=Tablet">Tablet</a>
                <a href="index.php?kategori=Aksesoris">Aksesoris</a>
            </div>

            <div class="user-actions">
                <?php if (isset($_SESSION['username'])): ?>
                    <?php if ($_SESSION['role'] == 'customer'): ?>
                        <a href="cart/keranjang.php" class="btn-icon cart-icon" title="Keranjang">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-count"><?= $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>

                    <div class="dropdown">
                        <button class="dropdown-toggle">
                            <div class="profile-img-wrapper">
                                <img src="gambar pp/<?= $foto_profil; ?>" alt="Profil" class="profile-img">
                            </div>
                            <span class="username"><?= htmlspecialchars($_SESSION['username']); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="user/profil.php"><i class="fas fa-user"></i> Profil Saya</a>
                            <a href="customer/pesanan_saya.php"><i class="fas fa-shopping-bag"></i> Pesanan Saya</a>
                            <?php if ($_SESSION['role'] == 'customer'): ?>
                                <a href="cart/keranjang.php"><i class="fas fa-shopping-cart"></i> Keranjang Belanja</a>
                                <a href="produk/wishlist_saya.php"><i class="fas fa-heart"></i> Wishlist Saya</a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="user/logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Keluar</a>
                        </div>
                    </div>

                <?php else: ?>
                    <a href="user/login.php" class="btn btn-outline">Masuk</a>
                    <a href="user/login.php?form=register" class="btn btn-primary">Daftar</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Profile Reminder Banner -->
        <?php if (isset($_SESSION['id_user']) && $_SESSION['role'] == 'customer' && isset($_SESSION['profil_lengkap']) && !$_SESSION['profil_lengkap']): ?>
            <div class="profile-reminder" id="profileReminder">
                <div class="profile-reminder-content">
                    <div class="profile-reminder-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="profile-reminder-text">
                        <strong>📋 Lengkapi Profil Anda!</strong>
                        <p>Data yang belum lengkap: <?= implode(', ', $_SESSION['missing_fields']); ?></p>
                    </div>
                </div>
                <a href="user/profil.php" class="profile-reminder-btn">
                    <i class="fas fa-edit"></i> Lengkapi Sekarang
                </a>
            </div>
        <?php endif; ?>
    </header>

    <!-- ==================== SEARCH SECTION ==================== -->
    <div class="search-section">
        <div class="search-container">
            <div class="search-icon">
                <i class="fas fa-search"></i>
            </div>
            <form action="index.php" method="GET" style="flex: 1; display: flex; align-items: center;">
                <input type="text" name="search" class="search-input"
                    placeholder="Cari laptop, smartphone, tablet, aksesoris..."
                    value="<?= htmlspecialchars($search_keyword); ?>" id="searchInput">
                <button type="button" class="search-clear" id="clearSearch"
                    style="display: <?= !empty($search_keyword) ? 'block' : 'none'; ?>">
                    <i class="fas fa-times-circle"></i>
                </button>
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Cari
                </button>
            </form>
        </div>
        <?php if (!empty($search_keyword)): ?>
            <div class="search-result-info">
                <i class="fas fa-search"></i> Hasil pencarian untuk:
                <strong>"<?= htmlspecialchars($search_keyword); ?>"</strong>
                <a href="index.php" style="margin-left: 1rem; color: var(--primary);">
                    <i class="fas fa-times"></i> Hapus filter
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- ==================== HERO SECTION ==================== -->
    <section class="hero">
        <div class="hero-content">
            <h1>Temukan Gadget Terbaik dengan Harga Terjangkau</h1>
            <p>Beli laptop, smartphone, dan aksesoris original dengan garansi resmi. Gratis ongkir & promo spesial
                setiap hari!</p>
            <a href="#products" class="btn btn-primary hero-btn">
                <i class="fas fa-shopping-bag"></i> Belanja Sekarang
            </a>
        </div>
    </section>

    <!-- ==================== CATEGORY SECTION ==================== -->
    <section class="categories">
        <h2 class="section-title">Kategori Populer</h2>
        <div class="category-list">
            <a href="index.php?kategori=Laptop" class="category-card">
                <div class="category-icon"><i class="fas fa-laptop"></i></div>
                <span>Laptop</span>
            </a>
            <a href="index.php?kategori=HP" class="category-card">
                <div class="category-icon"><i class="fas fa-mobile-alt"></i></div>
                <span>Smartphone</span>
            </a>
            <a href="index.php?kategori=Tablet" class="category-card">
                <div class="category-icon"><i class="fas fa-tablet-alt"></i></div>
                <span>Tablet</span>
            </a>
            <a href="index.php?kategori=Aksesoris&subkategori=Headset" class="category-card">
                <div class="category-icon"><i class="fas fa-headphones"></i></div>
                <span>Headset</span>
            </a>
            <a href="index.php?kategori=Aksesoris&subkategori=Keyboard" class="category-card">
                <div class="category-icon"><i class="fas fa-keyboard"></i></div>
                <span>Keyboard</span>
            </a>
            <a href="index.php?kategori=Aksesoris&subkategori=Powerbank" class="category-card">
                <div class="category-icon"><i class="fas fa-battery-full"></i></div>
                <span>Power Bank</span>
            </a>
        </div>
    </section>

    <!-- ==================== PRODUCT SECTION ==================== -->
    <section class="products" id="products">
        <h2 class="section-title">Produk Terbaru</h2>
        <div class="product-grid">
            <?php
            $where = "p.status = 'aktif'";
            if (isset($_GET['kategori']) && $_GET['kategori'] != '') {
                $kategori = mysqli_real_escape_string($koneksi, $_GET['kategori']);
                $where .= " AND p.kategori = '$kategori'";
            }
            if (isset($_GET['subkategori']) && $_GET['subkategori'] != '') {
                $subkategori = mysqli_real_escape_string($koneksi, $_GET['subkategori']);
                $where .= " AND (p.nama_produk LIKE '%$subkategori%' OR p.deskripsi LIKE '%$subkategori%')";
            }

            if (!empty($search_keyword)) {
                $where .= $search_filter;
            }

            $produk = mysqli_query($koneksi, "
            SELECT p.*, 
                COALESCE(SUM(dt.jumlah), 0) as total_terjual,
                COALESCE(AVG(r.rating), 0) as avg_rating,
                COUNT(r.id_rating) as total_rating,
                (SELECT COUNT(*) FROM wishlist w WHERE w.id_produk = p.id_produk) as wishlist_count
            FROM produk p
            LEFT JOIN detail_transaksi dt ON p.id_produk = dt.id_produk
            LEFT JOIN transaksi t ON dt.id_transaksi = t.id_transaksi AND t.status = 'selesai'
            LEFT JOIN rating r ON p.id_produk = r.id_produk
            WHERE $where
            GROUP BY p.id_produk
            ORDER BY total_terjual DESC, avg_rating DESC, p.id_produk DESC
            LIMIT 12
        ");

            $user_wishlist = [];
            if (isset($_SESSION['id_user']) && $_SESSION['role'] == 'customer') {
                $user_id = $_SESSION['id_user'];
                $wishlist_query = mysqli_query($koneksi, "SELECT id_produk FROM wishlist WHERE id_user = '$user_id'");
                while ($w = mysqli_fetch_assoc($wishlist_query)) {
                    $user_wishlist[] = $w['id_produk'];
                }
            }

            $rank = 1;
            if (mysqli_num_rows($produk) > 0):
                while ($row = mysqli_fetch_assoc($produk)):
                    $discount = isset($row['harga_asli']) && $row['harga_asli'] > $row['harga']
                        ? round((($row['harga_asli'] - $row['harga']) / $row['harga_asli'] * 100)) : 0;
                    $is_best_seller = $rank <= 3;
                    $is_in_wishlist = in_array($row['id_produk'], $user_wishlist);
                    ?>
                    <div class="product-card" data-product-id="<?= $row['id_produk']; ?>">
                        <div class="product-wishlist">
                            <?php if (isset($_SESSION['username']) && $_SESSION['role'] == 'customer'): ?>
                                <button onclick="toggleWishlist(<?= $row['id_produk']; ?>, this)"
                                    class="wishlist-btn <?= $is_in_wishlist ? 'active' : ''; ?>">
                                    <i class="<?= $is_in_wishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                                </button>
                            <?php else: ?>
                                <a href="user/login.php" class="wishlist-btn" title="Login untuk menambah wishlist">
                                    <i class="far fa-heart"></i>
                                </a>
                            <?php endif; ?>
                        </div>

                        <?php if ($is_best_seller): ?>
                            <span class="product-badge best-seller"><i class="fas fa-fire"></i> Top <?= $rank; ?></span>
                        <?php endif; ?>
                        <?php if ($discount > 0): ?>
                            <span class="product-badge discount">-<?= $discount; ?>%</span>
                        <?php endif; ?>

                        <div class="product-image-container">
                            <?php
                            // Cek lokasi gambar
                            $gambar_path_lama = "gambar/" . $row['gambar'];
                            $gambar_path_baru = "gambar/produk/" . $row['id_produk'] . "/" . $row['gambar'];

                            if (!empty($row['gambar']) && file_exists($gambar_path_baru)) {
                                $img_src = $gambar_path_baru;
                            } elseif (!empty($row['gambar']) && file_exists($gambar_path_lama)) {
                                $img_src = $gambar_path_lama;
                            } else {
                                $img_src = "gambar/default.jpg";
                            }
                            ?>
                            <img src="<?= $img_src; ?>" alt="<?= $row['nama_produk']; ?>" class="product-image">
                        </div>

                        <div class="product-info">
                            <div class="product-brand"><?= htmlspecialchars($row['merek'] ?? 'Premium Brand'); ?></div>
                            <h3 class="product-name"><?= htmlspecialchars($row['nama_produk']); ?></h3>

                            <div class="product-+price">
                                Rp <?= number_format($row['harga'], 0, ',', '.'); ?>
                                <?php if (isset($row['harga_asli']) && $row['harga_asli'] > $row['harga']): ?>
                                    <span class="product-original-price">Rp
                                        <?= number_format($row['harga_asli'], 0, ',', '.'); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="product-stats">
                                <?php if ($row['total_terjual'] > 0): ?>
                                    <span><i class="fas fa-shopping-bag"></i> Terjual <?= $row['total_terjual']; ?></span>
                                <?php endif; ?>
                                <span class="wishlist-count" id="wishlist-count-<?= $row['id_produk']; ?>">
                                    <i class="fas fa-heart"></i> <?= $row['wishlist_count']; ?> orang
                                </span>
                            </div>

                            <div class="product-rating">
                                <div class="rating-stars">
                                    <?php
                                    $rating = round($row['avg_rating'], 1);
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
                                <span class="rating-count">(<?= $row['total_rating']; ?>)</span>
                            </div>

                            <div class="product-stock <?= $row['stok'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                <i class="fas fa-<?= $row['stok'] > 0 ? 'check-circle' : 'times-circle'; ?>"></i>
                                <?= $row['stok'] > 0 ? 'Stok Tersedia' : 'Stok Habis'; ?>
                            </div>

                            <div class="product-actions">
                                <?php if ($row['stok'] > 0): ?>
                                    <?php if (isset($_SESSION['username']) && $_SESSION['role'] == 'customer'): ?>
                                        <a href="cart/tambah_keranjang.php?id=<?= $row['id_produk']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-cart-plus"></i> Keranjang
                                        </a>
                                    <?php else: ?>
                                        <a href="user/login.php" class="btn btn-primary btn-sm"><i class="fas fa-sign-in-alt"></i>
                                            Login</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-disabled" disabled><i class="fas fa-bell"></i> Habis</button>
                                <?php endif; ?>
                                <a href="produk/detail.php?id=<?= $row['id_produk']; ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php $rank++; endwhile; else: ?>
                <div style="text-align: center; grid-column: 1/-1; padding: 3rem;">
                    <i class="fas fa-search" style="font-size: 3rem; color: var(--gray); margin-bottom: 1rem;"></i>
                    <h3>Produk tidak ditemukan</h3>
                    <p>Tidak ada produk dengan kata kunci "<?= htmlspecialchars($search_keyword); ?>"</p>
                    <a href="index.php" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-arrow-left"></i> Lihat Semua Produk
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ==================== FOOTER ==================== -->
    <footer>
        <div class="footer-content">
            <div class="footer-column">
                <h3>Tentang GadgetMart</h3>
                <p>GadgetMart.id adalah toko online terpercaya untuk laptop, smartphone, dan aksesoris gadget dengan
                    garansi resmi dan harga terbaik.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <div class="footer-column">
                <h3>Belanja</h3>
                <ul class="footer-links">
                    <li><a href="index.php?kategori=Laptop"><i class="fas fa-chevron-right"></i> Laptop</a></li>
                    <li><a href="index.php?kategori=HP"><i class="fas fa-chevron-right"></i> Smartphone</a></li>
                    <li><a href="index.php?kategori=Tablet"><i class="fas fa-chevron-right"></i> Tablet</a></li>
                    <li><a href="index.php?kategori=Aksesoris&subkategori=Headset"><i class="fas fa-chevron-right"></i>
                            Headset</a></li>
                    <li><a href="index.php?kategori=Aksesoris&subkategori=Keyboard"><i class="fas fa-chevron-right"></i>
                            Keyboard</a></li>
                    <li><a href="index.php?kategori=Aksesoris&subkategori=Powerbank"><i
                                class="fas fa-chevron-right"></i> Powerbank</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>Kontak Kami</h3>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-phone-alt"></i> (021) 1234-5678</a></li>
                    <li><a href="#"><i class="fas fa-envelope"></i> cs@gadgetmart.id</a></li>
                    <li><a href="#"><i class="fas fa-map-marker-alt"></i> Malang, Indonesia</a></li>
                    <li><a href="#"><i class="fas fa-clock"></i> 07:00 - 20:00 WIB</a></li>
                    <li><a href="tentang_kami.php"><i class="fas fa-users"></i> Tentang Kami</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            &copy; <?= date('Y'); ?> GadgetMart.id - All Rights Reserved
        </div>
    </footer>

    <!-- ==================== MODAL POPUP ==================== -->
    <div class="profile-modal" id="profileModal">
        <div class="profile-modal-content">
            <div class="profile-modal-icon">
                <i class="fas fa-smile-wink"></i>
            </div>
            <h3>Selamat Datang! 👋</h3>
            <p>Sebelum mulai berbelanja, lengkapi data diri Anda terlebih dahulu:</p>
            <ul class="profile-modal-list">
                <?php if (isset($_SESSION['missing_fields'])): ?>
                    <?php foreach ($_SESSION['missing_fields'] as $field): ?>
                        <li><i class="fas fa-times-circle"></i> <?= $field; ?> belum diisi</li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            <div class="profile-modal-buttons">
                <a href="user/profil.php" class="btn btn-primary"><i class="fas fa-edit"></i> Lengkapi Profil</a>
                <button onclick="closeModal()" class="btn btn-outline"><i class="fas fa-shopping-bag"></i> Belanja
                    Dulu</button>
            </div>
        </div>
    </div>

    <!-- ==================== JAVASCRIPT ==================== -->
    <script>
        function closeModal() {
            document.getElementById('profileModal').classList.remove('active');
        }

        document.addEventListener("click", function (e) {
            const dropdown = document.querySelector(".dropdown");
            if (!dropdown) return;
            const toggle = dropdown.querySelector(".dropdown-toggle");
            const menu = dropdown.querySelector(".dropdown-menu");
            if (toggle && toggle.contains(e.target)) {
                dropdown.classList.toggle("open");
            } else if (menu && !menu.contains(e.target)) {
                dropdown.classList.remove("open");
            }
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.product-card, .category-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(el);
        });

        function showNotif(pesan, warna = '#10b981') {
            let n = document.getElementById('notif-global');
            if (!n) {
                const div = document.createElement('div');
                div.id = 'notif-global';
                div.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;display:none;padding:12px 20px;border-radius:10px;color:white;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
                document.body.appendChild(div);
                n = div;
            }
            let icon = warna === '#10b981' ? 'fa-check-circle' : (warna === '#ef4444' ? 'fa-exclamation-circle' : 'fa-info-circle');
            n.style.background = warna;
            n.innerHTML = '<i class="fas ' + icon + '"></i> ' + pesan;
            n.style.display = 'block';
            setTimeout(() => n.style.display = 'none', 2000);
        }

        function toggleWishlist(productId, buttonElement) {
            const btn = buttonElement;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i>';
            btn.disabled = true;
            let path = window.location.pathname.includes('/produk/') ? 'toggle_wishlist.php' : 'produk/toggle_wishlist.php';
            fetch(path, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_produk=' + productId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (data.action === 'added') {
                            btn.innerHTML = '<i class="fas fa-heart"></i>';
                            btn.classList.remove('btn-outline');
                            btn.classList.add('btn-primary');
                            showNotif('❤️ Ditambahkan ke wishlist', '#10b981');
                        } else {
                            btn.innerHTML = '<i class="far fa-heart"></i>';
                            btn.classList.remove('btn-primary');
                            btn.classList.add('btn-outline');
                            showNotif('💔 Dihapus dari wishlist', '#f59e0b');
                        }
                        btn.disabled = false;
                    } else if (data.status === 'login') {
                        showNotif('Silakan login dulu!', '#f59e0b');
                        setTimeout(() => window.location.href = 'user/login.php', 1500);
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

        // Search Clear Button
        const searchInput = document.getElementById('searchInput');
        const clearButton = document.getElementById('clearSearch');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                if (this.value.length > 0) {
                    clearButton.style.display = 'block';
                } else {
                    clearButton.style.display = 'none';
                }
            });
        }

        if (clearButton) {
            clearButton.addEventListener('click', function () {
                searchInput.value = '';
                clearButton.style.display = 'none';
                searchInput.focus();
            });
        }

        <?php if (isset($_SESSION['id_user']) && $_SESSION['role'] == 'customer' && isset($_SESSION['profil_lengkap']) && !$_SESSION['profil_lengkap']): ?>
            const modal = document.getElementById('profileModal');
            if (modal) {
                modal.classList.add('active');
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) modal.classList.remove('active');
                });
            }
        <?php endif; ?>
    </script>

</body>

</html>