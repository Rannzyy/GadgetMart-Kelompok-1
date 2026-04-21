<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses hanya untuk admin!'); window.location='../index.php';</script>";
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    // Hapus semua data terkait di detail_transaksi dulu
    mysqli_query($koneksi, "DELETE FROM detail_transaksi WHERE id_produk = '$id'");

    // Setelah itu, baru hapus produk
    $delete = mysqli_query($koneksi, "DELETE FROM produk WHERE id_produk = '$id'");

    if ($delete) {
        echo "<script>alert('Produk berhasil dihapus permanen'); window.location='data_produk.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus produk: " . mysqli_error($koneksi) . "'); window.location='data_produk.php';</script>";
    }
} else {
    echo "<script>alert('ID produk tidak ditemukan'); window.location='data_produk.php';</script>";
}
