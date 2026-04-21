<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../user/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_keranjang = $_POST['id_keranjang'];
    $action = $_POST['action'];
    
    // Ambil jumlah saat ini
    $query = mysqli_query($koneksi, "SELECT jumlah FROM keranjang WHERE id_keranjang = '$id_keranjang'");
    $data = mysqli_fetch_assoc($query);
    $current_jumlah = $data['jumlah'];
    
    if ($action == 'tambah') {
        $new_jumlah = $current_jumlah + 1;
    } elseif ($action == 'kurang') {
        $new_jumlah = max(1, $current_jumlah - 1);
    }
    
    mysqli_query($koneksi, "UPDATE keranjang SET jumlah = '$new_jumlah' WHERE id_keranjang = '$id_keranjang'");
}

header("Location: keranjang.php");
exit;
?>