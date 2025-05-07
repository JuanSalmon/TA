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
      position: relative !important;
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
    position: absolute !important;
    right: 10px;
    top: 70%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #aaa;
    z-index: 2; /* Pastikan ikon berada di atas input */
    }
    #formAlert.alert-success {
    background-color: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
    }
    #formAlert.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
    }
    #formAlert {
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 5px;
    display: none; /* Sembunyikan alert secara default */
    }
    #formAlert:not(.d-none) {
    display: block !important;
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
                <form id="registerForm" >
                  <div class="mb-3">
                    <label for="nama" class="form-label">Username</label>
                    <input type="text" class="form-control" id="nama" name="nama" aria-describedby="textHelp" required>
                  </div>
                  <div class="mb-4 password-container">
                    <label for="Pass" class="form-label">Password</label>
                    <input type="password" class="form-control" id="Pass" name="password" required>
                    <i class="fas fa-eye" id="togglePassword"></i>
                  </div>
                  <!-- <div id="passwordError" class="text-danger mt-1 d-none">Password harus 8 karakter huruf atau angka.</div> -->
                  <div id="formAlert" class="alert d-none" role="alert"></div>
                  <button type="submit" class="btn btn-primary w-100 py-8 mb-4 rounded-2" href="../../..login/index.php">Register</button>
                  <div class="d-flex align-items-center">
                    <p class="fs-4 mb-0 text-dark">Sudah Mendaftar?</p>
                    <a class="text-primary fw-medium ms-2" href="../../../login/index.php">Login</a>
                  </div>
                </form>
              </div>
              <div class="card-footer text-center py-3">
                <div class="row align-items-center justify-content-center">
                  <div class="col-auto">
                    <p class="mb-0">© 2025 Politeknik Negeri Kupang. All rights reserved.</p>
                  </div>
                  <div class="col-auto">
                    <a href="../../../landing/index.php" class="text-primary fw-medium">Kembali ke Beranda</a>
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
  <!-- Import Js Files -->
    <script src="/regis2/assets/js/theme/sidebarmenu.js"></script>
  <script>
    window.onload = function () {
        document.querySelector(".preloader").style.display = "none";
    };
</script>


  <script src="../src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../src/assets/libs/simplebar/dist/simplebar.min.js"></script>
  <script src="../src/assets/js/theme/app.init.js"></script>
  <script src="../src/assets/js/theme/theme.js"></script>
  <script src="../src/assets/js/theme/app.min.js"></script>

  <!-- solar icons -->
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- alert -->
<script>
  // Tangani submit form
  document.getElementById('registerForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Cegah pengiriman form default
    validateForm();
  });

  function validateForm() {
    let isValid = true;

    // Ambil elemen input
    const username = document.getElementById('nama');
    const password = document.getElementById('Pass');

    // Ambil elemen error
    const formAlert = document.getElementById('formAlert');

    // Reset pesan error hanya jika elemen ada
    if (formAlert) {
      formAlert.classList.add('d-none');
      formAlert.classList.remove('alert-success', 'alert-danger');
    }

    // Regex untuk huruf atau angka, 
    const regex = /^[A-Za-z0-9]{8,}$/;

    // Validasi username
    if (!username || username.value.trim() === '') {
      if (formAlert) {
        formAlert.textContent = 'Username tidak boleh kosong.';
        formAlert.classList.remove('d-none');
      }
      showAlert('Username tidak boleh kosong.', 'danger');
      isValid = false;
    } 

    // Validasi password
    if (!password || password.value.trim() === '') {
      if (formAlert) {
        formAlert.textContent = 'Password tidak boleh kosong.';
        formAlert.classList.remove('d-none');
      }
      showAlert('Password tidak boleh kosong.', 'danger');
      isValid = false;
    } else if (!regex.test(password.value.trim())) {
      if (formAlert) {
        //passwordError.textContent = 'Password harus tepat 8 karakter huruf atau angka.';
        formAlert.classList.remove('d-none');
      }
      showAlert('Password harus 8 karakter huruf atau angka.', 'danger');
      isValid = false;
    }

    // Jika validasi klien berhasil, kirim form dengan AJAX
    if (isValid) {
      console.log('Validasi klien lolos, mengirim form ke server'); // Debug
      sendForm();
    }
  }

  // Fungsi untuk mengirim form dengan AJAX
  function sendForm() {
    const form = document.getElementById('registerForm');
    const formData = new FormData(form);

    console.log('Mengirim form ke add_user.php:', Object.fromEntries(formData)); // Debug

    fetch('add_user.php', {
      method: 'POST',
      body: formData
    })
      .then(response => {
        console.log('Respons diterima:', response); // Debug
        if (!response.ok) {
          throw new Error('Respons server tidak OK: ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        console.log('Data JSON:', data); // Debug
        // Tangani struktur JSON
        let message = data.message || data.error || 'Terjadi kesalahan tidak diketahui.';
        let isSuccess = data.success === true;

        if (isSuccess) {
          showAlert(message, 'success');
          form.reset();
          // Opsional: Redirect setelah 2 detik
          setTimeout(() => window.location.href = '../../../login/index.php', 1000);
        } else {
          showAlert(message, 'danger');
        }
      })
      .catch(error => {
        console.error('Gagal mengirim form:', error);
        showAlert('Terjadi kesalahan : ' + error.message, 'danger');
      });
  }

  // Fungsi untuk menampilkan alert di dalam form
  function showAlert(message, type) {
    console.log('showAlert dipanggil:', { message, type }); // Debug

    try {
      const formAlert = document.getElementById('formAlert');
      if (!formAlert) {
        console.error('Elemen formAlert tidak ditemukan saat showAlert!');
        // Fallback: Gunakan alert browser
        alert(`${type === 'success' ? 'Sukses' : 'Error'}: ${message}`);
        return;
      }

      // Set pesan dan tipe alert
      formAlert.textContent = message;
      formAlert.classList.remove('d-none', 'alert-success', 'alert-danger');
      formAlert.classList.add(`alert-${type}`);

      // Scroll ke alert agar terlihat
      formAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

      // Hilangkan alert setelah 5 detik
      setTimeout(() => {
        formAlert.classList.add('d-none');
      }, 5000);
    } catch (error) {
      console.error('Gagal menampilkan alert:', error);
      // Fallback: Gunakan alert browser
      alert(`${type === 'success' ? 'Sukses' : 'Error'}: ${message}`);
    }
  }
</script>
<!-- alert end -->

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