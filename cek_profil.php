<?php
// Cek apakah session sudah aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    return;
}

$id_user = $_SESSION['id_user'];
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'");
$user = mysqli_fetch_assoc($query);

// Daftar field yang wajib diisi
$required_fields = [
    'no_hp' => 'Nomor HP',
    'jenis_kelamin' => 'Jenis Kelamin',
    'alamat' => 'Alamat'
];

$missing_fields = [];
foreach ($required_fields as $field => $label) {
    if (empty($user[$field])) {
        $missing_fields[] = $label;
    }
}

$_SESSION['profil_lengkap'] = empty($missing_fields);
$_SESSION['missing_fields'] = $missing_fields;
$_SESSION['user_data'] = $user;
?>