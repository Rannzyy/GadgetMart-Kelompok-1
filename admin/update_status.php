<?php
session_start();
include '../koneksi.php';

// Cek role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses hanya untuk admin!'); window.location='../index.php';</script>";
    exit;
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $status = mysqli_real_escape_string($koneksi, $_GET['status']);

    // Jika status menjadi SELESAI, catat tanggal_selesai
    if ($status == 'Selesai') {
        $tanggal_selesai = date('Y-m-d H:i:s');
        $query = "UPDATE transaksi SET status='$status', tanggal_selesai='$tanggal_selesai' WHERE id_transaksi='$id'";
        
        // Catat pemasukan
        $transaksi = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT total_harga FROM transaksi WHERE id_transaksi='$id'"));
        $jumlah = $transaksi['total_harga'];
        $tanggal = date('Y-m-d');
        
        $cek = mysqli_query($koneksi, "SELECT * FROM pemasukan WHERE id_transaksi='$id'");
        if (mysqli_num_rows($cek) == 0) {
            mysqli_query($koneksi, "INSERT INTO pemasukan (id_transaksi, tanggal, jumlah, keterangan)
                                    VALUES ('$id', '$tanggal', '$jumlah', 'Pemasukan dari transaksi #$id')");
        }
    } else {
        $query = "UPDATE transaksi SET status='$status' WHERE id_transaksi='$id'";
    }

    if (mysqli_query($koneksi, $query)) {
        echo "<script>
            alert('Status berhasil diubah menjadi $status');
            window.location.href = 'data_transaksi.php';
        </script>";
    } else {
        echo "<script>
            alert('Gagal update status: " . mysqli_error($koneksi) . "');
            window.location.href = 'data_transaksi.php';
        </script>";
    }
    exit;
} else {
    echo "<script>
        alert('ID atau status belum dikirim!');
        window.location.href = 'data_transaksi.php';
    </script>";
    exit;
}
?>