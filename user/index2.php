<?php
session_start();

// Include file konfigurasi database
$config = include __DIR__ . '/../config/config.php'; // Sesuaikan path ke config.php

// Include class Database
require_once __DIR__ . '/../config/database.php'; // Sesuaikan path ke file Database.php

try {
    // Buat objek Database
    $database = new \Jubox\Web\Database($config);
    // Dapatkan koneksi PDO
    $koneksi = $database->getConnection();
    $query = "SELECT * FROM user_db WHERE username = :username";
    $stmt = $koneksi->prepare($query);
    $stmt->execute(['username' => $_SESSION['username']]);
    
    if ($stmt->rowCount() == 0) {
      // Jika username tidak ada di tabel admin, redirect ke login atau halaman lain
      header("Location: ../login/login.php");
      exit;
  }

} catch (\PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="dark" data-color-theme="Blue_Theme" data-layout="vertical">

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
    .preloader img {
      transform: scale(5); /* Memperbesar gambar 5x lipat */
    }
    .password-container {
      position: relative;
      display: inline-block;
    }
    .password-container i {
      position: absolute;
      right: -25px;
      top: 35%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #aaa;
    }
  </style>
  

<title>User Dashboard Broker</title>
</head>

<body class="link-sidebar">
  <!-- Preloader -->
  <div class="preloader">
    <img src="../src/assets/images/logos/logo-pnk.png" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper">
    <!-- Sidebar Start -->
    <aside class="left-sidebar with-vertical">
      <div><!-- ---------------------------------- -->
        <!-- Start Vertical Layout Sidebar -->
        <!-- ---------------------------------- -->
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <a href="index.php" class="text-nowrap logo-img">
            <img src="../src/assets/images/logos/dark-logo.svg" class="dark-logo" alt="Logo-Dark" />
            <img src="../src/assets/images/logos/light-logo.svg" class="light-logo" alt="Logo-light" />
          </a>
          <a href="javascript:void(0)" class="sidebartoggler ms-auto text-decoration-none fs-5 d-block d-xl-none">
            <i class="ti ti-x"></i>
          </a>
        </div>

        <nav class="sidebar-nav scroll-sidebar" data-simplebar>
          <ul id="sidebarnav">
            <!-- ---------------------------------- -->
            <!-- Home -->
            <!-- ---------------------------------- -->
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Home</span>
            </li>
            <!-- ---------------------------------- -->
            <!-- Dashboard -->
            <!-- ---------------------------------- -->
            <li class="sidebar-item">
              <a class="sidebar-link" href="" id="get-url" aria-expanded="false">
                <span>
                  <i class="ti ti-aperture"></i>
                </span>
                <span class="hide-menu">Home</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="/user/index2.php" aria-expanded="false">
                <span>
                  <i class="ti ti-shopping-cart"></i>
                </span>
                <span class="hide-menu">Token</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="/user/index3.php" aria-expanded="false">
                <span>
                  <i class="ti ti-currency-dollar"></i>
                </span>
                <span class="hide-menu">ok3</span>
              </a>
            </li>
          </ul>
        </nav>
        <div class="fixed-profile p-3 mx-4 mb-2 bg-secondary-subtle rounded mt-3">
          <div class="hstack gap-3">
            <div class="john-title">
              <h6 class="mb-0 fs-4 fw-semibold"><?php echo $_SESSION['username']; ?></h6>
            </div>
            <button class="border-0 bg-transparent text-primary ms-auto" tabindex="0" type="button" aria-label="logout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="logout" href="../../logout/logout.php">
              <i class="ti ti-power fs-6"></i>
            </button>
          </div>
        </div>
      </div>
    </aside>
    <!--  Sidebar End -->
    <div class="page-wrapper">
      <!--  Header Start -->
      <header class="topbar">
        <div class="with-vertical"><!-- ---------------------------------- -->
          <!-- Start Vertical Layout Header -->
          <!-- ---------------------------------- -->
          <nav class="navbar navbar-expand-lg p-0">
            <ul class="navbar-nav">
              <li class="nav-item nav-icon-hover-bg rounded-circle ms-n2">
                <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                  <i class="ti ti-menu-2"></i>
                </a>
              </li>
            </ul>
            <div class="d-block d-lg-none py-4">
              <a href="../dark/index.html" class="text-nowrap logo-img">
                <img src="../src/assets/images/logos/dark-logo.svg" class="dark-logo" alt="Logo-Dark" />
                <img src="../src/assets/images/logos/light-logo.svg" class="light-logo" alt="Logo-light" />
              </a>
            </div>
            <a class="navbar-toggler nav-icon-hover-bg rounded-circle p-0 mx-0 border-0" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
              <i class="ti ti-dots fs-7"></i>
            </a>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
              <div class="d-flex align-items-center justify-content-between">
                <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-center">
                  <!-- ------------------------------- -->
                  <!-- Theme Change -->
                  <!-- ------------------------------- -->
                  <li class="nav-item nav-icon-hover-bg rounded-circle">
                    <a class="nav-link moon dark-layout" href="javascript:void(0)">
                      <i class="ti ti-moon moon"></i>
                    </a>
                    <a class="nav-link sun light-layout" href="javascript:void(0)">
                      <i class="ti ti-sun sun"></i>
                    </a>
                  </li>
                  <!-- ------------------------------- -->
                  <!-- end theme change -->
                  <!-- ------------------------------- -->

                  <!-- ------------------------------- -->
                  <!-- start notification Dropdown -->
                  <!-- ------------------------------- -->
                  <li class="nav-item nav-icon-hover-bg rounded-circle dropdown">
                    <a class="nav-link position-relative" href="javascript:void(0)" id="drop2" aria-expanded="false">
                      <i class="ti ti-bell-ringing"></i>
                      <div class="notification bg-primary rounded-circle"></div>
                    </a>
                  </li>
                  <!-- ------------------------------- -->
                  <!-- end notification Dropdown -->
                  <!-- ------------------------------- -->
                </ul>
              </div>
            </div>
          </nav>
          <!-- ---------------------------------- -->
          <!-- End Vertical Layout Header -->
          <!-- ---------------------------------- -->

          <!-- ------------------------------- -->
          <!-- apps Dropdown in Small screen -->
          <!-- ------------------------------- -->
          <!--  Mobilenavbar -->
          
        </div>
        <div class="app-header with-horizontal">
          <nav class="navbar navbar-expand-xl container-fluid p-0">
            <ul class="navbar-nav align-items-center">
              <li class="nav-item nav-icon-hover-bg rounded-circle d-flex d-xl-none ms-n2">
                <a class="nav-link sidebartoggler" id="sidebarCollapse" href="javascript:void(0)">
                  <i class="ti ti-menu-2"></i>
                </a>
              </li>
              <li class="nav-item d-none d-xl-block">
                <a href="../dark/index.html" class="text-nowrap nav-link">
                  <img src="../src/assets/images/logos/dark-logo.svg" class="dark-logo" width="180" alt="modernize-img" />
                  <img src="../src/assets/images/logos/light-logo.svg" class="light-logo" width="180" alt="modernize-img" />
                </a>
              </li>
              <li class="nav-item nav-icon-hover-bg rounded-circle d-none d-xl-flex">
                <a class="nav-link" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#exampleModal">
                  <i class="ti ti-search"></i>
                </a>
              </li>
            </ul>
            <div class="d-block d-xl-none">
              <a href="../dark/index.html" class="text-nowrap nav-link">
                <img src="../src/assets/images/logos/dark-logo.svg" width="180" alt="modernize-img" />
              </a>
            </div>
            <a class="navbar-toggler nav-icon-hover-bg rounded-circle p-0 mx-0 border-0" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
              <span class="p-2">
                <i class="ti ti-dots fs-7"></i>
              </span>
            </a>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
              <div class="d-flex align-items-center justify-content-between px-0 px-xl-8">
                <a href="javascript:void(0)" class="nav-link round-40 p-1 ps-0 d-flex d-xl-none align-items-center justify-content-center" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobilenavbar" aria-controls="offcanvasWithBothOptions">
                  <i class="ti ti-align-justified fs-7"></i>
                </a>
                <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-center">
                  <!-- ------------------------------- -->
                  <!-- start language Dropdown -->
                  <!-- ------------------------------- -->
                  <li class="nav-item nav-icon-hover-bg rounded-circle">
                    <a class="nav-link moon dark-layout" href="javascript:void(0)">
                      <i class="ti ti-moon moon"></i>
                    </a>
                    <a class="nav-link sun light-layout" href="javascript:void(0)">
                      <i class="ti ti-sun sun"></i>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </nav>
        </div>
      </header>
      <!--  Header End -->
      
      <div class="body-wrapper">
        <div class="container-fluid">
          <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
              <div class="row align-items-center">
                <div class="col-9">
                  <h4 class="fw-semibold mb-8">Halo <?php echo $_SESSION['username']; ?></h4> <!--buat sesuai nama yang ada di database--> 
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="../dark/index.html">Home</a>
                      </li>
                    </ol>
                  </nav>
                </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card w-100 position-relative overflow-hidden">
            <div class="px-4 py-3 border-bottom">
              <h4 class="card-title mb-0">User</h4>
            </div>
            <div class="card-body p-4">
              <div class="table-responsive mb-4 border rounded-1">
                <table class="table text-nowrap mb-0 align-middle table-striped w-100">
                  <thead class="text-dark fs-4">
                    <tr>
                      <th>
                        <h6 class="fs-4 fw-semibold mb-0 ms-3">User</h6>
                      </th>
                      <th>
                        <h6 class="fs-4 fw-semibold mb-0">Password</h6>
                      </th>
                      <th>
                        <h6 class="fs-4 fw-semibold mb-0 ms-2">Status</h6>
                      </th>
                      <th>
                        <h6 class="fs-4 fw-semibold mb-0 ms-2">Allow</h6>
                      </th>
                      <th>
                        <h6 class="fs-4 fw-semibold mb-0 ms-5">Action</h6>
                      </th>
                    </tr>
                  </thead>
                  <tbody id="user-table-body">
                        <?php foreach ($users as $user): ?>
                            <tr data-user-id="<?php echo $user['id']; ?>">
                                <td>
                                    <h6 class="fw-normal mb-0 ms-3"><?php echo htmlspecialchars($user['name']); ?></h6>
                                </td>
                                <td>
                                  <div class="password-container" >
                                  <h6 class="password-text" data-password="<?php echo htmlspecialchars($user['password']); ?>">********</h6> <!-- Password masked -->
                                    <i class="fas fa-eye toggle-password"></i>
                                  </div>
                                  </td>
                                <td>
                                    <h6 class="fw-normal mb-0 ms-2"><?php echo $user['allowed'] ? 'Allowed' : 'Not Allowed'; ?></h6>
                                </td>
                                <td>
                                    <form action="update_status.php" method="POST" style="margin: 0;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="<?php echo $user['allowed'] ? 'disallow' : 'allow'; ?>">
                                        <button 
                                            type="submit" 
                                            class="btn btn-sm <?php echo $user['allowed'] ? 'btn-danger' : 'btn-success'; ?>"
                                        >
                                            <?php echo $user['allowed'] ? 'Disallow' : 'Allow'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="delete_user.php" method="POST" style="display: inline-block; margin-left: 5px;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <!-- end tables -->
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
  <script src="../src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../src/assets/libs/simplebar/dist/simplebar.min.js"></script>
  <script src="../src/assets/js/theme/app.dark.init.js"></script>
  <script src="../src/assets/js/theme/theme.js"></script>
  <script src="../src/assets/js/theme/app.min.js"></script>
  <script src="../src/assets/js/theme/sidebarmenu.js"></script>

  <!-- solar icons -->
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

  <script>
        // JavaScript untuk menampilkan/menyembunyikan password
        document.querySelectorAll('.toggle-password').forEach(function (icon) {
            icon.addEventListener('click', function () {
                const passwordText = this.previousElementSibling; // h6 yang berisi teks password
                const originalPassword = passwordText.getAttribute('data-password'); // Ambil password asli
                const isMasked = passwordText.textContent === '********'; // Cek apakah saat ini tertutup

                if (isMasked) {
                    passwordText.textContent = originalPassword; // Tampilkan password asli
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash'); // Ubah ikon ke mata tertutup
                } else {
                    passwordText.textContent = '********'; // Tampilkan asterisk
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye'); // Ubah ikon ke mata terbuka
                }
            });
        });
    </script>

</body>

</html>