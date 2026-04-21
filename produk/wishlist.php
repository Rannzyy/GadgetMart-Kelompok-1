<?php
session_start();
include '../koneksi.php';

// Pastikan user login dulu
if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Kamu harus login dulu!'); window.location = '../login.php';</script>";
    exit();
}

// Cek apakah id_produk dikirim
if (!isset($_GET['id_produk'])) {
    echo "<script>alert('ID produk tidak ditemukan!'); window.location = '../index.php';</script>";
    exit();
}

$id_user = $_SESSION['id_user'];
$id_produk = $_GET['id_produk'];

// Cek apakah produk benar-benar ada
$cekProduk = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk = '$id_produk'");
if (mysqli_num_rows($cekProduk) == 0) {
    echo "<script>alert('Produk tidak ditemukan!'); window.location = '../index.php';</script>";
    exit();
}

// Cek apakah produk sudah ada di wishlist user
$cekWishlist = mysqli_query($koneksi, "SELECT * FROM wishlist WHERE id_user = '$id_user' AND id_produk = '$id_produk'");
if (mysqli_num_rows($cekWishlist) > 0) {
    echo "<script>alert('Produk ini sudah ada di wishlist kamu!'); window.history.back();</script>";
    exit();
}

// Tambahkan ke wishlist
$insert = mysqli_query($koneksi, "INSERT INTO wishlist (id_user, id_produk) VALUES ('$id_user', '$id_produk')");

if ($insert) {
    echo "<script>alert('Produk berhasil ditambahkan ke wishlist!'); window.history.back();</script>";
} else {
    echo "<script>alert('Gagal menambahkan ke wishlist!'); window.history.back();</script>";
}
?>
