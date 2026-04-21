<?php
session_start();
include '../koneksi.php';

// Cek apakah user login sebagai customer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    echo "<script>alert('Akses hanya untuk customer!'); window.location='../index.php';</script>";
    exit;
}

$id_user = $_SESSION['id_user'];
$id_transaksi = $_GET['id'] ?? '';

// Validasi ID transaksi
if (empty($id_transaksi)) {
    echo "<script>alert('ID transaksi tidak ditemukan!'); window.location='pesanan_saya.php';</script>";
    exit;
}

// Cek apakah transaksi milik user dan belum dikirim
$cek = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE id_transaksi='$id_transaksi' AND id_user='$id_user'");
$data = mysqli_fetch_assoc($cek);

if (!$data) {
    echo "<script>alert('Transaksi tidak ditemukan!'); window.location='pesanan_saya.php';</script>";
    exit;
}

$status = $data['status'];

// Cuma bisa batal kalau masih Menunggu atau Diproses
if ($status === 'Menunggu' || $status === 'Diproses') {

    // Ambil produk dari detail_transaksi untuk kembalikan stok
    $detail = mysqli_query($koneksi, "SELECT * FROM detail_transaksi WHERE id_transaksi='$id_transaksi'");
    while ($row = mysqli_fetch_assoc($detail)) {
        $id_produk = $row['id_produk'];
        $jumlah = $row['jumlah'];

        // Tambahkan kembali stok ke produk
        mysqli_query($koneksi, "UPDATE produk SET stok = stok + $jumlah WHERE id_produk='$id_produk'");
    }

    // Hapus detail transaksi
    mysqli_query($koneksi, "DELETE FROM detail_transaksi WHERE id_transaksi='$id_transaksi'");

    // Hapus transaksi utama
    mysqli_query($koneksi, "DELETE FROM transaksi WHERE id_transaksi='$id_transaksi'");

    echo "<script>alert('Pesanan berhasil dibatalkan dan stok dikembalikan!'); window.location='pesanan_saya.php';</script>";
    exit;
} else {
    echo "<script>alert('Pesanan tidak bisa dibatalkan karena sudah dikirim atau selesai!'); window.location='pesanan_saya.php';</script>";
    exit;
}
?>
