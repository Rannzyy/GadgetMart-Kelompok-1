<?php
session_start();
include '../koneksi.php';

// Pastikan user login
if (!isset($_SESSION['id_user'])) {
    echo "<script>
        alert('Silakan login dulu untuk membeli!');
        window.location='../user/login.php';
    </script>";
    exit;
}

$id_user = $_SESSION['id_user'];
$id_produk = $_GET['id'] ?? 0;

// Cek apakah produk sudah ada di keranjang
$cek = mysqli_query($koneksi, "SELECT * FROM keranjang WHERE id_user='$id_user' AND id_produk='$id_produk'");
if (mysqli_num_rows($cek) > 0) {
    // Kalau udah ada, tambahkan jumlah
    mysqli_query($koneksi, "UPDATE keranjang SET jumlah = jumlah + 1 WHERE id_user='$id_user' AND id_produk='$id_produk'");
} else {
    // Kalau belum ada, insert baru
    mysqli_query($koneksi, "INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES ('$id_user', '$id_produk', 1)");
}

echo "<script>
    alert('Produk ditambahkan ke keranjang!');
    window.location='../index.php';
</script>";
