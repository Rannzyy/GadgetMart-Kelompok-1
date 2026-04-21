<?php
session_start();
include '../koneksi.php';

// Cek login dulu
if (!isset($_SESSION['id_user'])) {
    echo "<script>
        alert('Silakan login untuk melihat keranjang!');
        window.location='../user/login.php';
    </script>";
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil foto profil user
$foto_profil = 'default.jpg';
$getUser = mysqli_query($koneksi, "SELECT foto FROM users WHERE id_user = '$id_user'");
$user = mysqli_fetch_assoc($getUser);
if ($user && !empty($user['foto'])) {
    $foto_profil = $user['foto'];
}

// Ambil data keranjang user
$query = mysqli_query($koneksi, "
    SELECT k.*, p.nama_produk, p.harga, p.gambar, p.id_produk
    FROM keranjang k 
    JOIN produk p ON k.id_produk = p.id_produk 
    WHERE k.id_user = '$id_user'
");

$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja | GadgetMart.id</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/cart.css">
    <style>
        /* Tambahan style untuk profile img */
        .profile-img-wrapper {
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--primary-light);
        }
        .profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
        .username {
            font-weight: 500;
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Dropdown menu styles */
        .dropdown {
            position: relative;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            min-width: 200px;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            margin-top: 0.5rem;
        }
        .dropdown.open .dropdown-menu {
            display: block;
        }
        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #1f2937;
            text-decoration: none;
            transition: all 0.3s;
        }
        .dropdown-menu a:hover {
            background: #f3f4f6;
        }
        .dropdown-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 0.5rem 0;
        }
        
        /* Cart styles */
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }
        
        @media (max-width: 768px) {
            .product-img {
                width: 60px;
                height: 60px;
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
                <a href="../index.php?kategori=HP">Smartphone</a>
                <a href="../index.php?kategori=Tablet">Tablet</a>
                <a href="../index.php?kategori=Aksesoris">Aksesoris</a>
            </div>
            
            <div class="user-actions">
                <a href="keranjang.php" class="btn-icon" title="Keranjang">
                    <i class="fas fa-shopping-cart"></i>
                    <?php 
                    $cart_count = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM keranjang WHERE id_user = '$id_user'");
                    $count = mysqli_fetch_assoc($cart_count);
                    if ($count['total'] > 0): ?>
                        <span class="cart-count"><?= $count['total']; ?></span>
                    <?php endif; ?>
                </a>
                <div class="dropdown">
                    <button class="dropdown-toggle">
                        <div class="profile-img-wrapper">
                            <img src="../gambar pp/<?= $foto_profil; ?>" alt="Profil" class="profile-img" onerror="this.src='../gambar pp/default.jpg'">
                        </div>
                        <span class="username"><?= htmlspecialchars($_SESSION['username']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="../user/profil.php">
                            <i class="fas fa-user"></i> Profil Saya
                        </a>
                        <a href="../customer/pesanan_saya.php">
                            <i class="fas fa-shopping-bag"></i> Pesanan Saya
                        </a>
                        <a href="keranjang.php">
                            <i class="fas fa-shopping-cart"></i> Keranjang Belanja
                        </a>
                        <a href="../produk/wishlist_saya.php">
                            <i class="fas fa-heart"></i> Wishlist Saya
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="../user/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Keluar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Cart Content -->
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Keranjang Belanja</h1>
            <a href="../index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Kembali ke Toko
            </a>
        </div>
        
        <div class="cart-card">
            <?php if (mysqli_num_rows($query) > 0): ?>
                <!-- Desktop Table View -->
                <div class="cart-header">
                    <div>Produk</div>
                    <div>Harga</div>
                    <div>Jumlah</div>
                    <div>Subtotal</div>
                    <div>Aksi</div>
                </div>
                
                <div class="cart-items">
                    <?php while ($row = mysqli_fetch_assoc($query)): 
                        $subtotal = $row['harga'] * $row['jumlah'];
                        $total += $subtotal;
                        
                        // Cari path gambar yang benar
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
                                    } else {
                                        $image_path = "https://via.placeholder.com/80x80?text=No+Image";
                                    }
                                } else {
                                    $image_path = "https://via.placeholder.com/80x80?text=No+Image";
                                }
                            }
                        }
                        ?>
                        <div class="cart-item">
                            <div class="product-info">
                                <img src="<?= $image_path; ?>" 
                                     class="product-img" 
                                     alt="<?= htmlspecialchars($row['nama_produk']); ?>"
                                     onerror="this.src='https://via.placeholder.com/80x80?text=No+Image'">
                                <span class="product-name"><?= htmlspecialchars($row['nama_produk']); ?></span>
                            </div>
                            <div class="product-price">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></div>
                            <div class="quantity-control">
                                <form action="update_keranjang.php" method="POST" style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="hidden" name="id_keranjang" value="<?= $row['id_keranjang']; ?>">
                                    <button type="submit" name="action" value="kurang" class="quantity-btn">-</button>
                                    <input type="number" name="jumlah" value="<?= $row['jumlah']; ?>" class="quantity-input" min="1" readonly>
                                    <button type="submit" name="action" value="tambah" class="quantity-btn">+</button>
                                </form>
                            </div>
                            <div class="product-subtotal">Rp <?= number_format($subtotal, 0, ',', '.'); ?></div>
                            <div class="delete-action">
                                <a href="hapus_keranjang.php?id=<?= $row['id_keranjang']; ?>" class="delete-btn" onclick="return confirm('Hapus item ini dari keranjang?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="cart-footer">
                    <div class="total-section">
                        <div class="total-label">Total Belanja</div>
                        <div class="total-amount">Rp <?= number_format($total, 0, ',', '.'); ?></div>
                    </div>
                    <div style="text-align: right; margin-top: 1.5rem;">
                        <a href="checkout.php" class="checkout-btn">
                            <i class="fas fa-credit-card"></i> Checkout Sekarang
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Keranjang Belanja Kosong</h3>
                    <p>Anda belum menambahkan produk ke keranjang belanja</p>
                    <a href="../index.php" class="btn-outline" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 2rem;">
                        <i class="fas fa-arrow-left"></i> Lanjutkan Belanja
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Dropdown functionality
        document.addEventListener("DOMContentLoaded", function() {
            const dropdowns = document.querySelectorAll('.dropdown');
            
            dropdowns.forEach(dropdown => {
                const toggle = dropdown.querySelector('.dropdown-toggle');
                
                if (toggle) {
                    toggle.addEventListener('click', function(e) {
                        e.stopPropagation();
                        dropdown.classList.toggle('open');
                    });
                }
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function() {
                dropdowns.forEach(dropdown => {
                    dropdown.classList.remove('open');
                });
            });
        });
    </script>
</body>
</html>