<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon icon-->
  <link rel="shortcut icon" type="image/png" href="../src/assets/images/logos/logo-pnk.png" />

  <!-- Core Css -->
  <link rel="stylesheet" href="../src/assets/css/styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <style>
        /* Styling untuk input dan tombol mata */
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
      box-sizing: border-box; /* Pastikan padding tidak memengaruhi lebar */
    }
    .password-container i {
    position: absolute;
    right: 10px;
    top: 70%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #aaa;
    z-index: 2; /* Pastikan ikon berada di atas input */
    }
  </style>

  <title>Register Broker</title>
</head>

<body>
  <!-- Preloader -->
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
                <a href="../../../landing/index.php" class="text-nowrap small-icon text-center d-block mb-1 w-100 " >
                  <img src="../src/assets/images/logos/logo-pnk.png" class="dark-logo img-fluid" alt="Logo-Dark" style="width: 200px; height: auto;" />
                  <p class="fs-4 mb-0 text-dark">Registrasi User Broker PNK</p>
                </a>
                <form action="add_user.php" method="POST">
                  <div class="mb-3">
                    <label for="nama" class="form-label">Username</label>
                    <input type="text" class="form-control" id="nama" name="nama" aria-describedby="textHelp" required>
                  </div>
                  <div class="mb-4 password-container">
                    <label for="Pass" class="form-label">Password</label>
                    <input type="password" class="form-control" id="Pass" name="password" required>
                    <i class="fas fa-eye" id="togglePassword"></i>
                  </div>
                  <button type="submit" class="btn btn-primary w-100 py-8 mb-4 rounded-2" href="../../..login/index.php">Register</button>
                  <div class="d-flex align-items-center">
                    <p class="fs-4 mb-0 text-dark">Sudah Mendaftar?</p>
                    <a class="text-primary fw-medium ms-2" href="../../../login/index.php">Login</a>
                  </div>
                </form>
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
  <!-- Import Js Files -->
    <script src="/regis2/assets/js/theme/sidebarmenu.js"></script>
  <script>
    window.onload = function () {
        document.querySelector(".preloader").style.display = "none";
    };
</script>
<script>
  function validateForm() {
    let isValid = true;

    // Ambil elemen input
    const username = document.getElementById('nama');
    const password = document.getElementById('Pass');

    // Ambil elemen error
    const namaError = document.getElementById('namaError');
    const passwordError = document.getElementById('passwordError');

    // Reset error message
    namaError.classList.add('d-none');
    passwordError.classList.add('d-none');

    // Validasi username
    if (username.value.trim() === '') {
      namaError.classList.remove('d-none');
      isValid = false;
    }

    // Validasi password
    if (password.value.trim() === '') {
      passwordError.classList.remove('d-none');
      isValid = false;
    }

    return isValid; // Jika isValid false, form tidak akan dikirim
  }
</script>

  <script src="../src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../src/assets/libs/simplebar/dist/simplebar.min.js"></script>
  <script src="../src/assets/js/theme/app.init.js"></script>
  <script src="../src/assets/js/theme/theme.js"></script>
  <script src="../src/assets/js/theme/app.min.js"></script>

  <!-- solar icons -->
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

  <script>
        // JavaScript untuk menampilkan/menyembunyikan password
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('Pass');

        togglePassword.addEventListener('click', function () {
            if (passwordField.type === 'password') {
                passwordField.type = 'text'; // Tampilkan password
                togglePassword.classList.remove('fa-eye');
                togglePassword.classList.add('fa-eye-slash'); // Ubah ikon ke mata tertutup
            } else {
                passwordField.type = 'password'; // Sembunyikan password
                togglePassword.classList.remove('fa-eye-slash');
                togglePassword.classList.add('fa-eye'); // Ubah ikon ke mata terbuka
            }
        });
  </script>
</body>

</html>