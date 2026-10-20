document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();

            const username = this.getAttribute('data-username');
            const row = this.closest('tr');

            // Log untuk debugging
            console.log('Button clicked, data-username:', username);
            console.log('Type of data-username:', typeof username);

            // Validasi username
            if (!username || username.trim() === '') {
                Swal.fire(
                    'Error!',
                    'Username tidak valid di sisi klien.',
                    'error'
                );
                console.log('Invalid username:', username);
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Penghapusan',
                text: `Apakah Anda yakin ingin menghapus user ${username}? Aksi ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const deleteUrl = 'hapus_user.php'; 
                    console.log('Sending fetch request to:', deleteUrl, 'with username:', username);
                    console.log('Request body:', 'username=' + encodeURIComponent(username));
                    fetch(deleteUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'username=' + encodeURIComponent(username)
                    })
                    .then(response => {
                        console.log('Status:', response.status);
                        console.log('Headers:', response.headers.get('Content-Type'));
                        if (!response.headers.get('Content-Type').includes('application/json')) {
                            return response.text().then(text => {
                                throw new Error('Expected JSON response, received: ' + text.substring(0, 100));
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Berhasil!',
                                'User telah dihapus.',
                                'success'
                            ).then(() => {
                                row.remove();
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
                        console.error('Fetch error:', error);
                    });
                }
            });
        });
    });
});