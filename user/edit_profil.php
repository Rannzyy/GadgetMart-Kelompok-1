<?php
session_start();
include '../koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Silakan login dulu'); window.location='../user/login.php';</script>";
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil data user dari database
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data user tidak ditemukan.";
    exit;
}

// Jika form disubmit
if (isset($_POST['update'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $jenis_kelamin = htmlspecialchars($_POST['jenis_kelamin']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $no_hp = htmlspecialchars($_POST['no_hp']);
    $latitude = htmlspecialchars($_POST['latitude'] ?? '');
    $longitude = htmlspecialchars($_POST['longitude'] ?? '');

    $foto = $data['foto'];

    // Jika upload foto baru
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nama_foto = uniqid() . '.' . $ext;
        $upload_path = "../gambar pp/" . $nama_foto;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
            if (!empty($foto) && file_exists("../gambar pp/" . $foto)) {
                unlink("../gambar pp/" . $foto);
            }
            $foto = $nama_foto;
        }
    }

    // Update data
    $update = mysqli_query($koneksi, "UPDATE users SET 
        nama = '$nama',
        username = '$username',
        email = '$email',
        jenis_kelamin = '$jenis_kelamin',
        alamat = '$alamat',
        no_hp = '$no_hp',
        foto = '$foto',
        latitude = '$latitude',
        longitude = '$longitude'
        WHERE id_user = '$id_user'
    ");

    if ($update) {
        echo "<script>alert('Profil berhasil diperbarui!'); window.location='profil.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui profil');</script>";
    }
}

// Default koordinat Indonesia
$default_lat = $data['latitude'] ?? '-7.981824';
$default_lng = $data['longitude'] ?? '112.687277';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil | GadgetMart.id</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Leaflet CSS & JS (OpenStreetMap - GRATIS) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Nominatim untuk reverse geocoding (GRATIS) -->
    <script src="https://cdn.jsdelivr.net/npm/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.js"></script>
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css" />

    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --success: #10b981;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9eef5 100%);
            min-height: 100vh;
        }

        /* Header Styles */
        header {
            background: white;
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-icon {
            width: 2.5rem;
            height: 2.5rem;
            background: var(--light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            color: var(--dark);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 1.2rem;
            height: 1.2rem;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-outline {
            border: 1px solid var(--primary);
            color: var(--primary);
            background: transparent;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        /* Profile Container */
        .profile-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .profile-header {
            margin-bottom: 1.5rem;
        }

        .profile-title {
            font-size: 1.8rem;
            color: var(--dark);
            position: relative;
            display: inline-block;
        }

        .profile-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary);
            border-radius: 2px;
        }

        .profile-card {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .profile-form {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            padding: 2rem;
        }

        /* Avatar Section */
        .profile-avatar {
            text-align: center;
            padding: 1rem;
        }

        .avatar-wrapper {
            position: relative;
            width: 180px;
            height: 180px;
            margin: 0 auto 1.5rem;
        }

        .avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: var(--shadow-md);
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }

        .avatar-placeholder {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            font-weight: 600;
            color: white;
            border: 4px solid white;
            box-shadow: var(--shadow-md);
        }

        .avatar-edit-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--primary);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            border: 3px solid white;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            z-index: 10;
        }

        .avatar-edit-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.05);
        }

        .avatar-edit-btn i {
            font-size: 1.2rem;
        }

        .avatar-edit-btn input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            border-radius: 50%;
        }

        .avatar-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .avatar-member-since {
            font-size: 0.8rem;
            color: var(--gray);
        }

        /* Preview image saat hover */
        .avatar-preview-tooltip {
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--dark);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }

        .avatar-wrapper:hover .avatar-preview-tooltip {
            opacity: 1;
        }

        /* Form */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-light);
            border-radius: 0.5rem;
            transition: 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Map */
        .map-container {
            margin-top: 0.5rem;
            border-radius: 0.75rem;
            overflow: hidden;
            border: 1px solid var(--gray-light);
        }

        #map {
            height: 300px;
            width: 100%;
        }

        .map-controls {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
            flex-wrap: wrap;
        }

        .map-btn {
            padding: 0.5rem 1rem;
            background: var(--light);
            border: 1px solid var(--gray-light);
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 0.8rem;
            transition: 0.3s;
        }

        .map-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .location-coords {
            background: var(--light);
            padding: 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            margin-top: 0.5rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .coords-value {
            font-family: monospace;
            color: var(--primary);
            font-weight: 500;
        }

        .profile-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .profile-form {
                grid-template-columns: 1fr;
            }

            .navbar {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="navbar">
            <a href="../index.php" class="logo">
                <i class="fas fa-laptop-code"></i>
                <span>GadgetMart</span>
            </a>
            <div class="nav-links">
                <a href="../index.php">Beranda</a>
                <a href="../index.php?kategori=Laptop">Laptop</a>
                <a href="../index.php?kategori=Smartphone">Smartphone</a>
                <a href="../index.php?kategori=Tablet">Tablet</a>
                <a href="../index.php?kategori=Aksesoris">Aksesoris</a>
            </div>
            <div class="user-actions">
                <a href="../cart/keranjang.php" class="btn-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <?php
                    $cart_count = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM keranjang WHERE id_user = '$id_user'");
                    $count = mysqli_fetch_assoc($cart_count);
                    if ($count['total'] > 0): ?>
                        <span class="cart-count"><?= $count['total']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="logout.php" class="btn-outline">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <div class="profile-container">
        <div class="profile-header">
            <h1 class="profile-title">Edit Profil</h1>
        </div>

        <div class="profile-card">
            <form method="POST" enctype="multipart/form-data" class="profile-form">
                <div class="profile-avatar">
                    <div class="avatar-wrapper">
                        <?php if (!empty($data['foto']) && file_exists('../gambar pp/' . $data['foto'])): ?>
                            <img src="../gambar pp/<?= $data['foto']; ?>" alt="Foto Profil" class="avatar" id="avatar-img">
                        <?php else: ?>
                            <div class="avatar-placeholder" id="avatar-placeholder">
                                <?= strtoupper(substr($data['nama'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>

                        <div class="avatar-edit-btn" title="Ganti Foto Profil">
                            <i class="fas fa-camera"></i>
                            <input type="file" name="foto" accept="image/*" id="avatar-upload">
                        </div>
                        <div class="avatar-preview-tooltip">Klik untuk ganti foto</div>
                    </div>
                    <div class="avatar-name"><?= htmlspecialchars($data['nama']); ?></div>
                    <div class="avatar-member-since">
                        <i class="far fa-calendar-alt"></i> Member sejak:
                        <?= date('d M Y', strtotime($data['tanggal_daftar'])); ?>
                    </div>
                </div>

                <div class="profile-info">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control"
                            value="<?= htmlspecialchars($data['nama']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control"
                            value="<?= htmlspecialchars($data['username']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                            value="<?= htmlspecialchars($data['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-control" required>
                            <option value="">-- Pilih --</option>

                            <option value="laki-laki" <?= (strtolower($data['jenis_kelamin']) == 'laki-laki') ? 'selected' : ''; ?>>
                                Laki-laki
                            </option>

                            <option value="perempuan" <?= (strtolower($data['jenis_kelamin']) == 'perempuan') ? 'selected' : ''; ?>>
                                Perempuan
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nomor HP</label>
                        <input type="text" name="no_hp" class="form-control"
                            value="<?= htmlspecialchars($data['no_hp']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alamat Lengkap</label>
                        <input type="text" name="alamat" id="alamat" class="form-control"
                            value="<?= htmlspecialchars($data['alamat']); ?>"
                            placeholder="Klik pada peta untuk memilih lokasi" required>

                        <!-- Map Container -->
                        <div class="map-container">
                            <div id="map"></div>
                        </div>

                        <!-- Map Controls -->
                        <div class="map-controls">
                            <button type="button" class="map-btn" onclick="getCurrentLocation()">
                                <i class="fas fa-location-dot"></i> Lokasi Saya
                            </button>
                            <button type="button" class="map-btn" onclick="setDefaultLocation()">
                                <i class="fas fa-store"></i> Toko GadgetMart
                            </button>
                            <button type="button" class="map-btn" onclick="resetMap()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>

                        <!-- Koordinat -->
                        <div class="location-coords">
                            <span><i class="fas fa-map-pin"></i> Koordinat:</span>
                            <span id="lat-display" class="coords-value">-</span>
                            <span>,</span>
                            <span id="lng-display" class="coords-value">-</span>
                        </div>

                        <input type="hidden" name="latitude" id="latitude" value="<?= $data['latitude'] ?? '' ?>">
                        <input type="hidden" name="longitude" id="longitude" value="<?= $data['longitude'] ?? '' ?>">
                    </div>

                    <div class="profile-actions">
                        <a href="profil.php" class="btn-outline">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" name="update" class="btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let map;
        let marker;
        let currentLat = <?= $default_lat ?>;
        let currentLng = <?= $default_lng ?>;

        // Inisialisasi map dengan Leaflet (OpenStreetMap - GRATIS)
        function initMap() {
            map = L.map('map').setView([currentLat, currentLng], 15);

            // Tile layer dari OpenStreetMap (GRATIS)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(map);

            // Marker dengan icon default
            marker = L.marker([currentLat, currentLng], {
                draggable: true
            }).addTo(map);

            // Update saat marker dipindah
            marker.on('dragend', function (e) {
                const pos = marker.getLatLng();
                updateLocation(pos.lat, pos.lng);
            });

            // Update saat map diklik
            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                updateLocation(e.latlng.lat, e.latlng.lng);
            });

            // Jika ada koordinat dari database, update display
            if (currentLat && currentLng && currentLat != '-6.2088') {
                updateLocation(currentLat, currentLng);
            }
        }

        // Update lokasi dan ambil alamat dari Nominatim (GRATIS)
        async function updateLocation(lat, lng) {
            // Update hidden inputs
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            // Update display
            document.getElementById('lat-display').innerHTML = lat.toFixed(6);
            document.getElementById('lng-display').innerHTML = lng.toFixed(6);

            // Reverse geocoding dengan Nominatim (GRATIS, tanpa API key)
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=id`);
                const data = await response.json();
                if (data.display_name) {
                    document.getElementById('alamat').value = data.display_name;
                }
            } catch (error) {
                console.log('Gagal mendapatkan alamat:', error);
            }
        }

        // Dapatkan lokasi saat ini dari browser
        // Dapatkan lokasi saat ini (diarahkan ke Malang)
        function getCurrentLocation() {
            // Koordinat Malang yang ditentukan
            const lat = -7.981824;
            const lng = 112.687277;

            map.setView([lat, lng], 15);
            marker.setLatLng([lat, lng]);
            updateLocation(lat, lng);

            // Tampilkan notifikasi bahwa lokasi diarahkan ke Malang
            showNotification('📍 Lokasi diatur ke Malang (Jl. ...)', 'info');
        }

        // Fungsi untuk menampilkan notifikasi
        function showNotification(message, type) {
            const preview = document.getElementById('address-preview');
            if (preview) {
                const span = document.getElementById('selected-address');
                if (span) {
                    span.innerHTML = message;
                    preview.style.background = type === 'success' ? '#10b981' : '#3b82f6';
                    preview.classList.add('active');

                    setTimeout(() => {
                        preview.classList.remove('active');
                    }, 3000);
                }
            } else {
                alert(message);
            }
        }

        // Set ke lokasi default (toko GadgetMart)
        function setDefaultLocation() {
            const lat = -6.2088;
            const lng = 106.8456;
            map.setView([lat, lng], 15);
            marker.setLatLng([lat, lng]);
            updateLocation(lat, lng);
        }

        // Reset peta
        function resetMap() {
            const lat = parseFloat(document.getElementById('latitude').value) || currentLat;
            const lng = parseFloat(document.getElementById('longitude').value) || currentLng;
            map.setView([lat, lng], 15);
        }

        // Avatar preview dengan efek loading
        document.getElementById('avatar-upload').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                // Validasi tipe file
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    showNotif('Format file tidak didukung! Gunakan JPG, PNG, GIF, atau WEBP', '#ef4444');
                    this.value = '';
                    return;
                }

                // Validasi ukuran file (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showNotif('Ukuran file terlalu besar! Maksimal 2MB', '#ef4444');
                    this.value = '';
                    return;
                }

                // Loading effect
                const avatarDiv = document.querySelector('.avatar-wrapper');
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'avatar-loading';
                loadingDiv.innerHTML = '<i class="fas fa-spinner fa-pulse"></i>';
                loadingDiv.style.cssText = 'position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(0,0,0,0.7);color:white;width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;z-index:20;';
                avatarDiv.appendChild(loadingDiv);

                const reader = new FileReader();
                reader.onload = function (event) {
                    const avatarImg = document.getElementById('avatar-img');
                    const avatarPlaceholder = document.getElementById('avatar-placeholder');

                    if (avatarImg) {
                        avatarImg.src = event.target.result;
                    } else if (avatarPlaceholder) {
                        avatarPlaceholder.remove();
                        const newImg = document.createElement('img');
                        newImg.id = 'avatar-img';
                        newImg.className = 'avatar';
                        newImg.src = event.target.result;
                        avatarDiv.insertBefore(newImg, avatarDiv.firstChild);
                    } else {
                        const existingImg = document.querySelector('.avatar');
                        if (existingImg) {
                            existingImg.src = event.target.result;
                        } else {
                            const newImg = document.createElement('img');
                            newImg.id = 'avatar-img';
                            newImg.className = 'avatar';
                            newImg.src = event.target.result;
                            avatarDiv.insertBefore(newImg, avatarDiv.firstChild);
                        }
                    }

                    // Hapus loading
                    loadingDiv.remove();
                    showNotif('Foto berhasil dipilih, klik Simpan untuk menyimpan', '#10b981');
                };
                reader.readAsDataURL(file);
            }
        });

        // Fungsi notifikasi sederhana
        function showNotif(pesan, warna = '#10b981') {
            let n = document.getElementById('notif-global');
            if (!n) {
                const div = document.createElement('div');
                div.id = 'notif-global';
                div.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;display:none;padding:12px 20px;border-radius:10px;color:white;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
                document.body.appendChild(div);
                n = div;
            }

            let icon = warna === '#10b981' ? 'fa-check-circle' : 'fa-exclamation-circle';
            n.style.background = warna;
            n.innerHTML = '<i class="fas ' + icon + '"></i> ' + pesan;
            n.style.display = 'block';

            setTimeout(() => {
                n.style.display = 'none';
            }, 3000);
        }

        // Inisialisasi map saat halaman selesai loading
        document.addEventListener('DOMContentLoaded', initMap);
    </script>
</body>

</html>