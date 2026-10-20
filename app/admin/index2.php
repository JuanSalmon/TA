<?php
namespace Jubox\Web;

session_start();
error_log("[" . date('Y-m-d H:i:s') . "] Memuat admin/index2.php, path: " . __FILE__ . ", sesi awal: " . print_r($_SESSION, true));

// Muat autentikasi
require_once __DIR__ . '/../config/auth.php';

// Pastikan hanya admin yang dapat mengakses
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    error_log("[" . date('Y-m-d H:i:s') . "] Akses ditolak untuk admin/index2.php, sesi: " . print_r($_SESSION, true));
    $_SESSION['error'] = 'Akses hanya untuk admin';
    header("Location: /login/index.php?error=access_denied");
    exit();
}
error_log("[" . date('Y-m-d H:i:s') . "] Akses admin/index2.php berhasil, sesi: " . print_r($_SESSION, true));

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
} catch (\PDOException $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Koneksi database gagal: " . $e->getMessage());
    $_SESSION['error'] = 'Koneksi database gagal';
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
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css" />
    <style>
        .preloader img { transform: scale(5); }
        .password-container { position: relative; display: inline-block; }
        .password-container i {
            position: absolute; right: -25px; top: 35%; transform: translateY(-50%); cursor: pointer; color: #aaa;
        }
        .dropdown { position: relative; display: inline-block; }
        .dropdown-toggle { cursor: pointer; display: inline-flex; align-items: center; white-space: nowrap; }
        .dropdown-toggle i { margin-right: 8px; font-size: 12px; }
        .dropdown-content {
            display: none; position: absolute; left: 0; background-color: #f9f9f9; min-width: 300px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.1); z-index: 1; border-radius: 8px;
            border: 1px solid #e0e0e0; padding: 10px;
        }
        .dropdown-content .text-muted { font-size: 14px; color: #6c757d; margin-bottom: 5px; }
        .dropdown-item { padding: 8px 0; font-size: 14px; border-bottom: 1px solid #e0e0e0; }
        .dropdown-item:last-child { border-bottom: none; }
        .dropdown-item:hover { background-color: #f1f1f1; }
    </style>
    <title>Admin Dashboard Broker</title>
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
                            <span class="hide-menu">User</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="/admin/index.php" aria-expanded="false">
                                <span><i class="fa fa-home"></i></span>
                                <span class="hide-menu">Home</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link active" href="/admin/index2.php" aria-expanded="false">
                                <span><i class="fa fa-users"></i></span>
                                <span class="hide-menu">Users</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="/admin/index3.php" aria-expanded="false">
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
                                                <a class="text-muted text-decoration-none" href="/admin/index.php">User</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">User Information</li>
                                        </ol>
                                    </nav>
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
                                <table id="userTable" class="table text-nowrap mb-0 align-middle table-striped w-100">
                                    <thead class="text-dark fs-4">
                                        <tr>
                                            <th class="text-center"><h6 class="fs-4 fw-semibold mb-0 ms-3">User</h6></th>
                                            <th class="text-center"><h6 class="fs-4 fw-semibold mb-0">Email</h6></th>
                                            <th class="text-center"><h6 class="fs-4 fw-semibold mb-0 ms-2">Status</h6></th>
                                            <th class="text-center"><h6 class="fs-4 fw-semibold mb-0 ms-2">Allow</h6></th>
                                            <th class="text-center"><h6 class="fs-4 fw-semibold mb-0 ms-5">Action</h6></th>
                                        </tr>
                                    </thead>
                                    <tbody id="user-table-body"></tbody>
                                </table>
                            </div>
                            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel">Edit User</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="editForm">
                                                <input type="hidden" id="editId" name="edit_id">
                                                <div class="mb-3">
                                                    <label for="editName" class="form-label">Username</label>
                                                    <input type="text" class="form-control" id="editName" name="name" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="editEmail" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="editEmail" name="email" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="editPassword" class="form-label">Password (Kosongkan jika tidak diubah)</label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" id="editPassword" name="password" placeholder="Masukkan password baru">
                                                        <span class="input-group-text">
                                                            <i class="fas fa-eye toggle-password-modal" style="cursor: pointer;"></i>
                                                        </span>
                                                    </div>
                                                    <div class="invalid-feedback">Password minimal 8 karakter.</div>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Simpan</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
            <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
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
                $(document).ready(function() {
                    // Escape HTML untuk mencegah XSS
                    function escapeHtml(text) {
                        if (!text) return '';
                        const map = {
                            '&': '&amp;',
                            '<': '&lt;',
                            '>': '&gt;',
                            '"': '&quot;',
                            "'": '&apos;'
                        };
                        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
                    }
                    // Validasi form edit
                    $('#editForm').on('submit', function(e) {
                        const password = $('#editPassword').val();
                        if (password && password.length < 8) {
                            e.preventDefault();
                            $('#editPassword').addClass('is-invalid');
                            return;
                        }
                        $('#editPassword').removeClass('is-invalid');
                    });
                    // Inisialisasi DataTable
                    var table = $('#userTable').DataTable({
                        ajax: {
                            url: 'get_user.php',
                            dataSrc: 'data',
                            error: function(xhr, error, thrown) {
                                let errorMsg = 'Gagal memuat data';
                                if (xhr.status === 0) {
                                    errorMsg = 'Koneksi jaringan gagal. Periksa koneksi internet Anda.';
                                } else if (xhr.status === 404) {
                                    errorMsg = 'API get_user.php tidak ditemukan.';
                                } else if (xhr.status === 500) {
                                    errorMsg = 'Terjadi kesalahan server. Silakan coba lagi nanti.';
                                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                                    errorMsg = xhr.responseJSON.error;
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMsg
                                });
                            }
                        },
                        columns: [
                            {
                                data: 'name',
                                render: function(data, type, row) {
                                    try {
                                        const topics = Array.isArray(row.topics) ? row.topics : [];
                                        let dropdownContent = topics.map(topic => {
                                            const escapedTopic = escapeHtml(topic || 'N/A');
                                            return `<div class="dropdown-item">${escapedTopic}</div>`;
                                        }).join('');
                                        const escapedName = escapeHtml(data || 'Unknown');
                                        return `
                                            <div class="dropdown">
                                                <span class="dropdown-toggle">
                                                    <i class="fas fa-chevron-down"></i>
                                                    ${escapedName}
                                                </span>
                                                <div class="dropdown-content p-2">
                                                    <div class="text-muted mb-1">Topic</div>
                                                    ${dropdownContent || '<div class="dropdown-item">Tidak ada topik</div>'}
                                                </div>
                                            </div>`;
                                    } catch (e) {
                                        return '<div>Error rendering user</div>';
                                    }
                                }
                            },
                            {
                                data: 'email',
                                render: function(data) {
                                    try {
                                        const escapedEmail = escapeHtml(data || 'N/A');
                                        return `<h6 class="fw-normal mb-0">${escapedEmail}</h6>`;
                                    } catch (e) {
                                        return '<h6>Error rendering email</h6>';
                                    }
                                }
                            },
                            {
                                data: 'allowed',
                                render: function(data) {
                                    try {
                                        return `<h6 class="fw-normal mb-0 ms-2">${data ? 'Allowed' : 'Not Allowed'}</h6>`;
                                    } catch (e) {
                                        return '<h6>Error rendering status</h6>';
                                    }
                                }
                            },
                            {
                                data: null,
                                render: function(data, type, row) {
                                    try {
                                        return `
                                            <form class="allow-form" action="allow.php" method="POST" style="margin: 0;">
                                                <input type="hidden" name="user_id" value="${escapeHtml(row.id.toString())}">
                                                <input type="hidden" name="allowed" value="${row.allowed ? '0' : '1'}">
                                                <button type="submit" class="btn btn-sm ${row.allowed ? 'btn-danger' : 'btn-success'} btn-allow">
                                                    ${row.allowed ? 'Disallow' : 'Allow'}
                                                </button>
                                            </form>`;
                                    } catch (e) {
                                        return '<div>Error rendering allow button</div>';
                                    }
                                }
                            },
                            {
                                data: null,
                                render: function(data, type, row) {
                                    try {
                                        const escapedName = escapeHtml(row.name || '');
                                        return `
                                            <button class="btn btn-sm btn-primary btn-edit" data-id="${escapeHtml(row.id.toString())}">Edit</button>
                                            <form action="hapus_user.php" method="POST" style="display: inline-block; margin-left: 5px;">
                                                <input type="hidden" name="username" value="${escapedName}">
                                                <button type="submit" class="btn btn-sm btn-danger delete-user" data-username="${escapedName}">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>`;
                                    } catch (e) {
                                        return '<div>Error rendering action buttons</div>';
                                    }
                                }
                            }
                        ],
                        language: {
                            search: "Cari:",
                            lengthMenu: "Tampilkan _MENU_ entri",
                            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                            paginate: {
                                first: "Pertama",
                                last: "Terakhir",
                                next: "Selanjutnya",
                                previous: "Sebelumnya"
                            }
                        },
                        pageLength: 10,
                        order: [[0, 'asc']],
                        responsive: true
                    });
                    // Handle edit button click
                    $('#userTable').on('click', '.btn-edit', function() {
                        const id = $(this).data('id');
                        $.ajax({
                            url: 'get_user.php',
                            type: 'GET',
                            data: { id: id },
                            dataType: 'json',
                            cache: false,
                            success: function(response) {
                                if (response.error) {
                                    Swal.fire({ icon: 'error', title: 'Error', text: response.error });
                                    return;
                                }
                                $('#editId').val(response.id);
                                $('#editName').val(response.name);
                                $('#editEmail').val(response.email);
                                $('#editPassword').val('');
                                $('#editModal').modal('show');
                            },
                            error: function(xhr, status) {
                                let errorMsg = 'Gagal mengambil data user';
                                if (xhr.status === 0) {
                                    errorMsg = 'Koneksi jaringan gagal. Periksa koneksi internet Anda.';
                                } else if (xhr.status === 404) {
                                    errorMsg = 'API get_user.php tidak ditemukan.';
                                } else if (xhr.status === 500) {
                                    errorMsg = 'Terjadi kesalahan server. Silakan coba lagi nanti.';
                                }
                                Swal.fire({ icon: 'error', title: 'Error', text: errorMsg });
                            }
                        });
                    });
                    // Handle edit form submission
                    $('#editForm').on('submit', function(e) {
                        e.preventDefault();
                        const password = $('#editPassword').val();
                        if (password && password.length < 8) {
                            $('#editPassword').addClass('is-invalid');
                            return;
                        }
                        $('#editPassword').removeClass('is-invalid');
                        const formData = $(this).serialize();
                        $.ajax({
                            url: 'update_user.php',
                            type: 'POST',
                            data: formData,
                            dataType: 'json',
                            cache: false,
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: response.message || 'Data user berhasil diperbarui.',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        $('#editModal').modal('hide');
                                        table.ajax.reload(null, false);
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: response.message || 'Gagal memperbarui data.'
                                    });
                                }
                            },
                            error: function(xhr, status) {
                                let errorMsg = 'Terjadi kesalahan saat menyimpan data';
                                if (xhr.status === 0) {
                                    errorMsg = 'Koneksi jaringan gagal. Periksa koneksi internet Anda.';
                                } else if (xhr.status === 404) {
                                    errorMsg = 'API update_user.php tidak ditemukan.';
                                } else if (xhr.status === 500) {
                                    errorMsg = 'Terjadi kesalahan server. Silakan coba lagi nanti.';
                                }
                                Swal.fire({ icon: 'error', title: 'Error', text: errorMsg });
                            }
                        });
                    });
                    // Toggle password visibility
                    $('.toggle-password-modal').on('click', function() {
                        const passwordInput = $(this).closest('.input-group').find('#editPassword');
                        if (passwordInput.attr('type') === 'password') {
                            passwordInput.attr('type', 'text');
                            $(this).removeClass('fa-eye').addClass('fa-eye-slash');
                        } else {
                            passwordInput.attr('type', 'password');
                            $(this).removeClass('fa-eye-slash').addClass('fa-eye');
                        }
                    });
                    // Handle allow/disallow form submission
                    $(document).on('submit', '.allow-form', function(e) {
                        e.preventDefault();
                        const form = $(this);
                        const button = form.find('.btn-allow');
                        const action = button.text().trim() === 'Allow' ? 'mengizinkan' : 'menonaktifkan';
                        Swal.fire({
                            title: 'Konfirmasi',
                            text: `Apakah Anda yakin ingin ${action} pengguna ini?`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ya, lanjutkan!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                button.text('Memproses...');
                                button.prop('disabled', true);
                                $.ajax({
                                    url: form.attr('action'),
                                    type: 'POST',
                                    data: form.serialize(),
                                    dataType: 'json',
                                    success: function(response) {
                                        if (response.success) {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Berhasil',
                                                text: response.message || `Pengguna berhasil ${action}.`,
                                                timer: 1500,
                                                showConfirmButton: false
                                            }).then(() => {
                                                table.ajax.reload(null, false);
                                                button.prop('disabled', false);
                                            });
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Gagal',
                                                text: response.message || `Gagal ${action} pengguna.`
                                            });
                                            button.prop('disabled', false);
                                            button.text(button.text().trim() === 'Allow' ? 'Allow' : 'Disallow');
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        let errorMsg = 'Terjadi kesalahan';
                                        if (xhr.status === 0) {
                                            errorMsg = 'Koneksi jaringan gagal. Periksa koneksi internet Anda.';
                                        } else if (xhr.status === 404) {
                                            errorMsg = 'API allow.php tidak ditemukan.';
                                        } else if (xhr.status === 500) {
                                            errorMsg = 'Terjadi kesalahan server. Silakan coba lagi nanti.';
                                        }
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Kesalahan',
                                            text: errorMsg
                                        });
                                        button.prop('disabled', false);
                                        button.text(button.text().trim() === 'Allow' ? 'Allow' : 'Disallow');
                                    }
                                });
                            }
                        });
                    });
                    // Handle delete form submission
                    $(document).on('submit', 'form:has(.delete-user)', function(e) {
                        e.preventDefault();
                        const form = $(this);
                        const username = form.find('.delete-user').data('username');
                        const button = form.find('.delete-user');
                        Swal.fire({
                            title: 'Konfirmasi',
                            text: `Apakah Anda yakin ingin menghapus pengguna ${username}?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ya, hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                button.text('Memproses...');
                                button.prop('disabled', true);
                                $.ajax({
                                    url: form.attr('action'),
                                    type: 'POST',
                                    data: form.serialize(),
                                    dataType: 'json',
                                    success: function(response) {
                                        if (response.success) {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Berhasil',
                                                text: response.message || 'Pengguna berhasil dihapus.',
                                                timer: 1500,
                                                showConfirmButton: false
                                            }).then(() => {
                                                table.ajax.reload(null, false);
                                                button.text('Hapus');
                                                button.prop('disabled', false);
                                            });
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Gagal',
                                                text: response.message || 'Gagal menghapus pengguna.'
                                            });
                                            button.text('Hapus');
                                            button.prop('disabled', false);
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        let errorMsg = 'Terjadi kesalahan saat menghapus';
                                        if (xhr.status === 0) {
                                            errorMsg = 'Koneksi jaringan gagal. Periksa koneksi internet Anda.';
                                        } else if (xhr.status === 404) {
                                            errorMsg = 'API hapus_user.php tidak ditemukan.';
                                        } else if (xhr.status === 500) {
                                            errorMsg = 'Terjadi kesalahan server. Silakan coba lagi nanti.';
                                        }
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Kesalahan',
                                            text: errorMsg
                                        });
                                        button.text('Hapus');
                                        button.prop('disabled', false);
                                    }
                                });
                            }
                        });
                    });
                    // Toggle dropdown
                    $('#userTable').on('click', '.dropdown-toggle', function(e) {
                        e.preventDefault();
                        const content = $(this).next('.dropdown-content');
                        $('.dropdown-content').not(content).slideUp(200);
                        content.slideToggle(200);
                    });
                    $(document).on('click', function(e) {
                        if (!$(e.target).closest('.dropdown').length) {
                            $('.dropdown-content').slideUp(200);
                        }
                    });
                    // Handle URL parameters for messages
                    function getUrlParameter(name) {
                        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                        const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                        const results = regex.exec(location.search);
                        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
                    }
                    const message = getUrlParameter('message');
                    const error = getUrlParameter('error');
                    if (message) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: message, timer: 3000, showConfirmButton: false });
                    } else if (error) {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: error, timer: 3000, showConfirmButton: false });
                    }
                });
            </script>
            <script src="../src/assets/libs/simplebar/dist/simplebar.min.js"></script>
            <script src="../src/assets/js/theme/app.dark.init.js"></script>
            <script src="../src/assets/js/theme/theme.js"></script>
            <script src="../src/assets/js/theme/app.min.js"></script>
        </div>
    </div>
</body>
</html>