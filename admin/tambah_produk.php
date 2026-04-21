<?php
session_start();
include '../koneksi.php';

// Cek role admin
if ($_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); window.location='../index.php';</script>";
    exit;
}

if (isset($_POST['submit'])) {
    // Escape input untuk keamanan
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

    // Simpan ke database produk
    $query = "INSERT INTO produk 
        (nama_produk, kategori, harga, stok, deskripsi, tanggal_masuk, 
        prosesor, ram, penyimpanan, layar, baterai, kompatibilitas, berat)
        VALUES 
        ('$nama', '$kategori', '$harga', '$stok', '$deskripsi', '$tanggal', 
         '$prosesor', '$ram', '$penyimpanan', '$layar', '$baterai', '$kompatibilitas', '$berat')";

    if (mysqli_query($koneksi, $query)) {
        $id_produk = mysqli_insert_id($koneksi);

        // Upload multiple gambar
        $gambar_upload = true;
        $gambar_utama = '';

        // Buat direktori jika belum ada
        if (!file_exists("../gambar/produk/" . $id_produk)) {
            mkdir("../gambar/produk/" . $id_produk, 0777, true);
        }

        // Proses upload gambar
        if (isset($_FILES['gambar'])) {
            $files = $_FILES['gambar'];
            $jumlah_file = count($files['name']);

            for ($i = 0; $i < $jumlah_file; $i++) {
                if ($files['error'][$i] == 0) {
                    $nama_gambar = time() . '_' . $i . '_' . basename($files['name'][$i]);
                    $target_path = "../gambar/produk/" . $id_produk . "/" . $nama_gambar;

                    if (move_uploaded_file($files['tmp_name'][$i], $target_path)) {
                        // Tentukan gambar utama (yang pertama diupload)
                        $is_primary = ($i == 0) ? 1 : 0;
                        if ($i == 0) {
                            $gambar_utama = $nama_gambar;
                        }

                        // Simpan ke tabel produk_gambar
                        $query_gambar = "INSERT INTO produk_gambar (id_produk, gambar, is_primary) 
                                        VALUES ('$id_produk', '$nama_gambar', '$is_primary')";
                        mysqli_query($koneksi, $query_gambar);
                    } else {
                        $gambar_upload = false;
                    }
                }
            }
        }

        // Update produk dengan gambar utama (untuk kompatibilitas)
        if ($gambar_utama) {
            mysqli_query($koneksi, "UPDATE produk SET gambar='$gambar_utama' WHERE id_produk='$id_produk'");
        }

        if ($gambar_upload) {
            echo "<script>alert('Produk berhasil ditambahkan dengan " . $jumlah_file . " gambar!'); window.location='data_produk.php';</script>";
        } else {
            echo "<script>alert('Produk ditambahkan namun ada beberapa gambar gagal diupload!'); window.location='data_produk.php';</script>";
        }
    } else {
        echo "<script>alert('Gagal menambahkan produk: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk | GadgetMart Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/add_produk.css">
    <style>
        /* Additional styles for multiple image upload */
        .image-upload-area {
            border: 2px dashed var(--border);
            border-radius: 16px;
            padding: 20px;
            background: var(--light);
            transition: all 0.3s;
            cursor: pointer;
        }

        .image-upload-area:hover {
            border-color: var(--primary);
            background: #eff6ff;
        }

        .image-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .image-upload-label i {
            font-size: 48px;
            color: var(--primary);
        }

        .image-upload-label span {
            font-size: 14px;
            color: var(--gray);
        }

        .image-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .preview-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            background: white;
        }

        .preview-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }

        .preview-item .preview-info {
            padding: 8px;
            font-size: 11px;
            text-align: center;
            background: white;
        }

        .preview-item .primary-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: var(--success);
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
        }

        .preview-item .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: all 0.3s;
        }

        .preview-item .remove-btn:hover {
            transform: scale(1.1);
        }

        .info-text {
            font-size: 12px;
            color: var(--gray);
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo-area">
                <i class="fas fa-plus-circle"></i>
                <h2>Tambah Produk Baru</h2>
                <span class="badge-admin">GadgetMart</span>
            </div>
            <div class="header-actions">
                <a class="btn btn-primary" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="btn btn-secondary" href="data_produk.php">
                    <i class="fas fa-box-open"></i> Data Produk
                </a>
                <a class="btn btn-danger" href="../user/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <div class="user-info">
                <i class="fas fa-user-shield"></i>
                <span>Hai, <strong><?= htmlspecialchars($_SESSION['username']); ?></strong> | Selamat datang
                    kembali!</span>
            </div>

            <form method="POST" enctype="multipart/form-data" id="productForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nama">
                            <i class="fas fa-tag"></i> Nama Produk
                        </label>
                        <input type="text" id="nama" name="nama" placeholder="Masukkan nama produk" required>
                    </div>

                    <div class="form-group">
                        <label for="kategori">
                            <i class="fas fa-boxes"></i> Kategori
                        </label>
                        <select id="kategori" name="kategori" onchange="tampilkanFieldKategori()" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="HP">📱 HP</option>
                            <option value="Laptop">💻 Laptop</option>
                            <option value="Tablet">📟 Tablet</option>
                            <option value="Aksesoris">🎧 Aksesoris</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="harga">
                            <i class="fas fa-money-bill-wave"></i> Harga (Rp)
                        </label>
                        <input type="text" id="harga" name="harga" placeholder="Masukkan harga" required>
                    </div>

                    <div class="form-group">
                        <label for="stok">
                            <i class="fas fa-cubes"></i> Stok
                        </label>
                        <input type="number" id="stok" name="stok" placeholder="Masukkan jumlah stok" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deskripsi">
                        <i class="fas fa-align-left"></i> Deskripsi
                    </label>
                    <textarea id="deskripsi" name="deskripsi" rows="5" placeholder="Masukkan deskripsi produk"
                        required></textarea>
                </div>

                <!-- Spesifikasi untuk HP, Laptop, Tablet -->
                <div id="spesifikasi" class="spec-section" style="display:none;">
                    <div class="section-title">
                        <i class="fas fa-microchip"></i>
                        <h4>Spesifikasi Produk</h4>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Prosesor</label>
                            <input type="text" name="prosesor" placeholder="Contoh: Intel Core i5-1135G7">
                        </div>
                        <div class="form-group">
                            <label>RAM</label>
                            <input type="text" name="ram" placeholder="Contoh: 8GB DDR4">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Penyimpanan</label>
                            <input type="text" name="penyimpanan" placeholder="Contoh: 512GB SSD">
                        </div>
                        <div class="form-group">
                            <label>Layar</label>
                            <input type="text" name="layar" placeholder="Contoh: 6.5 inch AMOLED">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Baterai</label>
                        <input type="text" name="baterai" placeholder="Contoh: 5000mAh">
                    </div>
                </div>

                <!-- Detail untuk Aksesoris -->
                <div id="aksesorisField" class="spec-section" style="display:none;">
                    <div class="section-title">
                        <i class="fas fa-headphones"></i>
                        <h4>Detail Aksesoris</h4>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Kompatibilitas</label>
                            <input type="text" name="kompatibilitas" placeholder="Contoh: Universal / iPhone / Android">
                        </div>
                        <div class="form-group">
                            <label>Berat (gram)</label>
                            <input type="number" name="berat" placeholder="Contoh: 150" min="1">
                        </div>
                    </div>
                </div>

                <!-- Multiple Image Upload -->
                <div class="form-group">
                    <label for="gambar">
                        <i class="fas fa-images"></i> Foto Produk (Bisa pilih beberapa)
                    </label>
                    <div class="image-upload-area" id="uploadArea">
                        <input type="file" id="gambar" name="gambar[]" accept="image/*" multiple style="display:none;">
                        <div class="image-upload-label" onclick="document.getElementById('gambar').click()">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Klik atau tarik file untuk upload</span>
                            <span style="font-size: 12px;">Bisa pilih beberapa gambar sekaligus</span>
                        </div>
                    </div>
                    <div id="imagePreview" class="image-preview-grid"></div>
                    <div class="info-text">
                        <i class="fas fa-info-circle"></i>
                        Gambar pertama akan menjadi foto utama produk
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tanggal">
                            <i class="fas fa-calendar-alt"></i> Tanggal Masuk
                        </label>
                        <input type="date" id="tanggal" name="tanggal" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-success" form="formUtama">
                        <i class="fas fa-save"></i> Simpan Produk
                    </button>
                    <button type="reset" class="btn btn-reset" onclick="resetPreview()">
                        <i class="fas fa-undo-alt"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let selectedFiles = [];

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

        // Set tanggal hari ini sebagai default
        document.addEventListener('DOMContentLoaded', function () {
            if (document.getElementById('tanggal')) {
                var today = new Date().toISOString().split('T')[0];
                document.getElementById('tanggal').value = today;
            }
        });

        // Format harga dengan titik sebagai pemisah ribuan
        const hargaInput = document.getElementById('harga');

        hargaInput.addEventListener('input', function (e) {
            let angka = this.value.replace(/\D/g, '');
            let format = new Intl.NumberFormat('id-ID').format(angka);
            this.value = format;
        });

        // Multiple image preview
        const imageInput = document.getElementById('gambar');
        const previewContainer = document.getElementById('imagePreview');

        imageInput.addEventListener('change', function (e) {
            const newFiles = Array.from(e.target.files);

            // gabung + hapus duplikat berdasarkan nama file
            selectedFiles = [...new Map(
                selectedFiles.concat(newFiles).map(f => [f.name, f])
            ).values()];

            // update input file
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            imageInput.files = dataTransfer.files;

            updatePreview();
        });

        function updatePreview() {
            previewContainer.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}">
                        ${index === 0 ? '<div class="primary-badge"><i class="fas fa-star"></i> Utama</div>' : ''}
                        <button type="button" class="remove-btn" onclick="removeImage(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="preview-info">
                            ${file.name.length > 20 ? file.name.substring(0, 20) + '...' : file.name}
                        </div>
                    `;
                    previewContainer.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            });
        }

        function removeImage(index) {
            selectedFiles.splice(index, 1);

            // Update input files
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            imageInput.files = dataTransfer.files;

            updatePreview();
        }

        function resetPreview() {
            selectedFiles = [];
            imageInput.value = '';
            previewContainer.innerHTML = '';
            document.getElementById('productForm').reset();
            var today = new Date().toISOString().split('T')[0];
            document.getElementById('tanggal').value = today;
        }

        // Drag and drop functionality
        const uploadArea = document.getElementById('uploadArea');

        uploadArea.addEventListener('dragover', function (e) {
            e.preventDefault();
            uploadArea.style.borderColor = 'var(--primary)';
            uploadArea.style.background = '#eff6ff';
        });

        uploadArea.addEventListener('dragleave', function (e) {
            e.preventDefault();
            uploadArea.style.borderColor = 'var(--border)';
            uploadArea.style.background = 'var(--light)';
        });

        uploadArea.addEventListener('drop', function (e) {
            e.preventDefault();
            uploadArea.style.borderColor = 'var(--border)';
            uploadArea.style.background = 'var(--light)';

            const files = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
            if (files.length > 0) {
                const existingFiles = Array.from(imageInput.files);
                const dataTransfer = new DataTransfer();

                existingFiles.forEach(file => dataTransfer.items.add(file));
                files.forEach(file => dataTransfer.items.add(file));

                imageInput.files = dataTransfer.files;
                selectedFiles = Array.from(imageInput.files);
                updatePreview();
            }
        });
    </script>
</body>

</html>