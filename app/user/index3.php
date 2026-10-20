<?php
namespace Jubox\Web;

session_start();
error_log("[" . date('Y-m-d H:i:s') . "] Memuat index3.php, path: " . __FILE__ . ", sesi awal: " . print_r($_SESSION, true));

// Periksa sesi
if (!isset($_SESSION['name']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] === true) {
    error_log("[" . date('Y-m-d H:i:s') . "] Akses ditolak untuk /user/index3.php: " . ($_SESSION['name'] ?? 'tidak ada') . ", is_admin: " . ($_SESSION['is_admin'] ?? 'tidak ada'));
    $_SESSION['error'] = $_SESSION['is_admin'] === true ? 'Admin tidak dapat mengakses dashboard user' : 'Silakan login terlebih dahulu';
    header("Location: /login/index.php?error=" . ($_SESSION['is_admin'] === true ? 'access_denied' : 'not_logged_in'));
    exit();
}

if (!isset($_SESSION['allowed']) || !$_SESSION['allowed']) {
    error_log("[" . date('Y-m-d H:i:s') . "] User tidak diizinkan: " . $_SESSION['name'] . ", allowed: " . ($_SESSION['allowed'] ?? 'tidak ada'));
    $_SESSION['error'] = 'Akun Anda tidak diizinkan untuk mengakses dashboard';
    header("Location: /login/index.php?error=not_allowed");
    exit();
}

// Muat konfigurasi
$configResult = include __DIR__ . '/../config/config.php';
if (!is_array($configResult) || !isset($configResult['success']) || !$configResult['success']) {
    error_log("[" . date('Y-m-d H:i:s') . "] Konfigurasi tidak valid: " . print_r($configResult, true));
    $_SESSION['error'] = 'Konfigurasi sistem tidak valid';
    header("Location: /login/index.php?error=config_error");
    exit();
}
$config = $configResult['config'];

// Muat kelas Database
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database($config);
    $koneksi = $database->getConnection();
    // Set zona waktu ke WITA
    $koneksi->exec("SET time_zone = '+08:00'");
    // Verifikasi user
    $stmt = $koneksi->prepare("SELECT id, name, allowed FROM user WHERE name = ? LIMIT 1");
    $stmt->execute([$_SESSION['name']]);
    $user = $stmt->fetch(\PDO::FETCH_ASSOC);
    if (!$user) {
        error_log("[" . date('Y-m-d H:i:s') . "] User tidak ditemukan di database: " . $_SESSION['name']);
        $_SESSION['error'] = 'Pengguna tidak ditemukan';
        header("Location: /login/index.php?error=user_not_found");
        exit();
    }
    if (!$user['allowed']) {
        error_log("[" . date('Y-m-d H:i:s') . "] User tidak diizinkan di database: " . $_SESSION['name']);
        $_SESSION['error'] = 'Akun Anda tidak diizinkan';
        header("Location: /login/index.php?error=not_allowed");
        exit();
    }
    // Sinkronisasi sesi
    $_SESSION['allowed'] = $user['allowed'];
    $_SESSION['email'] = $user['email'] ?? $_SESSION['email'] ?? '';
} catch (\PDOException $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Koneksi database gagal: " . $e->getMessage());
    $_SESSION['error'] = 'Koneksi database gagal: ' . $e->getMessage();
    header("Location: /login/index.php?error=db_error");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="dark" data-color-theme="Blue_Theme" data-layout="vertical">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" type="image/png" href="../src/assets/images/logos/logo-pnk.png" />
    <link rel="stylesheet" href="../src/assets/css/styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        .preloader img { transform: scale(5); }
        .form-container { max-width: 600px; margin: 0 auto; }
    </style>
    <title>Request Database Baru</title>
</head>
<body class="link-sidebar">
    <div class="preloader">
        <img src="../src/assets/images/logos/logo-pnk.png" alt="loader" class="lds-ripple img-fluid" />
    </div>
    <div id="main-wrapper">
        <aside class="left-sidebar with-vertical">
            <div>
                <div class="brand-logo d-flex align-items-center justify-content-between">
                    <a href="/user/index.php" class="text-nowrap logo-img">
                        <img src="../src/assets/images/logos/logo-pnk.png" width="50" class="dark-logo" alt="Logo-Dark" />
                        <img src="../src/assets/images/logos/logo-pnk.png" width="50" class="light-logo" alt="Logo-light" />
                    </a>
                    <a href="javascript:void(0)" class="sidebartoggler ms-auto text-decoration-none fs-5 d-block d-xl-none">
                        <i class="ti ti-x"></i>
                    </a>
                </div>
                <nav class="sidebar-nav scroll-sidebar" data-simplebar>
                    <ul id="sidebarnav">
                        <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                            <span class="hide-menu">Home</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="/user/index.php" aria-expanded="false">
                                <span><i class="ti ti-aperture"></i></span>
                                <span class="hide-menu">Home</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="/user/index2.php" aria-expanded="false">
                                <span><i class="ti ti-shopping-cart"></i></span>
                                <span class="hide-menu">Topik</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="/user/index3.php" aria-expanded="false">
                                <span><i class="ti ti-database"></i></span>
                                <span class="hide-menu">Database</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <div class="fixed-profile p-3 mx-4 mb-2 bg-secondary-subtle rounded mt-3">
                    <div class="hstack gap-3">
                        <div class="john-title">
                            <h6 class="mb-0 fs-4 fw-semibold"><?php echo htmlspecialchars($_SESSION['name']); ?></h6>
                        </div>
                        <button class="border-0 bg-transparent text-primary ms-auto" tabindex="0" type="button" aria-label="logout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="logout" onclick="confirmLogout()">
                            <i class="ti ti-power fs-6"></i>
                        </button>
                    </div>
                </div>
            </div>
        </aside>
        <div class="page-wrapper">
            <header class="topbar">
                <div class="with-vertical">
                    <nav class="navbar navbar-expand-lg p-0">
                        <ul class="navbar-nav">
                            <li class="nav-item nav-icon-hover-bg rounded-circle ms-n2">
                                <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                                    <i class="ti ti-menu-2"></i>
                                </a>
                            </li>
                        </ul>
                        <a class="navbar-toggler nav-icon-hover-bg rounded-circle p-0 mx-0 border-0" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                            <i class="ti ti-dots fs-7"></i>
                        </a>
                        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                            <div class="d-flex align-items-center justify-content-between">
                                <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-center">
                                    <li class="nav-item nav-icon-hover-bg rounded-circle">
                                        <a class="nav-link moon dark-layout" href="javascript:void(0)">
                                            <i class="ti ti-moon moon"></i>
                                        </a>
                                        <a class="nav-link sun light-layout" href="javascript:void(0)">
                                            <i class="ti ti-sun sun"></i>
                                        </a>
                                    </li>
                                    <li class="nav-item nav-icon-hover-bg rounded-circle dropdown">
                                        <a class="nav-link position-relative" href="javascript:void(0)" id="drop2" aria-expanded="false">
                                            <i class="ti ti-bell-ringing"></i>
                                            <div class="notification bg-primary rounded-circle"></div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </nav>
                </div>
            </header>
            <div class="body-wrapper">
                <div class="container-fluid">
                    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
                        <div class="card-body px-4 py-3">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="fw-semibold mb-8">Halo <?php echo htmlspecialchars($_SESSION['name']); ?></h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="/user/index.php">Home</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Database</li>
                                        </ol>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card w-100 position-relative overflow-hidden">
                        <div class="px-4 py-3 border-bottom">
                            <h4 class="card-title mb-0">Request Database Baru</h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="form-container">
                                <?php if (isset($_SESSION['message'])): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <?php echo htmlspecialchars($_SESSION['message']); ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                    <?php unset($_SESSION['message']); ?>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['error'])): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <?php echo htmlspecialchars($_SESSION['error']); ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                    <?php unset($_SESSION['error']); ?>
                                <?php endif; ?>
                                <form method="POST" action="/config/request_database.php">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="database_name" class="form-label">Nama Database</label>
                                        <input type="text" class="form-control" id="database_name" name="database_name" required pattern="[a-zA-Z0-9_]+" title="Hanya huruf, angka, dan garis bawah yang diizinkan">
                                    </div>
                                    <div class="mb-3">
                                        <label for="request_description" class="form-label">Deskripsi Kebutuhan (misalnya, untuk MQTT topic apa)</label>
                                        <textarea class="form-control" id="request_description" name="request_description" rows="4" required></textarea>
                                    </div>
                                    <button class="btn btn-primary w-100 py-8 mb-4 rounded-2" type="submit">Kirim Permintaan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                function handleColorTheme(e) {
                    document.documentElement.setAttribute("data-color-theme", e);
                }
                function confirmLogout() {
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: 'Anda akan keluar dari dashboard!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Logout!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'logout.php';
                        }
                    });
                }
            </script>
            <script src="../src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
            <script src="../src/assets/libs/simplebar/dist/simplebar.min.js"></script>
            <script src="../src/assets/js/theme/app.dark.init.js"></script>
            <script src="../src/assets/js/theme/theme.js"></script>
            <script src="../src/assets/js/theme/app.min.js"></script>
            <script src="../src/assets/js/theme/sidebarmenu.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </div>
    </div>
</body>
</html>