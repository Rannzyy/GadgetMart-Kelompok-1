<?php
session_start();
include '../koneksi.php';

// Check if user is already logged in
if (isset($_SESSION['id_user'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../index.php");
    }
    exit;
}

// Login Logic
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
    $data = mysqli_fetch_assoc($query);

    if ($data) {
        if ($password === $data['password']) {
            $_SESSION['id_user'] = $data['id_user'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['role'] = $data['role'];

            if ($data['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../index.php");
            }
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}

// Registration Logic
if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $confirm_password = $_POST['confirm_password'];
    $role = 'customer';

    $errors = [];

    if (empty($username)) {
        $errors[] = "Username harus diisi";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter";
    }
    if (empty($email)) {
        $errors[] = "Email harus diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    if (empty($password)) {
        $errors[] = "Password harus diisi";
    } elseif (strlen($password) < 4) {
        $errors[] = "Password minimal 4 karakter";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok";
    }

    $check_username = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check_username) > 0) {
        $errors[] = "Username sudah digunakan!";
    }

    $check_email = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $errors[] = "Email sudah terdaftar!";
    }

    if (empty($errors)) {
        $insert = mysqli_query($koneksi, "INSERT INTO users (username, password, email, role) VALUES ('$username', '$password', '$email', '$role')");
        if ($insert) {
            header("Location: ?form=login&success=1");
            exit;
        } else {
            $reg_error = "Terjadi kesalahan sistem!";
        }
    } else {
        $reg_error = implode("<br>", $errors);
    }
}

// Logout Logic - Redirect to index.php
if (isset($_GET['logout'])) {
    // Clear all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to index.php (homepage)
    header("Location: ../index.php");
    exit;
}



$active_form = isset($_GET['form']) && $_GET['form'] == 'register' ? 'register' : 'login';
$show_success = isset($_GET['success']) && $_GET['success'] == 1;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GadgetMart.id | Login & Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>
    <div class="container">
        <div class="visual-section">
            <div class="logo">
                <i class="fas fa-laptop-code"></i>
                <span>GadgetMart</span>
            </div>
            <p class="slogan">Beli laptop dan smartphone terbaik dengan harga terjangkau</p>
            <div class="illustration-container">
                <img src="https://cdn-icons-png.flaticon.com/512/1055/1055687.png" alt="Login Illustration"
                    class="illustration"
                    onerror="this.onerror=null; this.src='https://cdn-icons-png.flaticon.com/512/295/295128.png'; this.style.display='none'; this.parentElement.querySelector('.fallback-icon').style.display='block';">
                <i class="fas fa-laptop-code fallback-icon" style="display: none;"></i>
            </div>
        </div>

        <div class="form-section">
            <div class="form-container">
                <!-- Login Form -->
                <div class="form-wrapper <?= $active_form == 'login' ? '' : 'hidden' ?>" id="login-form">
                    <h2 class="form-title">Masuk ke Akun</h2>

                    <?php if (isset($error)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($show_success && $active_form == 'login'): ?>
                        <div class="success-message">
                            <i class="fas fa-check-circle"></i> Registrasi berhasil! Silakan login.
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="loginForm">
                        <div class="form-group">
                            <label for="login-username">
                                <i class="fas fa-user"></i> Username
                            </label>
                            <input type="text" id="login-username" name="username" class="form-control"
                                placeholder="Masukkan username" required>
                        </div>

                        <div class="form-group password-wrapper">
                            <label for="login-password">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <input type="password" id="login-password" name="password" class="form-control"
                                placeholder="Masukkan password" required>
                            <i class="fas fa-eye toggle-password" data-target="login-password"></i>
                        </div>

                        <button type="submit" name="login" class="btn btn-primary" id="loginBtn">
                            <i class="fas fa-sign-in-alt"></i> Masuk
                        </button>

                        <div class="divider">
                            <span>atau</span>
                        </div>

                        <div class="social-login">
                            <button type="button" class="btn btn-outline"
                                onclick="window.location.href='?form=register'">
                                <i class="fas fa-user-plus"></i> Buat Akun Baru
                            </button>
                        </div>

                        <div class="logout-wrapper">
                            <a href="?logout=1" class="btn-logout" id="stayLogoutBtn">
                                <i class="fas fa-sign-out-alt"></i> Stay Logout
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Register Form -->
                <div class="form-wrapper <?= $active_form == 'register' ? '' : 'hidden' ?>" id="register-form">
                    <h2 class="form-title">Daftar Akun Baru</h2>

                    <?php if (isset($reg_error) && $active_form == 'register'): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i> <?= $reg_error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="?form=register" id="registerForm">
                        <div class="form-group">
                            <label for="register-username">
                                <i class="fas fa-user"></i> Username
                            </label>
                            <input type="text" id="register-username" name="username" class="form-control"
                                placeholder="Username (min. 3 karakter)" required minlength="3">
                        </div>

                        <div class="form-group">
                            <label for="register-email">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" id="register-email" name="email" class="form-control"
                                placeholder="Email" required>
                        </div>

                        <div class="form-group password-wrapper">
                            <label for="register-password">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <input type="password" id="register-password" name="password" class="form-control"
                                placeholder="Minimal 4 karakter" required minlength="4">
                            <i class="fas fa-eye toggle-password" data-target="register-password"></i>
                        </div>

                        <div class="form-group password-wrapper">
                            <label for="confirm-password">
                                <i class="fas fa-check-circle"></i> Konfirmasi Password
                            </label>
                            <input type="password" id="confirm-password" name="confirm_password" class="form-control"
                                placeholder="Ulangi password" required>
                            <i class="fas fa-eye toggle-password" data-target="confirm-password"></i>
                        </div>

                        <button type="submit" name="register" class="btn btn-primary" id="registerBtn">
                            <i class="fas fa-user-plus"></i> Daftar Sekarang
                        </button>

                        <div class="divider">
                            <span>atau</span>
                        </div>

                        <div class="social-login">
                            <button type="button" class="btn btn-outline" onclick="window.location.href='?form=login'">
                                <i class="fas fa-sign-in-alt"></i> Sudah Punya Akun? Login
                            </button>
                        </div>

                        <div class="logout-wrapper">
                            <a href="?logout=1" class="btn-logout" id="stayLogoutBtn2">
                                <i class="fas fa-sign-out-alt"></i> Stay Logout
                            </a>
                        </div>

                        <div class="register-note">
                            <i class="fas fa-shield-alt"></i> Dengan mendaftar, Anda menyetujui Syarat & Ketentuan
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Stay Logout - langsung redirect ke index.php tanpa konfirmasi
        document.getElementById('stayLogoutBtn')?.addEventListener('click', function (e) {
            e.preventDefault();
            window.location.href = '?logout=1';
        });

        document.getElementById('stayLogoutBtn2')?.addEventListener('click', function (e) {
            e.preventDefault();
            window.location.href = '?logout=1';
        });

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const passwordField = document.getElementById(targetId);
                if (passwordField) {
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    this.classList.toggle('fa-eye-slash');
                }
            });
        });

        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.error-message, .success-message, .info-message').forEach(msg => {
                msg.style.transition = 'opacity 0.5s';
                msg.style.opacity = '0';
                setTimeout(() => msg.remove(), 500);
            });
        }, 5000);

        // Register form validation
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', function (e) {
                const password = document.getElementById('register-password').value;
                const confirm = document.getElementById('confirm-password').value;

                if (password.length < 4) {
                    e.preventDefault();
                    alert('Password minimal 4 karakter!');
                } else if (password !== confirm) {
                    e.preventDefault();
                    alert('Konfirmasi password tidak cocok!');
                } else {
                    const btn = document.getElementById('registerBtn');
                    btn.classList.add('btn-loading');
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendaftar...';
                }
            });
        }

        // Login form loading state
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function () {
                const btn = document.getElementById('loginBtn');
                btn.classList.add('btn-loading');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Masuk...';
            });
        }
    </script>
</body>

</html>