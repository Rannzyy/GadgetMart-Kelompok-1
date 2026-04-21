<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Login dulu dong!'); window.location='../user/login.php';</script>";
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil data user dari database
$user_query = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'");
$user_data = mysqli_fetch_assoc($user_query);

// ========== VALIDASI KELENGKAPAN PROFIL ==========
$required_fields = [
    'no_hp' => 'Nomor HP',
    'jenis_kelamin' => 'Jenis Kelamin',
    'alamat' => 'Alamat'
];

$missing_fields = [];
foreach ($required_fields as $field => $label) {
    if (empty($user_data[$field])) {
        $missing_fields[] = $label;
    }
}

// Jika ada field yang kosong, redirect ke edit profil
if (!empty($missing_fields)) {
    $field_list = implode(', ', $missing_fields);
    echo "<script>
        alert('⚠️ Data profil Anda belum lengkap!\\n\\nField yang harus diisi: $field_list\\n\\nSilakan lengkapi profil Anda terlebih dahulu sebelum checkout.');
        window.location='../user/edit_profil.php?redirect=checkout';
    </script>";
    exit;
}
// ========== END VALIDASI ==========

// Ambil data keranjang
$keranjang = mysqli_query($koneksi, "
    SELECT k.*, p.nama_produk, p.harga, p.stok, p.gambar, p.id_produk 
    FROM keranjang k 
    JOIN produk p ON k.id_produk = p.id_produk 
    WHERE k.id_user = '$id_user'
");

$total_harga = 0;
$items = [];
while ($row = mysqli_fetch_assoc($keranjang)) {
    $subtotal = $row['harga'] * $row['jumlah'];
    $total_harga += $subtotal;
    
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
                    $image_path = "https://via.placeholder.com/60x60?text=No+Image";
                }
            } else {
                $image_path = "https://via.placeholder.com/60x60?text=No+Image";
            }
        }
    }
    
    $row['image_path'] = $image_path;
    $items[] = $row;

    if ($row['stok'] < $row['jumlah']) {
        echo "<script>alert('Stok produk \"{$row['nama_produk']}\" tidak mencukupi!'); window.location='keranjang.php';</script>";
        exit;
    }
}

// Jika keranjang kosong
if (empty($items)) {
    echo "<script>alert('Keranjang belanja kosong!'); window.location='keranjang.php';</script>";
    exit;
}

// Proses checkout
if (isset($_POST['checkout'])) {
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);
    $metode = mysqli_real_escape_string($koneksi, $_POST['metode']);

    mysqli_query($koneksi, "INSERT INTO transaksi 
    (id_user, tanggal_pesan, total_harga, metode_pembayaran, status, catatan)
    VALUES 
    ('$id_user', NOW(), '$total_harga', '$metode', 'Menunggu', '$catatan')");

    $id_transaksi = mysqli_insert_id($koneksi);

    foreach ($items as $item) {
        $id_produk = $item['id_produk'];
        $jumlah = $item['jumlah'];
        $harga = $item['harga'];
        $subtotal = $harga * $jumlah;

        mysqli_query($koneksi, "INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, subtotal)
            VALUES ('$id_transaksi', '$id_produk', '$jumlah', '$subtotal')");

        mysqli_query($koneksi, "UPDATE produk SET stok = stok - $jumlah WHERE id_produk = '$id_produk'");
    }

    mysqli_query($koneksi, "DELETE FROM keranjang WHERE id_user = '$id_user'");

    echo "<script>
        alert('✅ Checkout berhasil! Pesanan Anda sedang diproses.');
        window.location='../customer/pesanan_saya.php';
    </script>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GadgetMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #93c5fd;
            --danger: #ef4444;
            --success: #28a745;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #94a3b8;
            --gray-light: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9eef5 100%);
            color: var(--dark);
            min-height: 100vh;
        }

        /* Header */
        header {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
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
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--light);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 5%;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid var(--primary);
            display: inline-block;
        }

        /* Checkout Grid */
        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        @media (max-width: 900px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
        }

        /* Card */
        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--primary-light);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-title i {
            color: var(--primary);
        }

        /* Info Group */
        .info-group {
            margin-bottom: 1rem;
        }

        .info-label {
            font-weight: 600;
            margin-bottom: 0.3rem;
            color: var(--gray);
            font-size: 0.85rem;
        }

        .info-value {
            padding: 0.75rem;
            background: var(--light);
            border-radius: 0.5rem;
            border-left: 3px solid var(--primary);
            font-weight: 500;
        }

        /* Form */
        .form-group {
            margin-bottom: 1.2rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        select, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-light);
            border-radius: 0.5rem;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }

        /* Product Item */
        .product-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .product-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 0.5rem;
            border: 1px solid var(--gray-light);
            background: white;
        }

        .product-details {
            flex: 1;
        }

        .product-name {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
        }

        .product-price {
            color: var(--primary);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .product-quantity {
            color: var(--gray);
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        .product-subtotal {
            font-weight: 700;
            color: var(--dark);
            font-size: 0.95rem;
            min-width: 100px;
            text-align: right;
        }

        /* Summary */
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
        }

        .total {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--primary);
            border-top: 2px solid var(--gray-light);
            padding-top: 1rem;
            margin-top: 0.5rem;
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            font-size: 0.9rem;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--gray);
            color: var(--gray);
        }

        .btn-outline:hover {
            background: var(--danger);
            border-color: var(--danger);
            color: white;
        }

        .button-group {
            display: flex;
            gap: 1rem;
        }

        /* Notification */
        .notification {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #28a745;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
            background: var(--dark);
            color: white;
        }

        @media (max-width: 768px) {
            .product-item {
                flex-wrap: wrap;
            }
            .product-image {
                width: 55px;
                height: 55px;
            }
            .product-subtotal {
                min-width: auto;
                text-align: left;
                margin-left: auto;
            }
            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-laptop-code"></i>
                <span>GadgetMart</span>
            </div>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?= htmlspecialchars($user_data['nama']); ?></span>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">🛒 Checkout Pesanan</h1>

        <div class="notification">
            <i class="fas fa-info-circle"></i>
            <div>Data penerima dan alamat pengiriman telah diambil dari profil Anda.</div>
        </div>

        <form method="POST">
            <div class="checkout-container">
                <!-- Kiri: Info Pengiriman -->
                <div>
                    <div class="card">
                        <h2 class="card-title">
                            <i class="fas fa-truck"></i> Informasi Pengiriman
                        </h2>

                        <div class="info-group">
                            <div class="info-label"><i class="fas fa-user"></i> Nama Penerima</div>
                            <div class="info-value"><?= htmlspecialchars($user_data['nama']); ?></div>
                        </div>

                        <div class="info-group">
                            <div class="info-label"><i class="fas fa-phone"></i> Nomor HP</div>
                            <div class="info-value"><?= htmlspecialchars($user_data['no_hp']); ?></div>
                        </div>

                        <div class="info-group">
                            <div class="info-label"><i class="fas fa-map-marker-alt"></i> Alamat Pengiriman</div>
                            <div class="info-value"><?= htmlspecialchars($user_data['alamat']); ?></div>
                        </div>

                        <div class="form-group">
                            <label for="catatan"><i class="fas fa-sticky-note"></i> Catatan (opsional)</label>
                            <textarea id="catatan" name="catatan" placeholder="Contoh: Tolong dibungkus rapat, pintu rumah warna biru..."></textarea>
                        </div>

                        <div class="form-group">
                            <label for="metode"><i class="fas fa-credit-card"></i> Metode Pembayaran</label>
                            <select id="metode" name="metode" required>
                                <option value="">-- Pilih Metode --</option>
                                <option value="COD">💰 COD (Bayar di Tempat)</option>
                                <option value="Transfer Bank">🏦 Transfer Bank</option>
                                <option value="E-Wallet">📱 E-Wallet (Dana, OVO, Gopay)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Detail Pesanan -->
                <div>
                    <div class="card">
                        <h2 class="card-title">
                            <i class="fas fa-shopping-cart"></i> Detail Pesanan
                        </h2>

                        <?php foreach ($items as $item): ?>
                        <div class="product-item">
                            <img src="<?= $item['image_path']; ?>" 
                                 class="product-image" 
                                 alt="<?= htmlspecialchars($item['nama_produk']); ?>"
                                 onerror="this.src='https://via.placeholder.com/70x70?text=No+Image'">
                            <div class="product-details">
                                <div class="product-name"><?= htmlspecialchars($item['nama_produk']); ?></div>
                                <div class="product-price">Rp <?= number_format($item['harga'], 0, ',', '.'); ?></div>
                                <div class="product-quantity"><i class="fas fa-box"></i> Jumlah: <?= $item['jumlah']; ?></div>
                            </div>
                            <div class="product-subtotal">
                                Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.'); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <div class="summary-item total">
                            <div>💰 Total Pembayaran</div>
                            <div>Rp <?= number_format($total_harga, 0, ',', '.'); ?></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="button-group">
                            <button type="submit" name="checkout" class="btn btn-primary">
                                <i class="fas fa-check-circle"></i> Konfirmasi & Bayar
                            </button>
                            <a href="keranjang.php" class="btn btn-outline">
                                <i class="fas fa-times-circle"></i> Batalkan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <footer>
        <p>&copy; <?= date('Y'); ?> GadgetMart. All rights reserved.</p>
    </footer>
</body>
</html>