<?php
session_start();

// Include file konfigurasi database
$config = include __DIR__ . '/../config/config.php'; // Sesuaikan path

require_once __DIR__ . '/../config/database.php'; // Sesuaikan path

try {
    // Buat objek Database
    $database = new \Jubox\Web\Database($config);
    $koneksi = $database->getConnection();

    // Verifikasi user berdasarkan username di sesi
    $query = "SELECT * FROM users WHERE name = :username";
    $stmt = $koneksi->prepare($query);
    $stmt->execute(['username' => $_SESSION['username']]);
    
    if ($stmt->rowCount() == 0) {
        header("Location: ../login/login.php");
        exit;
    }
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_id = $user['id']; // Ambil id user untuk relasi

    // Proses form untuk menambah topik
    $success_message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['topik'])) {
        $nama_topik = trim($_POST['topik']);
        $query = "INSERT INTO user_topik (id_user, nama_topik) VALUES (:id_user, :nama_topik)";
        $stmt = $koneksi->prepare($query);
        $stmt->execute(['id_user' => $user_id, 'nama_topik' => $nama_topik]);
        $success_message = 'Topik berhasil ditambahkan!';
    }

    // Ambil daftar topik berdasarkan id_user
    $query = "SELECT id_topik, nama_topik FROM user_topik WHERE id_user = :id_user";
    $stmt = $koneksi->prepare($query);
    $stmt->execute(['id_user' => $user_id]);
    $topik_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (\PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
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
        .password-container { position: relative; display: inline-block; }
        .password-container i {
            position: absolute;
            right: -25px;
            top: 35%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #aaa;
        }
        .table-responsive { max-height: 400px; overflow-y: auto; }
    </style>
    <title>User Dashboard Broker</title>
</head>
<body class="link-sidebar">
    <div class="preloader">
        <img src="../src/assets/images/logos/logo-pnk.png" alt="loader" class="lds-ripple img-fluid" />
    </div>
    <div id="main-wrapper">
        <!-- Sidebar Start -->
        <aside class="left-sidebar with-vertical">
            <div>
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
                        <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                            <span class="hide-menu">Home</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="index.php" aria-expanded="false">
                                <span><i class="ti ti-aperture"></i></span>
                                <span class="hide-menu">Home</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="index2.php" aria-expanded="false">
                                <span><i class="ti ti-shopping-cart"></i></span>
                                <span class="hide-menu">Topik</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="index3.php" aria-expanded="false">
                                <span><i class="ti ti-currency-dollar"></i></span>
                                <span class="hide-menu">ok3</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <div class="fixed-profile p-3 mx-4 mb-2 bg-secondary-subtle rounded mt-3">
                    <div class="hstack gap-3">
                        <div class="john-title">
                            <h6 class="mb-0 fs-4 fw-semibold"><?php echo htmlspecialchars($_SESSION['username']); ?></h6>
                        </div>
                        <button class="border-0 bg-transparent text-primary ms-auto" tabindex="0" type="button" aria-label="logout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="logout" onclick="confirmLogout()">
                            <i class="ti ti-power fs-6"></i>
                        </button>
                    </div>
                </div>
            </div>
        </aside>
        <!-- Sidebar End -->
        <div class="page-wrapper">
            <!-- Header Start -->
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
                        <div class="d-block d-lg-none py-4">
                            <a href="index.php" class="text-nowrap logo-img">
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
            <!-- Header End -->
            <div class="body-wrapper">
                <div class="container-fluid">
                    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
                        <div class="card-body px-4 py-3">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="fw-semibold mb-8">Halo <?php echo htmlspecialchars($_SESSION['username']); ?></h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Topik</a>
                                            </li>
                                        </ol>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card w-100 position-relative overflow-hidden">
                        <div class="px-4 py-3 border-bottom">
                            <h4 class="card-title mb-0">Kelola Topik</h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <!-- Form Section -->
                                <div class="col-lg-6 mb-4">
                                    <div class="card shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title mb-4">Buat Topik Baru</h5>
                                            <form method="POST" enctype="multipart/form-data" id="formTopik">
                                                <div class="mb-3">
                                                    <label for="topik" class="form-label">Nama Topik</label>
                                                    <input type="text" class="form-control" id="topik" name="topik" required>
                                                </div>
                                                <button type="submit" class="btn btn-primary w-100">Simpan Topik</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- List Section -->
                                <div class="col-lg-6 mb-4">
                                    <div class="card shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title mb-4">Daftar Topik</h5>
                                            <?php if (empty($topik_list)): ?>
                                                <p class="text-muted">Belum ada topik untuk user ini.</p>
                                            <?php else: ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">No</th>
                                                                <th scope="col">Nama Topik</th>
                                                                <th scope="col">Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($topik_list as $index => $topik): ?>
                                                                <tr>
                                                                    <td><?php echo $index + 1; ?></td>
                                                                    <td><?php echo htmlspecialchars($topik['nama_topik']); ?></td>
                                                                    <td>
                                                                        <a href="javascript:void(0)" class="btn btn-sm btn-danger delete-topik" data-id="<?php echo $topik['id_topik']; ?>">Hapus</a>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../src/assets/libs/simplebar/dist/simplebar.min.js"></script>
    <script src="../src/assets/js/theme/app.dark.init.js"></script>
    <script src="../src/assets/js/theme/theme.js"></script>
    <script src="../src/assets/js/theme/app.min.js"></script>
    <script src="../src/assets/js/theme/sidebarmenu.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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

        // SweetAlert untuk sukses menambah topik
        <?php if (!empty($success_message)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?php echo $success_message; ?>',
                showConfirmButton: false,
                timer: 1500
            });
        <?php endif; ?>

        // SweetAlert untuk konfirmasi hapus topik
        document.querySelectorAll('.delete-topik').forEach(button => {
            button.addEventListener('click', function() {
                const idTopik = this.getAttribute('data-id');
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: 'Topik ini akan dihapus!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `delete_topik.php?id_topik=${idTopik}`;
                    }
                });
            });
        });
    </script>
</body>
</html>