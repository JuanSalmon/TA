<?php
namespace Jubox\Web;

session_start();

// Muat autentikasi
require_once __DIR__ . '/../config/auth.php';

// Pastikan hanya admin yang dapat mengakses
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    error_log("[" . date('Y-m-d H:i:s') . "] Akses ditolak untuk non-admin: " . ($_SESSION['name'] ?? 'tidak ada'));
    $_SESSION['error'] = 'Akses hanya untuk admin';
    header("Location: /login/index.php?error=access_denied");
    exit();
}

// Muat konfigurasi
$configResult = include __DIR__ . '/../config/config.php';
if (!is_array($configResult) || !isset($configResult['success']) || !$configResult['success']) {
    error_log("Konfigurasi tidak valid: " . print_r($configResult, true));
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
} catch (\PDOException $e) {
    error_log("Koneksi database gagal: " . $e->getMessage());
    $_SESSION['error'] = 'Koneksi database gagal';
    header("Location: /login/index.php?error=db_error");
    exit();
}

// Ambil data landing page
try {
    $stmt = $koneksi->prepare("SELECT * FROM landing_page WHERE id = ?");
    $stmt->execute([1]);
    $landing = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
} catch (\PDOException $e) {
    error_log("Query gagal: " . $e->getMessage());
    $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Query gagal: ' . htmlspecialchars($e->getMessage()) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    $landing = [];
}

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description_1 = $_POST['description_1'] ?? '';
    $description_2 = $_POST['description_2'] ?? '';
    $image_path_1 = $landing['image_path_1'] ?? '';
    $image_path_2 = $landing['image_path_2'] ?? '';

    // Validasi input
    if (empty($title) || empty($description_1) || empty($description_2)) {
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Judul, deskripsi 1, dan deskripsi 2 wajib diisi!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    } else {
        // Validasi upload gambar
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        if (isset($_FILES['image_1']) && $_FILES['image_1']['error'] === UPLOAD_ERR_OK) {
            if (!in_array($_FILES['image_1']['type'], $allowed_types) || $_FILES['image_1']['size'] > $max_size) {
                $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Gambar 1 tidak valid (hanya JPEG/PNG/GIF, maks 5MB)!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            } else {
                $upload_dir = __DIR__ . '/../Uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $image_name_1 = uniqid() . '-' . basename($_FILES['image_1']['name']);
                $image_path_1 = 'Uploads/' . $image_name_1;
                if (!move_uploaded_file($_FILES['image_1']['tmp_name'], $upload_dir . $image_name_1)) {
                    $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Gagal mengunggah gambar 1!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                }
            }
        }
        if (isset($_FILES['image_2']) && $_FILES['image_2']['error'] === UPLOAD_ERR_OK) {
            if (!in_array($_FILES['image_2']['type'], $allowed_types) || $_FILES['image_2']['size'] > $max_size) {
                $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Gambar 2 tidak valid (hanya JPEG/PNG/GIF, maks 5MB)!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            } else {
                $upload_dir = __DIR__ . '/../Uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $image_name_2 = uniqid() . '-' . basename($_FILES['image_2']['name']);
                $image_path_2 = 'Uploads/' . $image_name_2;
                if (!move_uploaded_file($_FILES['image_2']['tmp_name'], $upload_dir . $image_name_2)) {
                    $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Gagal mengunggah gambar 2!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                }
            }
        }
        // Simpan ke database
        if (!isset($message)) {
            try {
                $sql = isset($landing['id'])
                    ? "UPDATE landing_page SET title = ?, description_1 = ?, description_2 = ?, image_path_1 = ?, image_path_2 = ? WHERE id = ?"
                    : "INSERT INTO landing_page (title, description_1, description_2, image_path_1, image_path_2) VALUES (?, ?, ?, ?, ?)";
                $params = isset($landing['id'])
                    ? [$title, $description_1, $description_2, $image_path_1, $image_path_2, $landing['id']]
                    : [$title, $description_1, $description_2, $image_path_1, $image_path_2];
                $stmt = $koneksi->prepare($sql);
                $stmt->execute($params);
                $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">Perubahan berhasil disimpan!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                $stmt = $koneksi->prepare("SELECT * FROM landing_page WHERE id = ?");
                $stmt->execute([1]);
                $landing = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
            } catch (\PDOException $e) {
                error_log("Gagal menyimpan: " . $e->getMessage());
                $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Gagal menyimpan: ' . htmlspecialchars($e->getMessage()) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            }
        }
    }
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
    </style>
    <title>Admin Dashboard - Edit Landing Page</title>
</head>
<body class="link-sidebar">
    <div class="preloader">
        <img src="../src/assets/images/logos/logo-pnk.png" alt="loader" class="lds-ripple img-fluid" />
    </div>
    <div id="main-wrapper">
        <aside class="left-sidebar with-vertical">
            <div>
                <div class="brand-logo d-flex align-items-center justify-content-between">
                    <div class="logo-container">
                        <a href="/admin/index.php" class="text-nowrap logo-img">
                            <img src="../src/assets/images/logos/polteklogo.png" width="50" class="dark-logo" alt="Logo-Dark" />
                            <img src="../src/assets/images/logos/polteklogo.png" width="50" class="light-logo" alt="Logo-light" />
                        </a>
                        <span class="logo-text">Broker PNK</span>
                    </div>
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
                            <a class="sidebar-link" href="/admin/index.php" aria-expanded="false">
                                <span><i class="fa fa-home"></i></span>
                                <span class="hide-menu">Home</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="/admin/index2.php" aria-expanded="false">
                                <span><i class="fa fa-users"></i></span>
                                <span class="hide-menu">Users</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link active" href="/admin/index3.php" aria-expanded="false">
                                <span><i class="fa fa-edit"></i></span>
                                <span class="hide-menu">Edit</span>
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
                                                <a class="text-muted text-decoration-none" href="/admin/index.php">Edit</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Edit Landing Page</li>
                                        </ol>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card w-100 position-relative overflow-hidden">
                        <div class="px-4 py-3 border-bottom">
                            <h4 class="card-title mb-0">Edit Landing Page</h4>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($message): ?>
                                <?php echo $message; ?>
                            <?php endif; ?>
                            <?php if (empty($landing)): ?>
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    Data landing page belum ada. Silakan isi form untuk membuat data baru.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-lg-6 mb-4">
                                    <div class="card shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title mb-4">Edit Konten</h5>
                                            <form method="POST" enctype="multipart/form-data">
                                                <div class="mb-3">
                                                    <label for="title" class="form-label">Judul</label>
                                                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($landing['title'] ?? ''); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="description_1" class="form-label">Deskripsi 1 (Hero Section)</label>
                                                    <textarea class="form-control" id="description_1" name="description_1" rows="5" required><?php echo htmlspecialchars($landing['description_1'] ?? ''); ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="description_2" class="form-label">Deskripsi 2 (Production Section)</label>
                                                    <textarea class="form-control" id="description_2" name="description_2" rows="5" required><?php echo htmlspecialchars($landing['description_2'] ?? ''); ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="image_1" class="form-label">Gambar Header 1</label>
                                                    <input type="file" class="form-control" id="image_1" name="image_1" accept="image/jpeg,image/png,image/gif">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="image_2" class="form-label">Gambar Header 2</label>
                                                    <input type="file" class="form-control" id="image_2" name="image_2" accept="image/jpeg,image/png,image/gif">
                                                </div>
                                                <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <div class="card shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title mb-4">Pratinjau Landing Page</h5>
                                            <?php if (!empty($landing)): ?>
                                                <div class="border rounded overflow-hidden mb-3">
                                                    <img src="<?php echo htmlspecialchars($landing['image_path_1'] ?? ''); ?>" alt="Pratinjau Gambar 1" class="img-fluid" style="height: 200px; object-fit: cover; width: 100%; <?php echo empty($landing['image_path_1']) ? 'display: none;' : ''; ?>">
                                                    <div class="p-3">
                                                        <h3 class="h4"><?php echo htmlspecialchars($landing['title'] ?? ''); ?></h3>
                                                        <p class="text-muted"><?php echo htmlspecialchars($landing['description_1'] ?? ''); ?></p>
                                                    </div>
                                                </div>
                                                <div class="border rounded overflow-hidden">
                                                    <img src="<?php echo htmlspecialchars($landing['image_path_2'] ?? ''); ?>" alt="Pratinjau Gambar 2" class="img-fluid" style="height: 200px; object-fit: cover; width: 100%; <?php echo empty($landing['image_path_2']) ? 'display: none;' : ''; ?>">
                                                    <div class="p-3">
                                                        <h3 class="h4"><?php echo htmlspecialchars($landing['title'] ?? ''); ?></h3>
                                                        <p class="text-muted"><?php echo htmlspecialchars($landing['description_2'] ?? ''); ?></p>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <p class="text-muted">Tidak ada data untuk ditampilkan. Silakan isi form untuk menambahkan konten.</p>
                                            <?php endif; ?>
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