<?php
session_start();
include '../koneksi.php';

// Cek role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses hanya untuk admin!'); window.location='../index.php';</script>";
    exit;
}

// Ambil data dengan error handling
$admins = mysqli_query($koneksi, "SELECT * FROM users WHERE role='admin' ORDER BY id_user DESC");
if (!$admins) {
    die("Error query admin: " . mysqli_error($koneksi));
}

$customers = mysqli_query($koneksi, "SELECT * FROM users WHERE role='customer' ORDER BY id_user DESC");
if (!$customers) {
    die("Error query customer: " . mysqli_error($koneksi));
}

$total_admins = mysqli_num_rows($admins);
$total_customers = mysqli_num_rows($customers);
$total_users = $total_admins + $total_customers;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data User - Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/data_user.css">
</head>
<body>

<div class="dashboard-header">
    <div class="container">
        <div class="header-content">
            <div class="header-title">
                <h3 class="page-title mb-0">
                    <i class="fas fa-users-cog text-success"></i> Manajemen User
                </h3>
                <p class="text-muted mb-0 mt-1">Kelola semua pengguna sistem GadgetMart</p>
            </div>
            <div class="header-actions">
                <a class="btn btn-back" href="dashboard.php">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
                <a class="btn btn-danger" href="../user/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
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
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3><?= $total_users ?></h3>
                <p>Total Pengguna</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-success-light">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="stat-info">
                <h3><?= $total_admins ?></h3>
                <p>Administrator</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-info-light">
                <i class="fas fa-user"></i>
            </div>
            <div class="stat-info">
                <h3><?= $total_customers ?></h3>
                <p>Pelanggan</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-warning-light">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-info">
                <h3><?= date('d M Y') ?></h3>
                <p>Update Terakhir</p>
            </div>
        </div>
    </div>
    
    <!-- Admin Section -->
    <div class="data-card">
        <div class="data-card-header">
            <div class="header-left">
                <i class="fas fa-user-shield"></i>
                <span>Administrator</span>
            </div>
            <div class="header-right">
                <span class="total-badge">
                    <i class="fas fa-chart-simple"></i>
                    Total: <?= $total_admins ?> Admin
                </span>
            </div>
        </div>
        <div class="data-card-body p-0">
            <?php if ($total_admins > 0): ?>
            <div class="table-responsive-custom">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="35%">Nama Lengkap</th>
                            <th width="30%">Username</th>
                            <th width="15%">Role</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            // Reset pointer
                            mysqli_data_seek($admins, 0);
                            while ($row = mysqli_fetch_assoc($admins)) { 
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>
                                    <div class="user-info">
                                        <i class="fas fa-user-circle"></i>
                                        <?= htmlspecialchars($row['nama']); ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($row['username']); ?></td>
                                <td>
                                    <span class="role-badge role-admin">
                                        <i class="fas fa-user-shield"></i> Admin
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="action-links">
                                        <a class="btn-delete" href="javascript:void(0);" 
                                           onclick="confirmDelete('<?= $row['id_user']; ?>', 'admin')" 
                                           title="Hapus Admin">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Table Footer -->
                <div class="table-footer">
                    <div class="footer-info">
                        <i class="fas fa-info-circle"></i>
                        Menampilkan <?= $total_admins ?> data administrator
                    </div>
                </div>
            <?php else: ?>
            <div class="empty-state-custom">
                <div class="empty-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h5>Belum Ada Administrator</h5>
                <p>Belum ada data administrator yang ditemukan</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Customer Section -->
    <div class="data-card">
        <div class="data-card-header">
            <div class="header-left">
                <i class="fas fa-users"></i>
                <span>Pelanggan</span>
            </div>
            <div class="header-right">
                <span class="total-badge">
                    <i class="fas fa-chart-simple"></i>
                    Total: <?= $total_customers ?> Pelanggan
                </span>
            </div>
        </div>
        <div class="data-card-body p-0">
            <?php if ($total_customers > 0): ?>
            <div class="table-responsive-custom">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="35%">Nama Lengkap</th>
                            <th width="30%">Username</th>
                            <th width="15%">Role</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            // Reset pointer
                            mysqli_data_seek($customers, 0);
                            while ($row = mysqli_fetch_assoc($customers)) { 
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>
                                    <div class="user-info">
                                        <i class="fas fa-user-circle"></i>
                                        <?= htmlspecialchars($row['nama']); ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($row['username']); ?></td>
                                <td>
                                    <span class="role-badge role-customer">
                                        <i class="fas fa-user"></i> Customer
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="action-links">
                                        <a class="btn-delete" href="javascript:void(0);" 
                                           onclick="confirmDelete('<?= $row['id_user']; ?>', 'customer')" 
                                           title="Hapus Pelanggan">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Table Footer -->
                <div class="table-footer">
                    <div class="footer-info">
                        <i class="fas fa-info-circle"></i>
                        Menampilkan <?= $total_customers ?> data pelanggan
                    </div>
                </div>
            <?php else: ?>
            <div class="empty-state-custom">
                <div class="empty-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h5>Belum Ada Pelanggan</h5>
                <p>Belum ada data pelanggan yang terdaftar</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Fungsi konfirmasi hapus dengan SweetAlert atau confirm biasa
    function confirmDelete(id, role) {
        let pesan = role === 'admin' ? 'Yakin ingin menghapus admin ini?' : 'Yakin ingin menghapus pelanggan ini?';
        if (confirm(pesan)) {
            window.location.href = 'hapus_user.php?id=' + id;
        }
        return false;
    }
    
    // Add animation on load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Page loaded successfully');
        
        const cards = document.querySelectorAll('.stat-card, .data-card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Debug: Cek apakah ada error
        console.log('Total Admins: <?= $total_admins ?>');
        console.log('Total Customers: <?= $total_customers ?>');
    });
    
    // Mencegah error jika ada link yang tidak sengaja
    window.onerror = function(msg, url, line) {
        console.log('Error: ' + msg + ' at ' + url + ':' + line);
        return false;
    };
</script>

</body>
</html>