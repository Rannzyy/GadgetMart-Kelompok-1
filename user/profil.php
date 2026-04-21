<?php
session_start();
include '../koneksi.php';

// Check if user is logged in
if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../user/login.php';</script>";
    exit;
}

// Cek notifikasi sukses dari edit_profil.php
if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo "<script>alert('Profil berhasil diperbarui!');</script>";
}

$id_user = $_SESSION['id_user'];
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'");
$data = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna | GadgetMart.id</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/profil.css">
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
                <a href="../index.php?kategori=Smartphone">Smartphone</a>
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
                <a href="logout.php" class="btn btn-outline">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Profile Content -->
    <div class="profile-container">
        <div class="profile-header">
            <h1 class="profile-title">Profil Saya</h1>
        </div>

        <div class="profile-card">
            <div class="profile-avatar">
                <?php if (!empty($data['foto']) && file_exists('../gambar pp/' . $data['foto'])): ?>
                    <img src="../gambar pp/<?= $data['foto']; ?>" class="avatar" alt="Foto Profil">
                <?php else: ?>
                    <div class="avatar">
                        <?= strtoupper(substr($data['nama'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <h3><?= htmlspecialchars($data['nama']); ?></h3>
                <p>Member sejak: <?= date('d M Y', strtotime($data['tanggal_daftar'])); ?></p>
            </div>

            <div class="profile-info">
                <div class="info-group">
                    <div class="info-label">Username</div>
                    <div class="info-value"><?= htmlspecialchars($data['username']); ?></div>
                </div>

                <div class="info-group">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?= htmlspecialchars($data['email']); ?></div>
                </div>

                <div class="info-group">
                    <div class="info-label">Nomor HP</div>
                    <div class="info-value">
                        <?= !empty($data['no_hp']) ? htmlspecialchars($data['no_hp']) : 'Belum diisi'; ?></div>
                </div>

                <div class="info-group">
                    <div class="info-label">Jenis Kelamin</div>
                    <div class="info-value">
                        <?= !empty($data['jenis_kelamin']) ? ucfirst(htmlspecialchars($data['jenis_kelamin'])) : 'Belum diisi'; ?>
                    </div>
                </div>

                <div class="info-group">
                    <div class="info-label">Alamat</div>
                    <div class="info-value">
                        <?= !empty($data['alamat']) ? htmlspecialchars($data['alamat']) : 'Belum diisi'; ?></div>
                </div>

                <div class="info-group">
                    <div class="info-label">Status</div>
                    <div class="info-value"><?= ucfirst($data['role']); ?></div>
                </div>
            </div>
        </div>

        <div class="profile-actions">
            <a href="edit_profil.php" class="btn btn-primary">
                <i class="fas fa-user-edit"></i> Edit Profil
            </a>
            <a href="ubah_password.php" class="btn btn-outline">
                <i class="fas fa-key"></i> Ubah Password
            </a>
        </div>
    </div>

    <script>
        // Dropdown functionality for mobile
        document.addEventListener("DOMContentLoaded", function () {
            const dropdowns = document.querySelectorAll('.dropdown');

            dropdowns.forEach(dropdown => {
                const toggle = dropdown.querySelector('.dropdown-toggle');

                toggle.addEventListener('click', function () {
                    dropdown.classList.toggle('open');
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function (e) {
                dropdowns.forEach(dropdown => {
                    if (!dropdown.contains(e.target)) {
                        dropdown.classList.remove('open');
                    }
                });
            });
        });
    </script>
</body>

</html>