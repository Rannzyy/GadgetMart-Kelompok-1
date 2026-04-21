<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$id_produk = $_GET['id'];

mysqli_query($koneksi, "DELETE FROM wishlist WHERE id_user = '$id_user' AND id_produk = '$id_produk'");
header("Location: wishlist_saya.php");
exit();
?>
