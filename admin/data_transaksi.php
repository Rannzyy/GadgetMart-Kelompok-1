<?php
session_start();
include '../koneksi.php';

// Cek role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses hanya untuk admin!'); window.location='../index.php';</script>";
    exit;
}

// Ambil data transaksi
$query_transaksi = "SELECT t.id_transaksi, u.nama, t.tanggal_pesan, t.total_harga, t.status, t.tanggal_selesai
                    FROM transaksi t 
                    JOIN users u ON t.id_user = u.id_user 
                    ORDER BY t.id_transaksi DESC";
$transaksi = mysqli_query($koneksi, $query_transaksi);

// Hitung statistik dengan satu query
$stat_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as selesai,
    SUM(CASE WHEN status IN ('Menunggu', 'Diproses', 'Dikirim') THEN 1 ELSE 0 END) as aktif
    FROM transaksi";
$stat_result = mysqli_query($koneksi, $stat_query);
$stats = mysqli_fetch_assoc($stat_result);

$total_transaksi = $stats['total'];
$total_selesai = $stats['selesai'];
$total_aktif = $stats['aktif'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Dashboard Transaksi - Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kelola data transaksi pelanggan">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/transaksi.css">
</head>

<body>

    <div class="dashboard-header">
        <div class="container">
            <div class="header-content">
                <div class="header-title">
                    <h1 class="page-title">
                        <i class="fas fa-shopping-cart text-success"></i>
                        Data Transaksi
                    </h1>
                    <p class="text-muted">Kelola dan pantau semua transaksi pelanggan dengan mudah</p>
                </div>
                <div class="header-actions">
                    <a class="btn-back" href="dashboard.php">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                    <a class="btn-danger" href="../user/logout.php" onclick="return confirm('Yakin ingin logout?')">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <main class="container">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon bg-primary-light">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($total_transaksi) ?></h3>
                    <p>Total Transaksi</p>
                    <span class="stat-trend positive">
                        <i class="fas fa-chart-line"></i> Keseluruhan
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-success-light">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($total_selesai) ?></h3>
                    <p>Transaksi Selesai</p>
                    <span class="stat-trend positive">
                        <i class="fas fa-check"></i> Selesai
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-warning-light">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($total_aktif) ?></h3>
                    <p>Transaksi Aktif</p>
                    <span class="stat-trend warning">
                        <i class="fas fa-spinner"></i> Proses
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-info-light">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?= date('d M Y') ?></h3>
                    <p>Update Terakhir</p>
                    <span class="stat-trend">
                        <i class="fas fa-clock"></i> <?= date('H:i') ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Transaction Data Table -->
        <div class="data-card">
            <div class="data-card-header">
                <div class="header-left">
                    <i class="fas fa-receipt"></i>
                    <div>
                        <h3>Daftar Transaksi</h3>
                        <p>Semua data transaksi pelanggan</p>
                    </div>
                </div>
                <div class="header-right">
                    <div class="total-badge">
                        <i class="fas fa-chart-simple"></i>
                        <span><?= $total_transaksi ?> Transaksi</span>
                    </div>
                    <button class="refresh-btn" onclick="window.location.reload()" title="Refresh data">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>

            <div class="data-card-body">
                <?php if ($total_transaksi > 0): ?>
                    <div class="table-responsive-custom">
                        <table class="modern-table" id="transactionTable">
                            <thead>
                                <tr>
                                    <th class="sortable" data-sort="id">ID Transaksi <i class="fas fa-sort"></i></th>
                                    <th class="sortable" data-sort="customer">Pembeli <i class="fas fa-sort"></i></th>
                                    <th class="sortable" data-sort="date">Tanggal <i class="fas fa-sort"></i></th>
                                    <th class="sortable text-end" data-sort="price">Total Bayar <i class="fas fa-sort"></i>
                                    </th>
                                    <th class="sortable" data-sort="status">Status <i class="fas fa-sort"></i></th>
                                    <th class="sortable" data-sort="date_selesai">Tgl Selesai <i class="fas fa-sort"></i>
                                    </th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($transaksi)):
                                    $status_config = [
                                        'Menunggu' => ['class' => 'status-menunggu', 'icon' => 'fa-clock', 'label' => 'Menunggu'],
                                        'Diproses' => ['class' => 'status-diproses', 'icon' => 'fa-spinner fa-pulse', 'label' => 'Diproses'],
                                        'Dikirim' => ['class' => 'status-dikirim', 'icon' => 'fa-truck', 'label' => 'Dikirim'],
                                        'Selesai' => ['class' => 'status-selesai', 'icon' => 'fa-check-circle', 'label' => 'Selesai']
                                    ];
                                    $status = $status_config[$row['status']] ?? $status_config['Menunggu'];
                                    ?>
                                    <tr>
                                        <td data-label="ID Transaksi">
                                            <span
                                                class="transaction-id">#<?= str_pad($row['id_transaksi'], 5, '0', STR_PAD_LEFT); ?></span>
                                        </td>
                                        <td data-label="Pembeli">
                                            <div class="customer-info">
                                                <div class="customer-avatar">
                                                    <?= strtoupper(substr($row['nama'], 0, 2)) ?>
                                                </div>
                                                <div class="customer-details">
                                                    <strong><?= htmlspecialchars($row['nama']); ?></strong>
                                                    <small>Customer</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Tanggal">
                                            <div class="date-info">
                                                <i class="far fa-calendar-alt"></i>
                                                <span><?= date('d M Y', strtotime($row['tanggal_pesan'])); ?></span>
                                                <small><?= date('H:i', strtotime($row['tanggal_pesan'])); ?></small>
                                            </div>
                                        </td>
                                        <td data-label="Total Bayar" class="text-end">
                                            <span class="price-amount">
                                                Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?>
                                            </span>
                                        </td>
                                        <td data-label="Status">
                                            <span class="status-badge <?= $status['class']; ?>">
                                                <i class="fas <?= $status['icon']; ?>"></i>
                                                <?= $status['label']; ?>
                                            </span>
                                        </td>
                                        <td data-label="Tgl Selesai">
                                            <?php
                                            if ($row['status'] == 'Selesai' && !empty($row['tanggal_selesai'])): ?>
                                                <div class="date-info">
                                                    <i class="fas fa-check-circle" style="color: #10b981;"></i>
                                                    <span><?= date('d M Y', strtotime($row['tanggal_selesai'])); ?></span>
                                                    <small><?= date('H:i', strtotime($row['tanggal_selesai'])); ?></small>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Aksi" class="text-center">
                                            <div class="action-group">
                                                <a class="btn-detail"
                                                    href="detail_transaksi.php?id=<?= $row['id_transaksi']; ?>"
                                                    title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                    <span>Detail</span>
                                                </a>

                                                <?php if ($row['status'] !== 'Selesai'): ?>
                                                    <form action="update_status.php" method="GET" class="status-form">
                                                        <input type="hidden" name="id" value="<?= $row['id_transaksi']; ?>">
                                                        <select name="status" class="status-select" onchange="updateStatus(this)"
                                                            data-id="<?= $row['id_transaksi']; ?>">
                                                            <option value="Menunggu" <?= $row['status'] == 'Menunggu' ? 'selected' : '' ?>>⏳ Menunggu</option>
                                                            <option value="Diproses" <?= $row['status'] == 'Diproses' ? 'selected' : '' ?>>⚙️ Diproses</option>
                                                            <option value="Dikirim" <?= $row['status'] == 'Dikirim' ? 'selected' : '' ?>>🚚 Dikirim</option>
                                                            <option value="Selesai" <?= $row['status'] == 'Selesai' ? 'selected' : '' ?>>✅ Selesai</option>
                                                        </select>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="badge-selesai-fixed">
                                                        <i class="fas fa-check-circle"></i> Transaksi Selesai
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer with Pagination -->
                    <div class="table-footer">
                        <div class="footer-info">
                            <i class="fas fa-info-circle"></i>
                            <span>Menampilkan <strong><?= $total_transaksi ?></strong> data transaksi</span>
                        </div>
                        <div class="footer-actions">
                            <button class="export-btn" onclick="exportTableToExcel()">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                            <button class="print-btn" onclick="window.print()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="empty-state-custom">
                        <div class="empty-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <i class="fas fa-ban empty-overlay"></i>
                        </div>
                        <h5>Belum Ada Transaksi</h5>
                        <p>Belum ada transaksi yang dilakukan oleh pelanggan</p>
                        <a href="dashboard.php" class="btn-primary-custom">
                            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Update status dengan konfirmasi
        function updateStatus(selectElement) {
            const newStatus = selectElement.value;
            const transactionId = selectElement.dataset.id;

            if (confirm(`Ubah status transaksi #${transactionId} menjadi ${newStatus}?`)) {
                selectElement.closest('form').submit();
            } else {
                selectElement.value = selectElement.querySelector('option[selected]')?.value || selectElement.value;
            }
        }

        // Sorting table
        document.addEventListener('DOMContentLoaded', function () {
            const table = document.getElementById('transactionTable');
            if (!table) return;

            const headers = table.querySelectorAll('th.sortable');
            headers.forEach(header => {
                header.addEventListener('click', () => {
                    const column = header.cellIndex;
                    const sortType = header.dataset.sort;
                    sortTable(table, column, sortType);

                    // Update sort icons
                    headers.forEach(h => h.classList.remove('asc', 'desc'));
                    header.classList.add('asc');
                });
            });

            // Animation for stat cards
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in-up');
            });
        });

        function sortTable(table, column, type) {
            const tbody = table.tBodies[0];
            const rows = Array.from(tbody.rows);

            rows.sort((a, b) => {
                let aVal = a.cells[column].innerText.trim();
                let bVal = b.cells[column].innerText.trim();

                if (type === 'price') {
                    aVal = parseFloat(aVal.replace(/[^0-9,-]/g, '').replace(',', ''));
                    bVal = parseFloat(bVal.replace(/[^0-9,-]/g, '').replace(',', ''));
                } else if (type === 'date') {
                    aVal = new Date(aVal);
                    bVal = new Date(bVal);
                }

                if (aVal < bVal) return -1;
                if (aVal > bVal) return 1;
                return 0;
            });

            rows.forEach(row => tbody.appendChild(row));
        }

        // Export to Excel
        function exportTableToExcel() {
            const table = document.getElementById('transactionTable');
            const html = table.outerHTML;
            const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `transaksi_${new Date().toISOString().slice(0, 19)}.xls`;
            link.click();
            URL.revokeObjectURL(link.href);
        }

        // Search functionality
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('transactionTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                for (let j = 0; j < cells.length - 1; j++) {
                    const text = cells[j].innerText.toLowerCase();
                    if (text.includes(filter)) {
                        found = true;
                        break;
                    }
                }
                rows[i].style.display = found ? '' : 'none';
            }
        }

        // Auto refresh every 5 minutes (optional)
        setInterval(() => {
            if (confirm('Data akan di-refresh secara otomatis. Lanjutkan?')) {
                window.location.reload();
            }
        }, 300000);
    </script>

</body>

</html>