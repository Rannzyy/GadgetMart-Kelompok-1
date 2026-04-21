<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses hanya untuk admin!'); window.location='../index.php';</script>";
    exit;
}


$id = $_GET['id'] ?? null;
$aksi = $_GET['aksi'] ?? null;

if ($id && in_array($aksi, ['aktif', 'nonaktif'])) {
    $query = mysqli_query($koneksi, "UPDATE produk SET status='$aksi' WHERE id_produk='$id'");

    if ($query) {
        echo "<script>alert('Status produk berhasil diubah jadi $aksi'); window.location='data_produk.php';</script>";
    } else {
        echo "<script>alert('Gagal mengubah status produk'); window.location='data_produk.php';</script>";
    }
} else {
    echo "<script>alert('Permintaan tidak valid'); window.location='data_produk.php';</script>";
}
?>
