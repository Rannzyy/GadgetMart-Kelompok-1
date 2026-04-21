<?php
session_start();
header('Content-Type: application/json');
include '../koneksi.php';

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['status' => 'login']);
    exit;
}

$id_user = $_SESSION['id_user'];
$id_produk = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;

if ($id_produk <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID produk tidak valid']);
    exit;
}

$cek = mysqli_query($koneksi, "SELECT * FROM wishlist WHERE id_user = '$id_user' AND id_produk = '$id_produk'");

if (mysqli_num_rows($cek) > 0) {
    mysqli_query($koneksi, "DELETE FROM wishlist WHERE id_user = '$id_user' AND id_produk = '$id_produk'");
    echo json_encode(['status' => 'success', 'action' => 'removed']);
} else {
    mysqli_query($koneksi, "INSERT INTO wishlist (id_user, id_produk) VALUES ('$id_user', '$id_produk')");
    echo json_encode(['status' => 'success', 'action' => 'added']);
}
?>