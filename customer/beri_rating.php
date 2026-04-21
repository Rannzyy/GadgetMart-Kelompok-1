<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    echo "<script>alert('Akses hanya untuk customer!'); window.location='../../index.php';</script>";
    exit;
}

$id_user = $_SESSION['id_user'];
$id_transaksi = $_GET['id'] ?? null;

// Ambil data user termasuk foto profil
$user_query = mysqli_query($koneksi, "SELECT nama, foto FROM users WHERE id_user = '$id_user'");
$user_data = mysqli_fetch_assoc($user_query);
$nama_user = $user_data['nama'] ?? 'Customer';
$foto = $user_data['foto'] ?? 'default-avatar.jpg';

// Cek path foto
$foto_path = "../gambar pp/" . $foto;
if (!file_exists($foto_path) || empty($foto)) {
    $foto_display = "../gambar pp/default-avatar.jpg";
} else {
    $foto_display = $foto_path;
}

if (!$id_transaksi) {
    echo "<script>alert('Transaksi tidak ditemukan.'); window.location='pesanan_saya.php';</script>";
    exit;
}

// Cek apakah transaksi sudah dinilai
$cek = mysqli_query($koneksi, "SELECT * FROM rating WHERE id_user='$id_user' AND id_transaksi='$id_transaksi'");
if (mysqli_num_rows($cek) > 0) {
    echo "<script>alert('Kamu sudah memberi rating untuk transaksi ini!'); window.location='pesanan_saya.php';</script>";
    exit;
}

// Ambil daftar produk dalam transaksi
$produk = mysqli_query($koneksi, "
    SELECT dt.id_produk, p.nama_produk, p.gambar, p.harga
    FROM detail_transaksi dt
    JOIN produk p ON dt.id_produk = p.id_produk
    WHERE dt.id_transaksi = '$id_transaksi'
");

if (isset($_POST['submit'])) {
    foreach ($_POST['rating'] as $id_produk => $nilai_rating) {
        $komentar = mysqli_real_escape_string($koneksi, $_POST['komentar'][$id_produk] ?? '');
        mysqli_query($koneksi, "
            INSERT INTO rating (id_user, id_produk, id_transaksi, rating, ulasan, tanggal)
            VALUES ('$id_user', '$id_produk', '$id_transaksi', '$nilai_rating', '$komentar', NOW())
        ");
    }
    echo "<script>alert('Terima kasih atas rating kamu!'); window.location='pesanan_saya.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Rating | GadgetMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ===== VARIABLES & RESET ===== */
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");

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
            --star-active: #fbbf24;
            --star-inactive: #cbd5e1;
            --border-radius: 1rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9eef5 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        @keyframes shine {
            0% { opacity: 0.6; }
            50% { opacity: 1; }
            100% { opacity: 0.6; }
        }

        /* ===== HEADER & NAVIGATION ===== */
        header {
            background: white;
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .logo i {
            font-size: 1.8rem;
            animation: float 3s ease-in-out infinite;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links a::after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: var(--transition);
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }

        /* ===== DROPDOWN PROFILE ===== */
        .dropdown {
            position: relative;
        }

        .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: transparent;
            border: 1px solid var(--gray-light);
            border-radius: 2rem;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: var(--transition);
            background: white;
        }

        .dropdown-toggle:hover {
            background: var(--light);
            border-color: var(--primary-light);
        }

        .profile-img {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            width: 220px;
            background: white;
            border-radius: 0.75rem;
            box-shadow: var(--shadow-lg);
            padding: 0.5rem;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
            margin-top: 0.5rem;
        }

        .dropdown.open .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: var(--dark);
            transition: var(--transition);
        }

        .dropdown-item:hover {
            background: var(--light);
            color: var(--primary);
        }

        .dropdown-divider {
            height: 1px;
            background: var(--gray-light);
            margin: 0.5rem 0;
        }

        /* ===== BUTTONS ===== */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }

        /* ===== MAIN LAYOUT ===== */
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1.5rem;
            animation: fadeIn 0.5s ease-out;
        }

        /* ===== BREADCRUMB ===== */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 0;
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            background: transparent;
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            transition: var(--transition);
        }

        .breadcrumb a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* ===== HEADER SECTION ===== */
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header h1 i {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: pulse 2s ease-in-out infinite;
        }

        .subtitle {
            color: var(--gray);
            font-size: 1rem;
        }

        /* ===== RATING CARD ===== */
        .rating-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            transition: var(--transition);
        }

        .rating-card:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        /* ===== CARD HEADER ===== */
        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 1.25rem 1.5rem;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .card-header-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .card-header-left i {
            font-size: 1.3rem;
        }

        .badge-success {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 500;
        }

        /* ===== TRANSACTION INFO ===== */
        .transaction-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid var(--gray-light);
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .info-item i {
            color: var(--primary);
            width: 20px;
        }

        .info-item strong {
            color: var(--dark);
        }

        /* ===== PRODUCT ITEMS ===== */
        .product-item {
            display: flex;
            gap: 1.5rem;
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-light);
            transition: all 0.3s ease;
            animation: slideIn 0.4s ease-out forwards;
            opacity: 0;
        }

        .product-item:nth-child(1) { animation-delay: 0.1s; }
        .product-item:nth-child(2) { animation-delay: 0.2s; }
        .product-item:nth-child(3) { animation-delay: 0.3s; }
        .product-item:nth-child(4) { animation-delay: 0.4s; }

        .product-item:hover {
            background: #fafcff;
            transform: translateX(5px);
        }

        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }

        .product-image:hover {
            transform: scale(1.05);
        }

        .product-details {
            flex: 1;
        }

        .product-name {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        /* ===== RATING STARS ===== */
        .rating-section {
            margin-bottom: 1rem;
        }

        .rating-title {
            font-size: 0.85rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .stars {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-start;
            gap: 0.25rem;
        }

        .stars input {
            display: none;
        }

        .stars label {
            font-size: 2rem;
            color: var(--star-inactive);
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 0 0.1rem;
        }

        .stars label:hover,
        .stars label:hover ~ label,
        .stars input:checked ~ label {
            color: var(--star-active);
            transform: scale(1.1);
        }

        .stars input:checked + label {
            animation: pulse 0.3s;
        }

        .rating-value-text {
            font-size: 0.8rem;
            color: var(--gray);
            margin-top: 0.25rem;
            display: inline-block;
        }

        /* ===== COMMENT SECTION ===== */
        .comment-section {
            margin-top: 1rem;
        }

        .comment-label {
            display: block;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            color: var(--gray);
            font-weight: 500;
        }

        .comment-textarea {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.75rem;
            border: 1px solid var(--gray-light);
            font-family: inherit;
            resize: vertical;
            transition: var(--transition);
            background: #fafcff;
            font-size: 0.9rem;
        }

        .comment-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: white;
        }

        /* ===== FORM ACTIONS ===== */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-top: 1px solid var(--gray-light);
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--gray-light);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            .nav-links {
                gap: 1rem;
                flex-wrap: wrap;
                justify-content: center;
            }

            .container {
                padding: 0 1rem;
                margin: 1rem auto;
            }

            .header h1 {
                font-size: 1.8rem;
            }

            .product-item {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .product-image {
                width: 120px;
                height: 120px;
            }

            .stars {
                justify-content: center;
            }

            .stars label {
                font-size: 1.8rem;
            }

            .form-actions {
                justify-content: center;
            }

            .transaction-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .dropdown-toggle span {
                display: none;
            }
        }
    </style>
</head>

<body>
    <!-- Header & Navbar -->
    <header>
        <nav class="navbar">
            <a href="../index.php" class="logo">
                <i class="fas fa-laptop-code"></i>
                <span>GadgetMart</span>
            </a>

            <div class="nav-links">
                <a href="../index.php">
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
                <a href="pesanan_saya.php" class="active">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Pesanan Saya</span>
                </a>
                <a href="../cart/keranjang.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Keranjang</span>
                </a>
            </div>

            <div class="dropdown">
                <button class="dropdown-toggle">
                    <img src="<?= $foto_display; ?>" alt="Profil" class="profile-img">
                    <span><?= htmlspecialchars($nama_user); ?></span>
                    <i class="fas fa-chevron-down" style="font-size: 0.8rem;"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="profil.php" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>Profil Saya</span>
                    </a>
                    <a href="pesanan_saya.php" class="dropdown-item">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Pesanan Saya</span>
                    </a>
                    <a href="../produk/wishlist_saya.php" class="dropdown-item">
                        <i class="fas fa-heart"></i>
                        <span>Wishlist</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="../user/logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="../index.php"><i class="fas fa-home"></i> Beranda</a>
            <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
            <a href="pesanan_saya.php">Pesanan Saya</a>
            <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
            <span>Beri Rating</span>
        </div>

        <div class="header">
            <h1>
                <i class="fas fa-star"></i>
                Beri Rating Produk
            </h1>
            <p class="subtitle">Bagikan pengalaman Anda dengan produk yang telah dibeli</p>
        </div>

        <div class="rating-card">
            <div class="card-header">
                <div class="card-header-left">
                    <i class="fas fa-receipt"></i>
                    <span>Transaksi #<?= $id_transaksi ?></span>
                </div>
                <div class="badge-success">
                    <i class="fas fa-check-circle"></i> Selesai
                </div>
            </div>

            <div class="transaction-info">
                <div class="info-item">
                    <i class="fas fa-calendar-alt"></i>
                    <strong>Tanggal Transaksi:</strong>
                    <?php
                    $tgl_query = mysqli_query($koneksi, "SELECT tanggal_pesan FROM transaksi WHERE id_transaksi='$id_transaksi'");
                    $tgl_data = mysqli_fetch_assoc($tgl_query);
                    echo $tgl_data ? date('d M Y, H:i', strtotime($tgl_data['tanggal_pesan'])) : '-';
                    ?>
                </div>
                <div class="info-item">
                    <i class="fas fa-truck"></i>
                    <strong>Status:</strong>
                    <span style="color: var(--success);">Pesanan Selesai</span>
                </div>
            </div>

            <form method="POST">
                <?php
                if (mysqli_num_rows($produk) > 0):
                    while ($row = mysqli_fetch_assoc($produk)):
                        ?>
                        <div class="product-item">
                            <img src="../gambar/<?= htmlspecialchars($row['gambar']); ?>" 
                                 alt="<?= htmlspecialchars($row['nama_produk']); ?>" 
                                 class="product-image">
                            <div class="product-details">
                                <div class="product-name"><?= htmlspecialchars($row['nama_produk']); ?></div>

                                <div class="rating-section">
                                    <div class="rating-title">
                                        <i class="fas fa-star" style="color: var(--secondary);"></i> Rating Anda
                                    </div>
                                    <div class="stars">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" name="rating[<?= $row['id_produk']; ?>]"
                                                id="star<?= $i; ?>_<?= $row['id_produk']; ?>" 
                                                value="<?= $i; ?>" required>
                                            <label for="star<?= $i; ?>_<?= $row['id_produk']; ?>">★</label>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-value-text" id="rating-text-<?= $row['id_produk']; ?>">
                                        Klik bintang untuk memberi rating
                                    </span>
                                </div>

                                <div class="comment-section">
                                    <label class="comment-label">
                                        <i class="fas fa-pen"></i> Ulasan (Opsional)
                                    </label>
                                    <textarea name="komentar[<?= $row['id_produk']; ?>]" 
                                              class="comment-textarea"
                                              placeholder="Ceritakan pengalaman Anda menggunakan produk ini..."></textarea>
                                </div>
                            </div>
                        </div>
                        <?php
                    endwhile;
                else:
                    ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <p>Tidak ada produk ditemukan untuk transaksi ini.</p>
                    </div>
                <?php endif; ?>

                <?php if (mysqli_num_rows($produk) > 0): ?>
                    <div class="form-actions">
                        <button type="submit" name="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Kirim Rating
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
        // Toggle dropdown
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

            // Update rating text when stars are clicked
            const starGroups = document.querySelectorAll('.stars');
            starGroups.forEach(group => {
                const radios = group.querySelectorAll('input[type="radio"]');
                const productId = radios.length > 0 ? radios[0].name.match(/\d+/)[0] : null;
                const ratingText = document.getElementById(`rating-text-${productId}`);
                
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        const ratingValue = this.value;
                        const ratingLabels = {
                            '1': '★ Sangat Buruk',
                            '2': '★★ Buruk',
                            '3': '★★★ Cukup',
                            '4': '★★★★ Baik',
                            '5': '★★★★★ Sangat Baik'
                        };
                        if (ratingText) {
                            ratingText.innerHTML = ratingLabels[ratingValue] || 'Klik bintang untuk memberi rating';
                            ratingText.style.color = '#f59e0b';
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>