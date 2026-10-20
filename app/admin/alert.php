<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert</title>
    <!-- Favicon icon-->
    <link rel="shortcut icon" type="image/png" href="../src/assets/images/logos/logo-pnk.png" />
    <!-- Core Css -->
    <link rel="stylesheet" href="../src/assets/css/styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="border-bottom title-part-padding">
                <h4 class="card-title">Confirm Dialog</h4>
                <h6 class="card-subtitle">(Click on image)</h6>
            </div>
            <div class="card-body p-3">
                <img src="../src/assets/images/alert/model.png" alt="alert" class="img-fluid model_img" id="sa-confirm" data-user-id="1" />
            </div>
        </div>
    </div>
    <div class="dark-transparent sidebartoggler"></div>

    <!-- Import Js Files -->
    <script src="../src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../src/assets/libs/simplebar/dist/simplebar.min.js"></script>
    <script src="../src/assets/js/theme/app.dark.init.js"></script>
    <script src="../src/assets/js/theme/theme.js"></script>
    <script src="../src/assets/js/sweetalert.js"></script>
    <script src="../src/assets/js/theme/app.min.js"></script>
    <script src="../src/assets/js/theme/sidebarmenu.js"></script>
    <!-- solar icons -->
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const confirmButton = document.getElementById('sa-confirm');

            confirmButton.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');

                Swal.fire({
                    title: 'Konfirmasi Penghapusan',
                    text: 'Apakah Anda yakin ingin menghapus user ini? Aksi ini tidak dapat dibatalkan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('delete_user.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'user_id=' + encodeURIComponent(userId)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Berhasil!',
                                    'User telah dihapus.',
                                    'success'
                                ).then(() => {
                                    // Opsional: Redirect atau refresh halaman
                                    // window.location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Gagal!',
                                    data.message || 'Tidak bisa menghapus user.',
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            Swal.fire(
                                'Error!',
                                'Terjadi kesalahan: ' + error.message,
                                'error'
                            );
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>