<?php
session_start();
include '../koneksi.php';

// Cek role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses hanya untuk admin!'); window.location='../index.php';</script>";
    exit;
}

// Filter tanggal
$filter_mulai = isset($_GET['mulai']) ? $_GET['mulai'] : '';
$filter_sampai = isset($_GET['sampai']) ? $_GET['sampai'] : '';

$where = "";
if (!empty($filter_mulai) && !empty($filter_sampai)) {
    $where = "AND DATE(t.tanggal_pesan) BETWEEN '$filter_mulai' AND '$filter_sampai'";
    $periode_text = date('d/m/Y', strtotime($filter_mulai)) . ' - ' . date('d/m/Y', strtotime($filter_sampai));
} else {
    $periode_text = 'Semua Waktu';
}

// Hitung total pemasukan
$query_total = mysqli_query($koneksi, "SELECT SUM(jumlah) AS total FROM pemasukan");
$row_total   = mysqli_fetch_assoc($query_total);
$total_pemasukan = $row_total['total'] ?? 0;

// Ambil data pemasukan
$query = mysqli_query($koneksi, "
    SELECT t.id_transaksi, t.tanggal_pesan, t.total_harga, u.nama AS nama_pembeli
    FROM transaksi t
    JOIN users u ON t.id_user = u.id_user
    WHERE 1=1 $where
    ORDER BY t.tanggal_pesan DESC
");

$total_transaksi = mysqli_num_rows($query);

// Nama file PDF
$filename = "Laporan_Pemasukan_" . date('Ymd_His') . ".pdf";

// Set header untuk PDF
header("Content-type: application/pdf");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Expires: 0");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Pemasukan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 20px;
            color: #2c3e50;
        }
        
        .header h3 {
            margin: 0;
            font-size: 14px;
            color: #666;
        }
        
        .header p {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #888;
        }
        
        .info-section {
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #ddd;
        }
        
        .info-table {
            width: 100%;
            font-size: 11px;
        }
        
        .info-table td {
            padding: 3px 5px;
        }
        
        .info-table td:first-child {
            font-weight: bold;
            width: 120px;
        }
        
        .summary {
            margin-bottom: 20px;
            padding: 10px;
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
        }
        
        .summary-table {
            width: 100%;
            font-size: 12px;
        }
        
        .summary-table td {
            padding: 5px;
        }
        
        .summary-table td:first-child {
            font-weight: bold;
            width: 150px;
        }
        
        .summary-table .total-value {
            font-size: 16px;
            font-weight: bold;
            color: #2e7d32;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        
        table.data-table th {
            background: #2c3e50;
            color: white;
            padding: 8px 5px;
            text-align: center;
            border: 1px solid #1a252f;
        }
        
        table.data-table td {
            padding: 6px 5px;
            text-align: center;
            border: 1px solid #ddd;
        }
        
        table.data-table td.text-end {
            text-align: right;
        }
        
        table.data-table td.text-start {
            text-align: left;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #ddd;
        }
        
        .print-date {
            text-align: right;
            font-size: 9px;
            color: #999;
            margin-top: 10px;
        }
        
        .badge-total {
            font-weight: bold;
            color: #2e7d32;
        }
        
        .text-end {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-start {
            text-align: left;
        }
        
        .customer-info {
            display: inline-flex;
            align-items: center;
        }
        
        .transaction-id {
            font-weight: bold;
            color: #1565c0;
        }
        
        .price-amount {
            font-weight: bold;
            color: #2e7d32;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN DATA PEMASUKAN</h1>
        <h3>Sistem Informasi Penjualan</h3>
        <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
    </div>
    
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>Periode Laporan</td>
                <td>: <?= $periode_text ?></td>
            </tr>
            <tr>
                <td>Tanggal Cetak</td>
                <td>: <?= date('d F Y H:i:s') ?></td>
            </tr>
            <tr>
                <td>Petugas</td>
                <td>: <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?></td>
            </tr>
        </table>
    </div>
    
    <div class="summary">
        <table class="summary-table">
            <tr>
                <td>Total Pemasukan</td>
                <td>:</td>
                <td class="total-value">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td>Total Transaksi</td>
                <td>:</td>
                <td><?= $total_transaksi ?> Transaksi</td>
            </tr>
        </table>
    </div>
    
    <h4 style="margin: 15px 0 5px 0;">Detail Transaksi:</h4>
    
    <?php if ($total_transaksi > 0): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th width="30">No</th>
                <th width="100">ID Transaksi</th>
                <th width="120">Tanggal Pesan</th>
                <th>Nama Pembeli</th>
                <th width="150">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while ($row = mysqli_fetch_assoc($query)): 
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center transaction-id">#<?= $row['id_transaksi'] ?></td>
                <td class="text-center"><?= date('d/m/Y H:i', strtotime($row['tanggal_pesan'])) ?></td>
                <td class="text-start"><?= htmlspecialchars($row['nama_pembeli']) ?></td>
                <td class="text-end price-amount">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td colspan="4" class="text-end">GRAND TOTAL :</td>
                <td class="text-end" style="color: #2e7d32;">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>
    <?php else: ?>
    <div style="text-align: center; padding: 40px; background: #f8f9fa; border: 1px solid #ddd;">
        <p style="color: #999;">Tidak ada data pemasukan untuk periode yang dipilih</p>
    </div>
    <?php endif; ?>
    
    <div class="print-date">
        Laporan ini digenerate secara otomatis oleh sistem
    </div>
    
    <div class="footer">
        <p>&copy; <?= date('Y') ?> Sistem Informasi Penjualan - All Rights Reserved</p>
    </div>
</body>
</html>