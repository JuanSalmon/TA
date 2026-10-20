<?php
namespace Jubox\Web;

session_start();
error_log("[" . date('Y-m-d H:i:s') . "] Memuat user/index.php, path: " . __FILE__ . ", sesi awal: " . print_r($_SESSION, true));

// Muat autentikasi
require_once __DIR__ . '/../config/auth.php';

// Pastikan pengguna telah login dan bukan admin
if (!isset($_SESSION['name']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] === true) {
    error_log("[" . date('Y-m-d H:i:s') . "] Akses ditolak untuk /user/index.php: " . ($_SESSION['name'] ?? 'tidak ada') . ", is_admin: " . ($_SESSION['is_admin'] ?? 'tidak ada'));
    $_SESSION['error'] = $_SESSION['is_admin'] === true ? 'Admin tidak dapat mengakses dashboard user' : 'Silakan login terlebih dahulu';
    header("Location: /login/index.php?error=" . ($_SESSION['is_admin'] === true ? 'access_denied' : 'not_logged_in'));
    exit();
}

// Periksa status allowed
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
    // Sinkronisasi sesi dengan database
    $_SESSION['allowed'] = $user['allowed'];
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .preloader img { transform: scale(5); }
        .card { border-radius: 8px; border: 1px solid #e0e0e0; }
        .card-title { font-size: 1.5em; }
        .text-muted { font-size: 0.9em; color: #666 !important; }
        .list-group-item { border: none; padding: 12px 0; }
        .list-group-item:not(:last-child) { border-bottom: 1px solid #e0e0e0; }
        .text-dark { color: #222 !important; }
        .fw-bold { font-weight: 600; }
        .connection-value { font-size: 0.95em; word-break: break-all; }
        .copy-btn { border: 1px solid #ccc; border-radius: 4px; padding: 4px 8px; background-color: transparent; transition: background-color 0.2s; }
        .copy-btn:hover { background-color: #f0f0f0; }
        .copy-btn i { font-size: 1em; color: #666; }
        @media (max-width: 576px) {
            .list-group-item { flex-direction: column; align-items: flex-start; }
            .list-group-item .connection-value { margin-top: 5px; }
            .copy-btn { margin-top: 10px; }
        }
    </style>
    <title>User Dashboard Broker</title>
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
                        <img src="../src/assets/images/logos/logo-pnk.png" width="50" light-logo" alt="Logo-light" />
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
                                        </ol>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container my-4">
                        <div class="card shadow-sm border">
                            <div class="card-body">
                                <h2 class="card-title mb-2">Connection Details</h2>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold text-dark">URL</span>
                                            <div class="text-dark connection-value" id="url">iot-broker.pnk.ac.id</div>
                                        </div>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-copy-target="url">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold text-dark">Port</span>
                                            <div class="text-dark connection-value" id="port">1883</div>
                                        </div>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-copy-target="port">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold text-dark">Websocket Port</span>
                                            <div class="text-dark connection-value" id="websocket-port">8083</div>
                                        </div>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-copy-target="websocket-port">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </li>
                                </ul>
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
                document.addEventListener('DOMContentLoaded', () => {
                    const copyButtons = document.querySelectorAll('.copy-btn');
                    copyButtons.forEach(button => {
                        button.addEventListener('click', () => {
                            const targetId = button.getAttribute('data-copy-target');
                            const targetElement = document.getElementById(targetId);
                            const textToCopy = targetElement.textContent;
                            if (!navigator.clipboard) {
                                const tempInput = document.createElement('textarea');
                                tempInput.value = textToCopy;
                                document.body.appendChild(tempInput);
                                tempInput.select();
                                document.execCommand('copy');
                                document.body.removeChild(tempInput);
                                showFeedback(button, 'Copied!');
                                return;
                            }
                            navigator.clipboard.writeText(textToCopy)
                                .then(() => {
                                    showFeedback(button, 'Copied!');
                                })
                                .catch(err => {
                                    console.error('Failed to copy text: ', err);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: 'Gagal menyalin teks. Silakan coba lagi.'
                                    });
                                });
                        });
                    });
                    function showFeedback(button, message) {
                        const originalIcon = button.innerHTML;
                        button.innerHTML = '<i class="fas fa-check"></i> ' + message;
                        button.classList.add('btn-success');
                        button.classList.remove('btn-outline-secondary');
                        setTimeout(() => {
                            button.innerHTML = originalIcon;
                            button.classList.remove('btn-success');
                            button.classList.add('btn-outline-secondary');
                        }, 1500);
                    }
                });
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