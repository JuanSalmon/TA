document.querySelectorAll('.delete-user').forEach(button => {
    button.addEventListener('click', function (e) {
        e.preventDefault(); // Mencegah submit form default

        // Ambil form terdekat
        const form = this.closest('form');
        // Ambil ID user (opsional, untuk pesan kustom atau logging)
        const userId = this.getAttribute('data-id');

        // Tampilkan SweetAlert
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Data pengguna akan dihapus permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545', // Warna merah Bootstrap (btn-danger)
            cancelButtonColor: '#6c757d', // Warna abu-abu Bootstrap (btn-secondary)
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            buttonsStyling: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit(); // Submit form jika dikonfirmasi
            }
        });
    });
});