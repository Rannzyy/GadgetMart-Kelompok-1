<?php
session_start();
include '../koneksi.php';

$id = $_GET['id'];
mysqli_query($koneksi, "DELETE FROM keranjang WHERE id_keranjang = '$id'");

header("Location: keranjang.php");
