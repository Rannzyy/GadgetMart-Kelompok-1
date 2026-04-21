<?php
session_start();
include '../koneksi.php';

if ($_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); window.location='../index.php';</script>";
    exit;
}

$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk='$id'"));

if (!$data) {
    echo "<script>alert('Produk tidak ditemukan!'); window.location='data_produk.php';</script>";
    exit;
}

// Ambil semua gambar produk dari tabel produk_gambar
$query_gambar = "SELECT * FROM produk_gambar WHERE id_produk='$id' ORDER BY is_primary DESC, id_gambar ASC";
$result_gambar = mysqli_query($koneksi, $query_gambar);
$list_gambar = [];
while ($row = mysqli_fetch_assoc($result_gambar)) {
    $list_gambar[] = $row;
}

// Proses hapus gambar
if (isset($_GET['hapus_gambar'])) {
    $id_gambar = $_GET['hapus_gambar'];
    $query_get = "SELECT * FROM produk_gambar WHERE id_gambar='$id_gambar' AND id_produk='$id'";
    $res_get = mysqli_query($koneksi, $query_get);
    $gambar_data = mysqli_fetch_assoc($res_get);

    if ($gambar_data) {
        $file_path = "../gambar/produk/" . $id . "/" . $gambar_data['gambar'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        mysqli_query($koneksi, "DELETE FROM produk_gambar WHERE id_gambar='$id_gambar'");

        if ($gambar_data['is_primary'] == 1) {
            mysqli_query($koneksi, "UPDATE produk_gambar SET is_primary=1 WHERE id_produk='$id' LIMIT 1");
            $new_primary = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT gambar FROM produk_gambar WHERE id_produk='$id' AND is_primary=1"));
            if ($new_primary) {
                mysqli_query($koneksi, "UPDATE produk SET gambar='{$new_primary['gambar']}' WHERE id_produk='$id'");
            } else {
                mysqli_query($koneksi, "UPDATE produk SET gambar='' WHERE id_produk='$id'");
            }
        }
    }
    echo "<script>window.location='edit_produk.php?id=$id';</script>";
    exit;
}

// Proses set gambar utama
if (isset($_GET['set_primary'])) {
    $id_gambar = $_GET['set_primary'];
    mysqli_query($koneksi, "UPDATE produk_gambar SET is_primary=0 WHERE id_produk='$id'");
    mysqli_query($koneksi, "UPDATE produk_gambar SET is_primary=1 WHERE id_gambar='$id_gambar'");
    $gambar_data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT gambar FROM produk_gambar WHERE id_gambar='$id_gambar'"));
    if ($gambar_data) {
        mysqli_query($koneksi, "UPDATE produk SET gambar='{$gambar_data['gambar']}' WHERE id_produk='$id'");
    }
    echo "<script>window.location='edit_produk.php?id=$id';</script>";
    exit;
}

// Proses upload gambar baru (AJAX)
if (isset($_POST['upload_gambar_ajax'])) {
    if (isset($_FILES['gambar_baru']) && !empty($_FILES['gambar_baru']['name'][0])) {
        $files = $_FILES['gambar_baru'];
        $jumlah_file = count($files['name']);

        if (!file_exists("../gambar/produk/" . $id)) {
            mkdir("../gambar/produk/" . $id, 0777, true);
        }

        $upload_success = 0;
        $uploaded_images = [];

        for ($i = 0; $i < $jumlah_file; $i++) {
            if ($files['error'][$i] == 0) {
                $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                $nama_gambar = time() . '_' . $i . '_' . uniqid() . '.' . $ext;
                $target_path = "../gambar/produk/" . $id . "/" . $nama_gambar;

                if (move_uploaded_file($files['tmp_name'][$i], $target_path)) {
                    $cek_gambar = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM produk_gambar WHERE id_produk='$id'");
                    $total = mysqli_fetch_assoc($cek_gambar)['total'];
                    $is_primary = ($total == 0) ? 1 : 0;

                    $query_gambar = "INSERT INTO produk_gambar (id_produk, gambar, is_primary) 
                                    VALUES ('$id', '$nama_gambar', '$is_primary')";
                    mysqli_query($koneksi, $query_gambar);

                    if ($is_primary == 1) {
                        mysqli_query($koneksi, "UPDATE produk SET gambar='$nama_gambar' WHERE id_produk='$id'");
                    }
                    $upload_success++;
                    $uploaded_images[] = [
                        'id_gambar' => mysqli_insert_id($koneksi),
                        'gambar' => $nama_gambar,
                        'is_primary' => $is_primary
                    ];
                }
            }
        }

        echo json_encode(['success' => true, 'count' => $upload_success, 'images' => $uploaded_images]);
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'Tidak ada gambar']);
    exit;
}

if (isset($_POST['submit'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $harga = str_replace('.', '', $_POST['harga']);
    $harga = mysqli_real_escape_string($koneksi, $harga);
    $stok = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $prosesor = mysqli_real_escape_string($koneksi, $_POST['prosesor'] ?? '');
    $ram = mysqli_real_escape_string($koneksi, $_POST['ram'] ?? '');
    $penyimpanan = mysqli_real_escape_string($koneksi, $_POST['penyimpanan'] ?? '');
    $layar = mysqli_real_escape_string($koneksi, $_POST['layar'] ?? '');
    $baterai = mysqli_real_escape_string($koneksi, $_POST['baterai'] ?? '');
    $kompatibilitas = mysqli_real_escape_string($koneksi, $_POST['kompatibilitas'] ?? '');
    $berat = mysqli_real_escape_string($koneksi, $_POST['berat'] ?? '');

    $query = "UPDATE produk SET 
        nama_produk='$nama', 
        kategori='$kategori', 
        harga='$harga',
        stok='$stok', 
        deskripsi='$deskripsi', 
        tanggal_masuk='$tanggal',
        prosesor='$prosesor', 
        ram='$ram', 
        penyimpanan='$penyimpanan', 
        layar='$layar', 
        baterai='$baterai', 
        kompatibilitas='$kompatibilitas', 
        berat='$berat'
        WHERE id_produk='$id'";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data produk berhasil diperbarui!'); window.location='data_produk.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data: " . mysqli_error($koneksi) . "');</script>";
    }
}

// Refresh list gambar setelah proses
$query_gambar = "SELECT * FROM produk_gambar WHERE id_produk='$id' ORDER BY is_primary DESC, id_gambar ASC";
$result_gambar = mysqli_query($koneksi, $query_gambar);
$list_gambar = [];
while ($row = mysqli_fetch_assoc($result_gambar)) {
    $list_gambar[] = $row;
}

// Fungsi untuk mendapatkan path gambar
function getImagePath($id_produk, $gambar) {
    if (empty($gambar)) {
        return "../gambar/default.jpg";
    }
    
    $new_path = "../gambar/produk/" . $id_produk . "/" . $gambar;
    if (file_exists($new_path)) {
        return $new_path;
    }
    
    $old_path = "../gambar/" . $gambar;
    if (file_exists($old_path)) {
        return $old_path;
    }
    
    return "../gambar/default.jpg";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk | GadgetMart Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/edit_produk.css">
    <style>
        .gallery-section {
            margin-top: 30px;
            padding: 20px;
            background: var(--light);
            border-radius: 16px;
            border: 1px solid var(--border);
        }

        .gallery-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border);
        }

        .gallery-title i {
            font-size: 24px;
            color: var(--primary);
        }

        .gallery-title h4 {
            margin: 0;
            color: var(--dark);
        }

        /* Main Image Preview */
        .main-image-preview {
            margin-bottom: 20px;
            background: white;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            border: 1px solid var(--border);
        }

        .main-image-preview img {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
            border-radius: 8px;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .gallery-item {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            position: relative;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .gallery-item img {
            width: 100%;
            height: 160px;
            object-fit: cover;
        }

        .primary-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--success);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            z-index: 1;
        }

        .gallery-actions {
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            background: #f8f9fa;
            border-top: 1px solid var(--border);
        }

        .gallery-actions a {
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 11px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-set-primary {
            background: var(--success);
            color: white;
        }

        .btn-set-primary:hover {
            background: #059669;
            transform: scale(1.02);
        }

        .btn-delete-image {
            background: var(--danger);
            color: white;
        }

        .btn-delete-image:hover {
            background: #dc2626;
            transform: scale(1.02);
        }

        .upload-area {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 35px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }

        .upload-area:hover {
            border-color: var(--primary);
            background: #eff6ff;
        }

        .upload-area i {
            font-size: 48px;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .upload-area p {
            margin: 0;
            color: var(--dark);
            font-size: 14px;
            font-weight: 500;
        }

        .upload-area small {
            color: var(--gray);
            font-size: 12px;
        }

        .empty-gallery {
            text-align: center;
            padding: 50px;
            color: var(--gray);
        }

        .empty-gallery i {
            font-size: 56px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-spinner {
            background: white;
            padding: 25px 35px;
            border-radius: 16px;
            text-align: center;
            box-shadow: var(--shadow-hover);
        }

        .loading-spinner i {
            font-size: 48px;
            color: var(--primary);
            margin-bottom: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .fa-spinner {
            animation: spin 1s linear infinite;
        }

        .form-actions-wrapper {
            position: sticky;
            bottom: 20px;
            margin-top: 30px;
            z-index: 100;
        }

        .form-actions {
            background: white;
            padding: 20px 30px;
            border-radius: 16px;
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }

        .btn-secondary {
            background: var(--secondary);
            color: white;
        }

        .btn-secondary:hover {
            background: #475569;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }
            .btn {
                justify-content: center;
            }
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
                gap: 15px;
            }
            .upload-area {
                padding: 25px;
            }
        }
    </style>
</head>

<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner"></i>
            <p>Mengupload gambar...</p>
        </div>
    </div>

    <div class="container">
        <div class="header">
            <div class="logo-area">
                <i class="fas fa-edit"></i>
                <h2>Edit Produk</h2>
                <span class="badge-admin">GadgetMart</span>
            </div>
            <a href="data_produk.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data" id="formUtama">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nama"><i class="fas fa-tag"></i> Nama Produk</label>
                        <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($data['nama_produk']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="kategori"><i class="fas fa-boxes"></i> Kategori</label>
                        <select id="kategori" name="kategori" onchange="tampilkanFieldKategori()">
                            <option value="HP" <?= $data['kategori'] == 'HP' ? 'selected' : ''; ?>>📱 HP</option>
                            <option value="Laptop" <?= $data['kategori'] == 'Laptop' ? 'selected' : ''; ?>>💻 Laptop</option>
                            <option value="Tablet" <?= $data['kategori'] == 'Tablet' ? 'selected' : ''; ?>>📟 Tablet</option>
                            <option value="Aksesoris" <?= $data['kategori'] == 'Aksesoris' ? 'selected' : ''; ?>>🎧 Aksesoris</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="harga"><i class="fas fa-money-bill-wave"></i> Harga (Rp)</label>
                        <input type="text" id="harga" name="harga" value="<?= number_format($data['harga'], 0, ',', '.'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="stok"><i class="fas fa-cubes"></i> Stok</label>
                        <input type="number" id="stok" name="stok" value="<?= $data['stok']; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deskripsi"><i class="fas fa-align-left"></i> Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="5"><?= htmlspecialchars($data['deskripsi']); ?></textarea>
                </div>

                <div id="spesifikasi" class="spec-section" style="display:none;">
                    <div class="section-title">
                        <i class="fas fa-microchip"></i>
                        <h4>Spesifikasi Produk</h4>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Prosesor</label>
                            <input type="text" name="prosesor" value="<?= htmlspecialchars($data['prosesor'] ?? ''); ?>" placeholder="Contoh: Intel Core i5-1135G7">
                        </div>
                        <div class="form-group">
                            <label>RAM</label>
                            <input type="text" name="ram" value="<?= htmlspecialchars($data['ram'] ?? ''); ?>" placeholder="Contoh: 8GB DDR4">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Penyimpanan</label>
                            <input type="text" name="penyimpanan" value="<?= htmlspecialchars($data['penyimpanan'] ?? ''); ?>" placeholder="Contoh: 512GB SSD">
                        </div>
                        <div class="form-group">
                            <label>Layar</label>
                            <input type="text" name="layar" value="<?= htmlspecialchars($data['layar'] ?? ''); ?>" placeholder="Contoh: 6.5 inch AMOLED">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Baterai</label>
                        <input type="text" name="baterai" value="<?= htmlspecialchars($data['baterai'] ?? ''); ?>" placeholder="Contoh: 5000mAh">
                    </div>
                </div>

                <div id="aksesorisField" class="spec-section" style="display:none;">
                    <div class="section-title">
                        <i class="fas fa-headphones"></i>
                        <h4>Detail Aksesoris</h4>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Kompatibilitas</label>
                            <input type="text" name="kompatibilitas" value="<?= htmlspecialchars($data['kompatibilitas'] ?? ''); ?>" placeholder="Contoh: Universal / iPhone / Android">
                        </div>
                        <div class="form-group">
                            <label>Berat (gram)</label>
                            <input type="number" name="berat" value="<?= $data['berat'] ?? ''; ?>" placeholder="Contoh: 150">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="tanggal"><i class="fas fa-calendar-alt"></i> Tanggal Masuk</label>
                    <input type="date" id="tanggal" name="tanggal" value="<?= $data['tanggal_masuk']; ?>" required>
                </div>
            </form>

            <!-- Galeri Produk -->
            <div class="gallery-section">
                <div class="gallery-title">
                    <i class="fas fa-images"></i>
                    <h4>Galeri Produk</h4>
                </div>

                <!-- Preview Gambar Utama -->
                <?php if (!empty($list_gambar)): ?>
                <div class="main-image-preview">
                    <img id="gambarUtama" src="<?= getImagePath($id, $list_gambar[0]['gambar']); ?>" alt="Gambar Utama">
                    <p style="margin-top: 10px; font-size: 12px; color: var(--gray);">
                        <i class="fas fa-info-circle"></i> Klik thumbnail untuk melihat gambar
                    </p>
                </div>
                <?php endif; ?>

                <div id="galleryGrid" class="gallery-grid">
                    <?php foreach ($list_gambar as $gambar): ?>
                        <div class="gallery-item" data-id="<?= $gambar['id_gambar'] ?>">
                            <img src="<?= getImagePath($id, $gambar['gambar']); ?>" 
                                 alt="Gambar Produk"
                                 onclick="gantiGambar(this)"
                                 style="cursor: pointer;">
                            <?php if ($gambar['is_primary'] == 1): ?>
                                <div class="primary-badge">
                                    <i class="fas fa-star"></i> Utama
                                </div>
                            <?php endif; ?>
                            <div class="gallery-actions">
                                <?php if ($gambar['is_primary'] != 1): ?>
                                    <a href="?id=<?= $id; ?>&set_primary=<?= $gambar['id_gambar']; ?>" 
                                       class="btn-set-primary"
                                       onclick="return confirm('Jadikan ini gambar utama?')">
                                        <i class="fas fa-star"></i> Jadi Utama
                                    </a>
                                <?php endif; ?>
                                <a href="?id=<?= $id; ?>&hapus_gambar=<?= $gambar['id_gambar']; ?>" 
                                   class="btn-delete-image"
                                   onclick="return confirm('Yakin ingin menghapus gambar ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($list_gambar)): ?>
                    <div id="emptyGallery" class="empty-gallery">
                        <i class="fas fa-camera"></i>
                        <p>Belum ada gambar</p>
                        <small>Klik area di bawah untuk upload gambar</small>
                    </div>
                <?php endif; ?>

                <!-- Upload Area -->
                <form method="POST" enctype="multipart/form-data" id="formUpload">
                    <div class="upload-area" id="uploadArea">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Klik atau tarik file untuk upload gambar</p>
                        <small>Bisa pilih beberapa gambar sekaligus (Ctrl+klik)</small>
                        <input type="file" id="gambar_baru" name="gambar_baru[]" accept="image/*" multiple style="display:none;">
                    </div>
                </form>
            </div>

            <!-- Form Actions -->
            <div class="form-actions-wrapper">
                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-primary" form="formUtama">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="data_produk.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function tampilkanFieldKategori() {
            var kategori = document.getElementById("kategori").value;
            var spesifikasi = document.getElementById("spesifikasi");
            var aksesorisField = document.getElementById("aksesorisField");

            if (kategori === 'Laptop' || kategori === 'HP' || kategori === 'Tablet') {
                spesifikasi.style.display = 'block';
                aksesorisField.style.display = 'none';
            } else if (kategori === 'Aksesoris') {
                spesifikasi.style.display = 'none';
                aksesorisField.style.display = 'block';
            } else {
                spesifikasi.style.display = 'none';
                aksesorisField.style.display = 'none';
            }
        }

        window.onload = function () {
            tampilkanFieldKategori();
        }

        // Format harga
        const hargaInput = document.getElementById('harga');
        if (hargaInput) {
            hargaInput.addEventListener('input', function (e) {
                let angka = this.value.replace(/\D/g, '');
                let format = new Intl.NumberFormat('id-ID').format(angka);
                this.value = format;
            });
        }

        // Ganti gambar utama saat thumbnail diklik
        function gantiGambar(el) {
            const gambarUtama = document.getElementById('gambarUtama');
            if (gambarUtama) {
                gambarUtama.src = el.src;
            }
        }

        // UPLOAD GAMBAR
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('gambar_baru');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const galleryGrid = document.getElementById('galleryGrid');
        const emptyGallery = document.getElementById('emptyGallery');

        uploadArea.addEventListener('click', function () {
            fileInput.click();
        });

        fileInput.addEventListener('change', function () {
            if (this.files.length === 0) return;

            loadingOverlay.style.display = 'flex';

            const formData = new FormData();
            formData.append('upload_gambar_ajax', '1');

            for (let i = 0; i < this.files.length; i++) {
                formData.append('gambar_baru[]', this.files[i]);
            }

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loadingOverlay.style.display = 'none';

                if (data.success && data.images.length > 0) {
                    if (emptyGallery) {
                        emptyGallery.style.display = 'none';
                    }

                    // Hapus main image preview jika belum ada
                    let mainPreview = document.querySelector('.main-image-preview');
                    if (!mainPreview && galleryGrid) {
                        const newMainPreview = document.createElement('div');
                        newMainPreview.className = 'main-image-preview';
                        newMainPreview.innerHTML = '<img id="gambarUtama" src="" alt="Gambar Utama"><p style="margin-top:10px;font-size:12px;color:var(--gray);"><i class="fas fa-info-circle"></i> Klik thumbnail untuk melihat gambar</p>';
                        galleryGrid.parentNode.insertBefore(newMainPreview, galleryGrid);
                    }

                    data.images.forEach(function (img) {
                        const isPrimary = img.is_primary;
                        const imageUrl = '../gambar/produk/<?= $id ?>/' + img.gambar;

                        const galleryItem = document.createElement('div');
                        galleryItem.className = 'gallery-item';
                        galleryItem.setAttribute('data-id', img.id_gambar);
                        galleryItem.innerHTML = `
                            <img src="${imageUrl}" alt="Gambar Produk" onclick="gantiGambar(this)" style="cursor: pointer;">
                            ${isPrimary ? '<div class="primary-badge"><i class="fas fa-star"></i> Utama</div>' : ''}
                            <div class="gallery-actions">
                                ${!isPrimary ? `<a href="?id=<?= $id ?>&set_primary=${img.id_gambar}" class="btn-set-primary" onclick="return confirm('Jadikan ini gambar utama?')">
                                    <i class="fas fa-star"></i> Jadi Utama
                                </a>` : ''}
                                <a href="?id=<?= $id ?>&hapus_gambar=${img.id_gambar}" class="btn-delete-image" onclick="return confirm('Yakin ingin menghapus gambar ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </div>
                        `;
                        galleryGrid.appendChild(galleryItem);
                    });

                    Swal.fire('Berhasil!', data.count + ' gambar berhasil ditambahkan', 'success');
                    fileInput.value = '';
                    
                    // Refresh halaman untuk update gambar utama
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    Swal.fire('Gagal!', 'Gagal upload gambar', 'error');
                }
            })
            .catch(() => {
                loadingOverlay.style.display = 'none';
                Swal.fire('Gagal!', 'Terjadi kesalahan saat upload', 'error');
            });
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', function (e) {
            e.preventDefault();
            uploadArea.style.borderColor = 'var(--primary)';
            uploadArea.style.background = '#eff6ff';
        });

        uploadArea.addEventListener('dragleave', function (e) {
            e.preventDefault();
            uploadArea.style.borderColor = 'var(--border)';
            uploadArea.style.background = 'white';
        });

        uploadArea.addEventListener('drop', function (e) {
            e.preventDefault();
            uploadArea.style.borderColor = 'var(--border)';
            uploadArea.style.background = 'white';

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                const event = new Event('change');
                fileInput.dispatchEvent(event);
            }
        });
    </script>
</body>

</html>