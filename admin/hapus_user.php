<?php
session_start();
include '../koneksi.php';

// Cek role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses hanya untuk admin!'); window.location='../index.php';</script>";
    exit;
}

// Cek apakah ada parameter id yang dikirim
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>
        alert('ID User tidak ditemukan!');
        window.location='data_user.php';
    </script>";
    exit;
}

$id_user = mysqli_real_escape_string($koneksi, $_GET['id']);

// Cek apakah user dengan id tersebut ada
$check_query = "SELECT * FROM users WHERE id_user = '$id_user'";
$check_result = mysqli_query($koneksi, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    echo "<script>
        alert('User tidak ditemukan!');
        window.location='data_user.php';
    </script>";
    exit;
}

$user_data = mysqli_fetch_assoc($check_result);
$username = $user_data['username'];
$role = $user_data['role'];

// Cek apakah user yang akan dihapus adalah admin terakhir
if ($role == 'admin') {
    $admin_count_query = "SELECT COUNT(*) as total FROM users WHERE role = 'admin'";
    $admin_count_result = mysqli_query($koneksi, $admin_count_query);
    $admin_count = mysqli_fetch_assoc($admin_count_result)['total'];
    
    if ($admin_count <= 1) {
        echo "<script>
            alert('Tidak dapat menghapus admin terakhir! Minimal harus ada 1 admin.');
            window.location='data_user.php';
        </script>";
        exit;
    }
}

// Cek apakah user yang dihapus adalah diri sendiri (admin yang sedang login)
if ($_SESSION['id_user'] == $id_user) {
    echo "<script>
        alert('Anda tidak dapat menghapus akun sendiri!');
        window.location='data_user.php';
    </script>";
    exit;
}

// ========== HAPUS DATA TERKAIT SEBELUM MENGHAPUS USER ==========

// 1. Hapus data wishlist user
$delete_wishlist = "DELETE FROM wishlist WHERE id_user = '$id_user'";
mysqli_query($koneksi, $delete_wishlist);

// 2. Hapus data keranjang user
$delete_keranjang = "DELETE FROM keranjang WHERE id_user = '$id_user'";
mysqli_query($koneksi, $delete_keranjang);

// 3. Hapus detail transaksi dari transaksi user (jika ada)
$get_transaksi = mysqli_query($koneksi, "SELECT id_transaksi FROM transaksi WHERE id_user = '$id_user'");
while ($trans = mysqli_fetch_assoc($get_transaksi)) {
    $id_transaksi = $trans['id_transaksi'];
    mysqli_query($koneksi, "DELETE FROM detail_transaksi WHERE id_transaksi = '$id_transaksi'");
}

// 4. Hapus transaksi user
$delete_transaksi = "DELETE FROM transaksi WHERE id_user = '$id_user'";
mysqli_query($koneksi, $delete_transaksi);

// 5. Hapus rating user
$delete_rating = "DELETE FROM rating WHERE id_user = '$id_user'";
mysqli_query($koneksi, $delete_rating);

// 6. Hapus foto profil jika ada (opsional)
if (!empty($user_data['foto']) && $user_data['foto'] != 'default.jpg') {
    $foto_path = "../gambar pp/" . $user_data['foto'];
    if (file_exists($foto_path)) {
        unlink($foto_path);
    }
}

// ========== HAPUS USER ==========
$delete_query = "DELETE FROM users WHERE id_user = '$id_user'";

if (mysqli_query($koneksi, $delete_query)) {
    echo "<script>
        alert('User $username (Role: $role) berhasil dihapus!');
        window.location='data_user.php';
    </script>";
} else {
    echo "<script>
        alert('Gagal menghapus user! Error: " . mysqli_error($koneksi) . "');
        window.location='data_user.php';
    </script>";
}

mysqli_close($koneksi);
?>