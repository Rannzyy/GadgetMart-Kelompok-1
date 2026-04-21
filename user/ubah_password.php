<?php
session_start();
include '../koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='login.php';</script>";
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil data user untuk header
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'");
$data = mysqli_fetch_assoc($query);
$foto_profil = $data['foto'] ?? 'default.jpg';

// Jika form disubmit
if (isset($_POST['ubah'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_baru = $_POST['konfirmasi_baru'];

    // Validasi password lama
    if ($password_lama !== $data['password']) {
        echo "<script>alert('Password lama salah!');</script>";
    } elseif (strlen($password_baru) < 4) {
        echo "<script>alert('Password baru minimal 4 karakter!');</script>";
    } elseif ($password_baru !== $konfirmasi_baru) {
        echo "<script>alert('Konfirmasi password baru tidak cocok!');</script>";
    } else {
        // Update password baru
        $update = mysqli_query($koneksi, "UPDATE users SET password = '$password_baru' WHERE id_user = '$id_user'");
        if ($update) {
            echo "<script>alert('Password berhasil diubah!'); window.location='profil.php';</script>";
        } else {
            echo "<script>alert('Gagal mengubah password!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password | GadgetMart.id</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/ubah_password.css">
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
                <a href="../index.php?kategori=HP">Smartphone</a>
                <a href="../index.php?kategori=Tablet">Tablet</a>
                <a href="../index.php?kategori=Aksesoris">Aksesoris</a>
            </div>

            <div class="user-actions">
                <a href="../cart/keranjang.php" class="btn-icon" title="Keranjang">
                    <i class="fas fa-shopping-cart"></i>
                    <?php
                    $cart_count = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM keranjang WHERE id_user = '$id_user'");
                    $count = mysqli_fetch_assoc($cart_count);
                    if ($count['total'] > 0): ?>
                        <span class="cart-count"><?= $count['total']; ?></span>
                    <?php endif; ?>
                </a>

                <div class="dropdown">
                    <button class="dropdown-toggle">
                        <img src="../gambar pp/<?= $foto_profil; ?>" alt="Profil" class="profile-img">
                        <span><?= htmlspecialchars($data['username']); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="profil.php">
                            <i class="fas fa-user"></i> Profil Saya
                        </a>
                        <a href="edit_profil.php">
                            <i class="fas fa-edit"></i> Edit Profil
                        </a>
                        <a href="ubah_password.php">
                            <i class="fas fa-key"></i> Ubah Password
                        </a>
                        <?php if ($_SESSION['role'] == 'customer'): ?>
                            <a href="../customer/pesanan_saya.php">
                                <i class="fas fa-shopping-bag"></i> Pesanan Saya
                            </a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Keluar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Ubah Password</h1>
            <a href="profil.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Kembali ke Profil
            </a>
        </div>

        <div class="password-card">
            <div class="card-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h2>Keamanan Akun</h2>
            <p>Pastikan password baru Anda kuat dan mudah diingat</p>

            <form method="POST" class="password-form">
                <div class="form-group">
                    <label for="password_lama">
                        <i class="fas fa-key"></i> Password Lama
                    </label>
                    <div class="input-group">
                        <input type="password" name="password_lama" id="password_lama" class="form-control" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password_lama')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_baru">
                        <i class="fas fa-lock"></i> Password Baru
                    </label>
                    <div class="input-group">
                        <input type="password" name="password_baru" id="password_baru" class="form-control" required
                            minlength="4">
                        <button type="button" class="toggle-password" onclick="togglePassword('password_baru')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar"></div>
                        <div class="strength-text">
                            <span id="strength-icon"><i class="fas fa-shield-alt"></i></span>
                            Kekuatan password: <span id="strength-label">Ketik password...</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="konfirmasi_baru">
                        <i class="fas fa-check-circle"></i> Konfirmasi Password Baru
                    </label>
                    <div class="input-group">
                        <input type="password" name="konfirmasi_baru" id="konfirmasi_baru" class="form-control"
                            required>
                        <button type="button" class="toggle-password" onclick="togglePassword('konfirmasi_baru')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="confirm-message" class="confirm-message"></div>
                </div>

                <div class="password-requirements">
                    <h4><i class="fas fa-shield-alt"></i> Tips Password Kuat:</h4>
                    <ul>
                        <li id="req-min-length"><i class="fas fa-circle"></i> Minimal 4 karakter</li>
                        <li id="req-lowercase"><i class="fas fa-circle"></i> Mengandung huruf kecil (a-z)</li>
                        <li id="req-uppercase"><i class="fas fa-circle"></i> Mengandung huruf besar (A-Z)</li>
                        <li id="req-number"><i class="fas fa-circle"></i> Mengandung angka (0-9)</li>
                        <li id="req-special"><i class="fas fa-circle"></i> Mengandung karakter khusus ($@#&!)</li>
                    </ul>
                </div>

                <div class="form-actions">
                    <a href="profil.php" class="btn btn-outline">
                        <i class="fas fa-times-circle"></i> Batal
                    </a>
                    <button type="submit" name="ubah" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const button = field.nextElementSibling;
            const icon = button.querySelector('i');

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password strength checker with checklist
        const passwordInput = document.getElementById('password_baru');
        const confirmInput = document.getElementById('konfirmasi_baru');
        const strengthBar = document.querySelector('.strength-bar');
        const strengthLabel = document.getElementById('strength-label');
        const confirmMessage = document.getElementById('confirm-message');

        // Get checklist elements
        const minLengthReq = document.getElementById('req-min-length');
        const hasLowercaseReq = document.getElementById('req-lowercase');
        const hasUppercaseReq = document.getElementById('req-uppercase');
        const hasNumberReq = document.getElementById('req-number');
        const hasSpecialReq = document.getElementById('req-special');

        function updateChecklist(password) {
            // Update icons based on requirements
            let allMet = true;

            // Check minimum length (>= 4)
            if (password.length >= 4) {
                minLengthReq.innerHTML = '<i class="fas fa-check-circle"></i> Minimal 4 karakter';
                minLengthReq.classList.add('requirement-met');
            } else {
                minLengthReq.innerHTML = '<i class="fas fa-circle"></i> Minimal 4 karakter';
                minLengthReq.classList.remove('requirement-met');
                allMet = false;
            }

            // Check lowercase
            if (/[a-z]/.test(password)) {
                hasLowercaseReq.innerHTML = '<i class="fas fa-check-circle"></i> Mengandung huruf kecil (a-z)';
                hasLowercaseReq.classList.add('requirement-met');
            } else {
                hasLowercaseReq.innerHTML = '<i class="fas fa-circle"></i> Mengandung huruf kecil (a-z)';
                hasLowercaseReq.classList.remove('requirement-met');
                allMet = false;
            }

            // Check uppercase
            if (/[A-Z]/.test(password)) {
                hasUppercaseReq.innerHTML = '<i class="fas fa-check-circle"></i> Mengandung huruf besar (A-Z)';
                hasUppercaseReq.classList.add('requirement-met');
            } else {
                hasUppercaseReq.innerHTML = '<i class="fas fa-circle"></i> Mengandung huruf besar (A-Z)';
                hasUppercaseReq.classList.remove('requirement-met');
                allMet = false;
            }

            // Check number
            if (/[0-9]/.test(password)) {
                hasNumberReq.innerHTML = '<i class="fas fa-check-circle"></i> Mengandung angka (0-9)';
                hasNumberReq.classList.add('requirement-met');
            } else {
                hasNumberReq.innerHTML = '<i class="fas fa-circle"></i> Mengandung angka (0-9)';
                hasNumberReq.classList.remove('requirement-met');
                allMet = false;
            }

            // Check special character
            if (/[$@#&!]/.test(password)) {
                hasSpecialReq.innerHTML = '<i class="fas fa-check-circle"></i> Mengandung karakter khusus ($@#&!)';
                hasSpecialReq.classList.add('requirement-met');
            } else {
                hasSpecialReq.innerHTML = '<i class="fas fa-circle"></i> Mengandung karakter khusus ($@#&!)';
                hasSpecialReq.classList.remove('requirement-met');
                allMet = false;
            }

            // Calculate strength
            let strength = 0;
            if (password.length >= 4) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[$@#&!]/.test(password)) strength++;

            let strengthText = '';
            let strengthClass = '';

            switch (strength) {
                case 0:
                case 1:
                    strengthText = 'Lemah';
                    strengthClass = 'weak';
                    break;
                case 2:
                case 3:
                    strengthText = 'Sedang';
                    strengthClass = 'medium';
                    break;
                case 4:
                case 5:
                    strengthText = 'Kuat';
                    strengthClass = 'strong';
                    break;
            }

            strengthBar.className = 'strength-bar ' + strengthClass;
            strengthLabel.innerHTML = strengthText;

            // Change strength label color based on strength
            const strengthSpan = document.getElementById('strength-label');
            if (strengthClass === 'weak') {
                strengthSpan.style.color = 'var(--danger)';
            } else if (strengthClass === 'medium') {
                strengthSpan.style.color = 'var(--secondary)';
            } else if (strengthClass === 'strong') {
                strengthSpan.style.color = 'var(--success)';
            }

            // Update strength bar background color
            if (strengthClass === 'weak') {
                strengthBar.style.background = 'var(--danger)';
            } else if (strengthClass === 'medium') {
                strengthBar.style.background = 'var(--secondary)';
            } else if (strengthClass === 'strong') {
                strengthBar.style.background = 'var(--success)';
            }

            // Add success icon next to strength text when all requirements met
            const strengthIcon = document.getElementById('strength-icon');
            if (allMet && password.length > 0) {
                strengthIcon.innerHTML = '<i class="fas fa-check-circle" style="color: var(--success);"></i>';
            } else {
                strengthIcon.innerHTML = '<i class="fas fa-shield-alt"></i>';
            }

            // Check confirm password
            checkConfirmPassword();
        }

        function checkConfirmPassword() {
            const password = passwordInput.value;
            const confirm = confirmInput.value;

            if (confirm.length > 0) {
                if (password === confirm) {
                    confirmMessage.innerHTML = '<i class="fas fa-check-circle"></i> Password cocok!';
                    confirmMessage.className = 'confirm-message success';
                    // Add green border to confirm input
                    confirmInput.classList.add('success');
                    confirmInput.classList.remove('error');
                } else {
                    confirmMessage.innerHTML = '<i class="fas fa-times-circle"></i> Password tidak cocok!';
                    confirmMessage.className = 'confirm-message error';
                    confirmInput.classList.add('error');
                    confirmInput.classList.remove('success');
                }
            } else {
                confirmMessage.innerHTML = '';
                confirmInput.classList.remove('success', 'error');
            }
        }

        passwordInput.addEventListener('input', function () {
            updateChecklist(this.value);

            // Add/remove success class based on password strength
            const strength = this.value.length >= 4 && /[a-z]/.test(this.value) && /[A-Z]/.test(this.value) && /[0-9]/.test(this.value);
            if (strength && this.value.length > 0) {
                this.classList.add('success');
            } else {
                this.classList.remove('success');
            }
        });

        confirmInput.addEventListener('input', checkConfirmPassword);

        // Initial check
        updateChecklist(passwordInput.value);

        // Dropdown functionality
        document.addEventListener('DOMContentLoaded', function () {
            const dropdownToggle = document.querySelector('.dropdown-toggle');
            const dropdown = document.querySelector('.dropdown');

            if (dropdownToggle) {
                dropdownToggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    dropdown.classList.toggle('open');
                });
            }

            document.addEventListener('click', function (e) {
                if (dropdown && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('open');
                }
            });
        });
    </script>
</body>

</html>