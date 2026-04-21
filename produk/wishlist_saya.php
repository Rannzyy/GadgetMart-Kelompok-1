<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$query = mysqli_query($koneksi, "SELECT p.*, w.id_wishlist FROM wishlist w JOIN produk p ON w.id_produk = p.id_produk WHERE w.id_user = '$id_user'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist Saya | GadgetMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #93c5fd;
            --danger: #ef4444;
            --danger-dark: #dc2626;
            --success: #10b981;
            --dark: #1f2937;
            --light: #f9fafb;
            --gray: #6b7280;
            --gray-light: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
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
            min-height: 100vh;
        }

        /* Header */
        header {
            background: white;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .logo i {
            font-size: 1.8rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            border: none;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: var(--danger-dark);
            transform: translateY(-2px);
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        main.container {
            padding: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid var(--primary);
            display: inline-block;
        }

        /* Wishlist Grid */
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .wishlist-item {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }

        .wishlist-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .wishlist-image {
            width: 100%;
            height: 220px;
            overflow: hidden;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .wishlist-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .wishlist-item:hover .wishlist-image img {
            transform: scale(1.05);
        }

        .wishlist-content {
            padding: 1.2rem;
        }

        .wishlist-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .wishlist-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .wishlist-actions {
            display: flex;
            gap: 0.75rem;
        }

        .wishlist-actions .btn {
            flex: 1;
            justify-content: center;
            font-size: 0.85rem;
            padding: 0.6rem 0.8rem;
        }

        /* Empty Wishlist */
        .empty-wishlist {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow);
        }

        .empty-icon {
            font-size: 5rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .empty-text {
            font-size: 1.3rem;
            color: var(--gray);
            margin-bottom: 1.5rem;
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            margin-top: 3rem;
            padding: 3rem 0 1rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-column h3 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: white;
        }

        .footer-column p {
            color: #9ca3af;
            line-height: 1.6;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: #9ca3af;
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-links a:hover {
            color: var(--primary);
            transform: translateX(5px);
        }

        .copyright {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #374151;
            color: #9ca3af;
            font-size: 0.85rem;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
            }
            main.container {
                padding: 1rem;
            }
            .wishlist-grid {
                grid-template-columns: 1fr;
            }
            .wishlist-actions {
                flex-direction: column;
            }
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .footer-links a {
                justify-content: center;
            }
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
            <div>
                <a href="../index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </header>

    <main class="container">
        <h1 class="page-title">Wishlist Saya</h1>
        
        <?php if (mysqli_num_rows($query) > 0): ?>
            <div class="wishlist-grid">
                <?php while ($row = mysqli_fetch_assoc($query)): 
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
                                    $image_path = "https://via.placeholder.com/300x220?text=No+Image";
                                }
                            } else {
                                $image_path = "https://via.placeholder.com/300x220?text=No+Image";
                            }
                        }
                    }
                ?>
                    <div class="wishlist-item">
                        <div class="wishlist-image">
                            <img src="<?= $image_path; ?>" 
                                 alt="<?= htmlspecialchars($row['nama_produk']); ?>"
                                 onerror="this.src='https://via.placeholder.com/300x220?text=No+Image'">
                        </div>
                        <div class="wishlist-content">
                            <h3 class="wishlist-title"><?= htmlspecialchars($row['nama_produk']); ?></h3>
                            <div class="wishlist-price">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></div>
                            <div class="wishlist-actions">
                                <a href="../produk/detail.php?id=<?= $row['id_produk']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </a>
                                <a href="hapus_wishlist.php?id=<?= $row['id_wishlist']; ?>" class="btn btn-danger" onclick="return confirm('Hapus produk ini dari wishlist?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-wishlist">
                <div class="empty-icon">
                    <i class="far fa-heart"></i>
                </div>
                <h3 class="empty-text">Wishlist Anda Kosong</h3>
                <p style="color: var(--gray); margin-bottom: 1.5rem;">Belum ada produk yang ditambahkan ke wishlist</p>
                <a href="../index.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Mulai Belanja
                </a>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Tentang GadgetMart</h3>
                    <p style="color: var(--gray); margin-bottom: 1.5rem; line-height: 1.6;">
                        GadgetMart.id adalah toko online terpercaya untuk laptop, smartphone, dan aksesoris gadget dengan garansi resmi dan harga terbaik.
                    </p>
                </div>
                
                <div class="footer-column">
                    <h3>Belanja</h3>
                    <ul class="footer-links">
                        <li><a href="../index.php?kategori=Laptop"><i class="fas fa-chevron-right"></i> Laptop</a></li>
                        <li><a href="../index.php?kategori=HP"><i class="fas fa-chevron-right"></i> Smartphone</a></li>
                        <li><a href="../index.php?kategori=Tablet"><i class="fas fa-chevron-right"></i> Tablet</a></li>
                        <li><a href="../index.php?kategori=Aksesoris"><i class="fas fa-chevron-right"></i> Aksesoris</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Kontak</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-phone-alt"></i> (021) 1234-5678</a></li>
                        <li><a href="#"><i class="fas fa-envelope"></i> cs@gadgetmart.id</a></li>
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> Jakarta, Indonesia</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                &copy; <?= date('Y'); ?> GadgetMart.id - All Rights Reserved
            </div>
        </div>
    </footer>
</body>
</html>