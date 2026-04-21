<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses hanya untuk admin!'); window.location='../index.php';</script>";
    exit;
}

$id_transaksi = $_GET['id'];

// Ambil detail produk
$detail = mysqli_query($koneksi, "
    SELECT dt.*, p.nama_produk, p.harga, p.gambar
    FROM detail_transaksi dt
    JOIN produk p ON dt.id_produk = p.id_produk
    WHERE dt.id_transaksi = '$id_transaksi'
");

// Ambil info transaksi utama + user
$info = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT t.catatan, t.status, t.tanggal_pesan, 
           u.nama AS nama_pembeli, u.no_hp, u.alamat
    FROM transaksi t 
    JOIN users u ON t.id_user = u.id_user
    WHERE t.id_transaksi = '$id_transaksi'
"));

$alamat_pengirim = $info['alamat'];
$catatan = $info['catatan'];
$status = $info['status'];
$tanggal = $info['tanggal_pesan'];
$nama_pembeli = $info['nama_pembeli'];
$no_telepon = $info['no_hp'];

?>

<!DOCTYPE html>
<html>

<head>
    <title>Invoice Transaksi #<?= $id_transaksi ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }

        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Header */
        .invoice-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .invoice-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .invoice-header .invoice-id {
            font-size: 18px;
            opacity: 0.9;
        }

        /* Content */
        .invoice-content {
            padding: 30px;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }

        .info-item {
            background: #f8f9fa;
            padding: 12px 15px;
            border-radius: 8px;
        }

        .info-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 500;
            color: #2c3e50;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-menunggu {
            background: #ffc107;
            color: #856404;
        }

        .status-diproses {
            background: #17a2b8;
            color: white;
        }

        .status-dikirim {
            background: #007bff;
            color: white;
        }

        .status-selesai {
            background: #28a745;
            color: white;
        }

        /* Table */
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .product-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }

        .product-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }

        .summary-row {
            background: #f8f9fa;
            font-weight: 500;
        }

        .grand-total-row {
            background: #e9ecef;
            font-weight: bold;
            font-size: 18px;
        }

        .grand-total-row td:last-child {
            color: #28a745;
            font-size: 20px;
        }

        /* Footer */
        .invoice-footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }

        .invoice-footer p {
            margin: 5px 0;
            color: #6c757d;
            font-size: 12px;
        }

        /* Button Container */
        .button-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            gap: 15px;
            z-index: 1000;
        }

        /* Back Button */
        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        /* Print Button */
        .print-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .print-btn:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .button-container {
                display: none;
            }

            .invoice-container {
                box-shadow: none;
                margin: 0;
                border-radius: 0;
            }

            .invoice-header {
                background: #667eea;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .status-badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .product-table th {
                background: #f8f9fa;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <h1>INVOICE</h1>
            <div class="invoice-id">#<?= $id_transaksi ?></div>
        </div>

        <!-- Content -->
        <div class="invoice-content">
            <!-- Info -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Nama Pemesan</div>
                    <div class="info-value"><?= htmlspecialchars($nama_pembeli) ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">No. Telepon</div>
                    <div class="info-value"><?= !empty($no_telepon) ? htmlspecialchars($no_telepon) : '-' ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Tanggal Pesan</div>
                    <div class="info-value"><?= date('d M Y, H:i', strtotime($tanggal)) ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge status-<?= $status ?>">
                            <?= strtoupper($status) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Alamat -->
            <div class="info-item" style="margin-bottom: 20px;">
                <div class="info-label">Alamat Pengiriman</div>
                <div class="info-value"><?= !empty($alamat_pengirim) ? nl2br(htmlspecialchars($alamat_pengirim)) : '-' ?></div>
            </div>

            <?php if (!empty($catatan)): ?>
                <div class="info-item" style="margin-bottom: 20px;">
                    <div class="info-label">Catatan</div>
                    <div class="info-value" style="background: #fff3cd; padding: 10px; border-radius: 6px;">
                        <?= nl2br(htmlspecialchars($catatan)) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tabel Produk -->
            <table class="product-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Harga Satuan</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $grandtotal = 0;
                    $totalItem = 0;
                    while ($row = mysqli_fetch_assoc($detail)) {
                        $subtotal = $row['harga'] * $row['jumlah'];
                        $grandtotal += $subtotal;
                        $totalItem += $row['jumlah'];
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><img src="../gambar/<?= htmlspecialchars($row['gambar']); ?>" class="product-img"
                                    alt="<?= htmlspecialchars($row['nama_produk']); ?>"></td>
                            <td><?= htmlspecialchars($row['nama_produk']); ?></td>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                            <td><?= $row['jumlah']; ?></td>
                            <td>Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                        </tr>
                    <?php } ?>

                    <tr class="summary-row">
                        <td colspan="4">Total Barang</td>
                        <td colspan="2"><?= $totalItem ?> item</td>
                    </tr>

                    <tr class="grand-total-row">
                        <td colspan="4">Grand Total</td>
                        <td colspan="2">Rp <?= number_format($grandtotal, 0, ',', '.'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <p>Terima kasih telah berbelanja di toko kami</p>
            <p>Invoice ini dibuat secara otomatis oleh sistem</p>
            <p>Tanggal Cetak: <?= date('d M Y H:i:s') ?></p>
        </div>
    </div>

    <div class="button-container">
        <a href="detail_transaksi.php?id=<?= $id_transaksi ?>" class="back-btn">
            ← Kembali ke Detail Transaksi
        </a>
        <button class="print-btn" onclick="window.print()">
            🖨️ Cetak Invoice
        </button>
    </div>

    <script>
        // Optional: Auto print when page loads (uncomment if needed)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>

</html>