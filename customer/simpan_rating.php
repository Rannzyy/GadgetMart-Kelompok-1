<?php
session_start();
include '../koneksi.php';

if (isset($_POST['id_user'], $_POST['id_produk'], $_POST['id_transaksi'], $_POST['rating'], $_POST['ulasan'])) {
    $id_user = $_POST['id_user'];
    $id_produk = $_POST['id_produk'];
    $id_transaksi = $_POST['id_transaksi'];
    $rating = $_POST['rating'];
    $ulasan = $_POST['ulasan'];

    // Cek dulu kalau udah pernah ngasih rating
    $cek = mysqli_query($koneksi, "SELECT * FROM rating WHERE id_user='$id_user' AND id_produk='$id_produk' AND id_transaksi='$id_transaksi'");
    if (mysqli_num_rows($cek) == 0) {
        mysqli_query($koneksi, "INSERT INTO rating (id_user, id_produk, id_transaksi, rating, ulasan) 
                                VALUES ('$id_user', '$id_produk', '$id_transaksi', '$rating', '$ulasan')");
    }

    echo "<script>alert('Terima kasih atas ratingnya!'); window.location='pesanan_saya.php';</script>";
}
?>
