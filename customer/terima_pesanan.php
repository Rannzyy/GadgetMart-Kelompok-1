<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    echo "<script>alert('Akses hanya untuk customer!'); window.location='../index.php';</script>";
    exit;
}

if (isset($_POST['id_transaksi'])) {
    $id_transaksi = $_POST['id_transaksi'];
    $id_user = $_SESSION['id_user'];
    
    // Verifikasi bahwa transaksi ini milik user yang login
    $cek = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE id_transaksi = '$id_transaksi' AND id_user = '$id_user'");
    
    if (mysqli_num_rows($cek) > 0) {
        $transaksi = mysqli_fetch_assoc($cek);
        
        // Pastikan statusnya 'Sedang Diantar'
        if (strtolower($transaksi['status']) == 'sedang diantar') {
            // Update status menjadi 'Selesai'
            $update = mysqli_query($koneksi, "UPDATE transaksi SET status = 'Selesai', tanggal_selesai = NOW() WHERE id_transaksi = '$id_transaksi'");
            
            if ($update) {
                echo "<script>alert('Pesanan telah diterima dan status diubah menjadi Selesai!'); window.location='pesanan_saya.php';</script>";
            } else {
                echo "<script>alert('Gagal mengupdate status pesanan!'); window.location='pesanan_saya.php';</script>";
            }
        } else {
            echo "<script>alert('Pesanan tidak dalam status Sedang Diantar!'); window.location='pesanan_saya.php';</script>";
        }
    } else {
        echo "<script>alert('Transaksi tidak ditemukan!'); window.location='pesanan_saya.php';</script>";
    }
} else {
    echo "<script>alert('Akses tidak valid!'); window.location='pesanan_saya.php';</script>";
}
?>