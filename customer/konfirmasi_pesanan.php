<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    echo "<script>alert('Akses hanya untuk customer!'); window.location='../index.php';</script>";
    exit;
}

$id_user = $_SESSION['id_user'];
$id_transaksi = $_GET['id'];

// Pastikan transaksi punya user ini
$cek = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE id_transaksi='$id_transaksi' AND id_user='$id_user'");
$data = mysqli_fetch_assoc($cek);

if (!$data) {
    echo "<script>alert('Transaksi tidak ditemukan.'); window.location='pesanan_saya.php';</script>";
    exit;
}

// Kalau status Dikirim → update ke Selesai
if ($data['status'] === 'Dikirim') {
    mysqli_query($koneksi, "UPDATE transaksi SET status='Selesai' WHERE id_transaksi='$id_transaksi'");

    // Catat pemasukan (hindari double)
    $cek_pemasukan = mysqli_query($koneksi, "SELECT * FROM pemasukan WHERE id_transaksi='$id_transaksi'");
    if (mysqli_num_rows($cek_pemasukan) == 0) {
        $jumlah = $data['total_harga'];
        $tanggal = date('Y-m-d');
        mysqli_query($koneksi, "INSERT INTO pemasukan (id_transaksi, tanggal, jumlah, keterangan)
                                VALUES ('$id_transaksi', '$tanggal', '$jumlah', 'Konfirmasi pesanan selesai')");
    }

    echo "<script>alert('Pesanan berhasil dikonfirmasi!'); window.location='pesanan_saya.php';</script>";
    exit;
} else {
    echo "<script>alert('Pesanan ini tidak bisa dikonfirmasi.'); window.location='pesanan_saya.php';</script>";
    exit;
}
?>
