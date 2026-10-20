<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" type="image/png" href="../src/assets/images/logos/logo-pnk.png" />
  <link rel="stylesheet" href="../src/assets/css/styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <title>Lupa Kata Sandi</title>
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
                  <p class="fs-4 mb-0 text-dark">Reset Kata Sandi</p>
                  <?php
                    if (session_status() === PHP_SESSION_NONE) {
                      session_start();
                    }
                    if (isset($_SESSION['message'])) {
                      echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['message']) . '</div>';
                      unset($_SESSION['message']);
                    } elseif (isset($_SESSION['error'])) {
                      echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
                      unset($_SESSION['error']);
                    }
                  ?>
                </a>
                <form method="POST" action="/config/forgot_password.php">
                  <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" required>
                  </div>
                  <button class="btn btn-primary w-100 py-8 mb-4 rounded-2" type="submit">Kirim Link Reset</button>
                  <div class="d-flex align-items-center justify-content-center">
                    <a class="text-primary fw-medium" href="/login/index.php">Kembali ke Login</a>
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
</body>
</html>