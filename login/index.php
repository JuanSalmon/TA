<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" type="image/png" href="../src/assets/images/logos/logo-pnk.png" />
  <link rel="stylesheet" href="../src/assets/css/styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    .password-container {
      position: relative;
      width: 100%;
      max-width: 500px;
    }
    .password-container input {
      width: 100%;
      padding: 10px;
      padding-right: 40px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 5px;
      box-sizing: border-box;
    }
    .password-container i {
      position: absolute;
      right: 10px;
      top: 70%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #aaa;
      z-index: 2;
    }
  </style>
  <title>Login Broker</title>
</head>
<body>
  <div class="preloader">
    <img src="../src/assets/images/logos/logo-pnk.png" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper" class="auth-customizer-none">
    <div class="position-relative overflow-hidden radial-gradient min-vh-100 w-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3 auth-card">
            <div class="card mb-0">
              <div class="card-body">
                <a href="/landing/index.php" class="text-nowrap logo-img text-center d-block mb-1 w-100">
                  <img src="../src/assets/images/logos/logo-pnk.png" class="dark-logo" alt="Logo-Dark img-fluid" style="width: 200px; height: auto;" />
                  <p class="fs-4 mb-0 text-dark">Login User Broker PNK</p>
                  <?php
                    if (session_status() === PHP_SESSION_NONE) {
                      session_start();
                    }
                    error_log("[" . date('Y-m-d H:i:s') . "] Memuat login/index.php, sesi: " . print_r($_SESSION, true));
                    if (isset($_SESSION['error'])) {
                      echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
                      error_log("[" . date('Y-m-d H:i:s') . "] Menampilkan error dari sesi: " . $_SESSION['error']);
                      unset($_SESSION['error']);
                    } elseif (isset($_GET['error'])) {
                      $error_message = match ($_GET['error']) {
                        'user_not_found' => 'Username atau email tidak ditemukan',
                        'password_mismatch' => 'Kata sandi salah',
                        'not_allowed' => 'Akun Anda tidak diizinkan',
                        'db_error' => 'Koneksi database gagal. Silakan hubungi administrator.',
                        'config_error' => 'Konfigurasi sistem tidak valid',
                        'empty_input' => 'Username dan kata sandi harus diisi',
                        'not_logged_in' => 'Silakan login terlebih dahulu',
                        default => 'Terjadi kesalahan saat login'
                      };
                      echo '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
                      error_log("[" . date('Y-m-d H:i:s') . "] Menampilkan error dari URL: " . $error_message);
                    }
                  ?>
                </a>
                <form method="POST" action="/config/auth.php">
                  <div class="mb-3">
                    <label for="Username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="Username" name="username" aria-describedby="emailHelp" required>
                  </div>
                  <div class="mb-4 password-container">
                    <label for="Password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="Password" name="password" style="padding-right: 40px;" required>
                    <i class="fas fa-eye" id="togglePassword"></i>
                  </div>
                  <button class="btn btn-primary w-100 py-8 mb-4 rounded-2" id="Login" name="login">Sign In</button> 
                  <div class="d-flex align-items-center justify-content-center">
                    <p class="fs-4 mb-0 fw-medium">Belum Mendaftar?</p>
                    <a class="text-primary fw-medium ms-2" href="/regis/index.php">Daftar disini</a>
                  </div>
                  <div class="d-flex align-items-center justify-content-center mt-2">
                    <a class="text-primary fw-medium" href="/forgot_password.php">Lupa Kata Sandi?</a>
                  </div>
                </form>
              </div>
              <div class="card-footer text-center py-3">
                <div class="row align-items-center justify-content-center">
                  <div class="col-auto">
                    <p class="mb-0">© 2025 Politeknik Negeri Kupang.</p>
                  </div>
                  <div class="col-auto">
                    <a href="/landing/index.php" class="text-primary fw-medium">Kembali ke Beranda</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script>
      function handleColorTheme(e) {
        document.documentElement.setAttribute("data-color-theme", e);
      }
    </script>
    <div class="dark-transparent sidebartoggler"></div>
    <script src="../src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../src/assets/libs/simplebar/dist/simplebar.min.js"></script>
    <script src="../src/assets/js/theme/app.init.js"></script>
    <script src="../src/assets/js/theme/theme.js"></script>
    <script src="../src/assets/js/theme/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script>
      const togglePassword = document.getElementById('togglePassword');
      const passwordField = document.getElementById('Password');
      togglePassword.addEventListener('click', function () {
        if (passwordField.type === 'password') {
          passwordField.type = 'text';
          togglePassword.classList.remove('fa-eye');
          togglePassword.classList.add('fa-eye-slash');
        } else {
          passwordField.type = 'password';
          togglePassword.classList.remove('fa-eye-slash');
          togglePassword.classList.add('fa-eye');
        }
      });
    </script>
</body>
</html>