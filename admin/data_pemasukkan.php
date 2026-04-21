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

// Hitung total pemasukan
$query_total = mysqli_query($koneksi, "SELECT SUM(jumlah) AS total FROM pemasukan");
$row_total   = mysqli_fetch_assoc($query_total);
$total_pemasukan = $row_total['total'] ?? 0; 

$where = "";
if (!empty($filter_mulai) && !empty($filter_sampai)) {
    $where = "AND DATE(t.tanggal_pesan) BETWEEN '$filter_mulai' AND '$filter_sampai'";
}

// Ambil data pemasukan
$query = mysqli_query($koneksi, "
    SELECT t.id_transaksi, t.tanggal_pesan, t.total_harga, u.nama AS nama_pembeli
    FROM transaksi t
    JOIN users u ON t.id_user = u.id_user
    WHERE 1=1 $where
    ORDER BY t.tanggal_pesan DESC
");

$total_transaksi = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Pemasukan - Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/pemasukkan.css">
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <div class="header-content">
                <div class="header-title">
                    <h3 class="page-title mb-0">
                        <i class="fas fa-money-bill-wave text-success"></i> Data Pemasukan
                    </h3>
                    <p class="text-muted mb-0 mt-1">Kelola dan pantau semua transaksi pemasukan</p>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon bg-primary-light">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h3>Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></h3>
                    <p>Total Pemasukan</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-success-light">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $total_transaksi ?></h3>
                    <p>Total Transaksi</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-info-light">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?= !empty($filter_mulai) && !empty($filter_sampai) ? date('d/m/Y', strtotime($filter_mulai)) . ' - ' . date('d/m/Y', strtotime($filter_sampai)) : 'Semua Waktu' ?></h3>
                    <p>Periode Filter</p>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-card">
            <div class="filter-header">
                <i class="fas fa-filter"></i>
                <h5 class="mb-0">Filter Laporan</h5>
            </div>
            <form method="GET" class="filter-form">
                <div class="filter-inputs">
                    <div class="input-group-custom">
                        <label>
                            <i class="fas fa-calendar-day"></i>
                            Tanggal Mulai
                        </label>
                        <input type="date" name="mulai" class="form-control-custom" value="<?= $filter_mulai ?>">
                    </div>
                    <div class="input-group-custom">
                        <label>
                            <i class="fas fa-calendar-check"></i>
                            Tanggal Sampai
                        </label>
                        <input type="date" name="sampai" class="form-control-custom" value="<?= $filter_sampai ?>">
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-search"></i> Terapkan Filter
                        </button>
                        <a href="data_pemasukkan.php" class="btn btn-secondary-custom">
                            <i class="fas fa-sync-alt"></i> Reset
                        </a>
                        <a href="cetak_pemasukkan.php?mulai=<?= $filter_mulai ?>&sampai=<?= $filter_sampai ?>" 
                           target="_blank" 
                           class="btn btn-print-custom">
                            <i class="fas fa-print"></i> Cetak PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Income Data Table -->
        <div class="data-card">
            <div class="data-card-header">
                <div class="header-left">
                    <i class="fas fa-receipt"></i>
                    <span>Daftar Transaksi</span>
                </div>
                <div class="header-right">
                    <span class="total-badge">
                        <i class="fas fa-chart-simple"></i>
                        Total: Rp <?= number_format($total_pemasukan, 0, ',', '.') ?>
                    </span>
                </div>
            </div>
            <div class="data-card-body">
                <?php if ($total_transaksi > 0): ?>
                <div class="table-responsive-custom">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Transaksi</th>
                                <th>Tanggal</th>
                                <th>Pembeli</th>
                                <th class="text-end">Total Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($query)): 
                            ?>
                            <tr>
                                <td class="fw-bold"><?= $no++ ?></td>
                                <td>
                                    <span class="transaction-id">#<?= $row['id_transaksi'] ?></span>
                                </td>
                                <td>
                                    <i class="far fa-calendar-alt"></i>
                                    <?= date('d M Y, H:i', strtotime($row['tanggal_pesan'])) ?>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <i class="fas fa-user-circle"></i>
                                        <?= htmlspecialchars($row['nama_pembeli']) ?>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="price-amount">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Table Footer -->
                <div class="table-footer">
                    <div class="footer-info">
                        <i class="fas fa-info-circle"></i>
                        Menampilkan <?= $total_transaksi ?> data transaksi
                    </div>
                </div>
                
                <?php else: ?>
                <div class="empty-state-custom">
                    <div class="empty-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h5>Tidak Ada Data Pemasukan</h5>
                    <p>Tidak ditemukan transaksi yang sesuai dengan filter yang Anda pilih</p>
                    <a href="data_pemasukkan.php" class="btn btn-primary-custom">
                        <i class="fas fa-sync-alt"></i> Tampilkan Semua
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add animation on load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in-up');
            });
        });
    </script>
</body>
</html>