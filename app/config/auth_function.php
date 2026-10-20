<?php
namespace Jubox\Web;

use PDO;
use PDOException;

function authenticateUser(PDO $koneksi, $username, $password) {
    // Validasi input
    if (empty($username) || empty($password)) {
        error_log("[" . date('Y-m-d H:i:s') . "] Input kosong: username=$username");
        return [
            'success' => false,
            'session_data' => [
                'error' => 'Harap masukkan username/email dan kata sandi'
            ],
            'redirect' => null
        ];
    }

    // Validasi format username/email
    if (!preg_match('/^[A-Za-z0-9._-]+$/i', $username) && !filter_var($username, FILTER_VALIDATE_EMAIL)) {
        error_log("[" . date('Y-m-d H:i:s') . "] Username/email tidak valid: $username");
        return [
            'success' => false,
            'session_data' => [
                'error' => 'Username hanya boleh berisi huruf, angka, titik, atau tanda hubung, atau format email tidak valid'
            ],
            'redirect' => null
        ];
    }

    // Cek tabel admin
    try {
        $stmt = $koneksi->prepare("SELECT id, name, password FROM admin WHERE name = :name LIMIT 1");
        $stmt->bindParam(':name', $username, PDO::PARAM_STR);
        if (!$stmt->execute()) {
            error_log("[" . date('Y-m-d H:i:s') . "] Gagal menjalankan query admin untuk username: $username");
            return [
                'success' => false,
                'session_data' => [
                    'error' => 'Terjadi kesalahan saat memeriksa admin'
                ],
                'redirect' => null
            ];
        }
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && is_array($admin)) {
            if (password_verify($password, $admin['password'])) {
                error_log("[" . date('Y-m-d H:i:s') . "] Login admin berhasil: $username");
                session_regenerate_id(true);
                return [
                    'success' => true,
                    'session_data' => [
                        'id' => $admin['id'],
                        'name' => $admin['name'],
                        'is_admin' => true
                    ],
                    'redirect' => '/admin/index.php'
                ];
            }
            error_log("[" . date('Y-m-d H:i:s') . "] Password salah untuk admin: $username");
            return [
                'success' => false,
                'session_data' => [
                    'error' => 'Kata sandi salah'
                ],
                'redirect' => null
            ];
        }
    } catch (PDOException $e) {
        error_log("[" . date('Y-m-d H:i:s') . "] Error query admin: " . $e->getMessage());
        return [
            'success' => false,
            'session_data' => [
                'error' => 'Terjadi kesalahan server'
            ],
            'redirect' => null
        ];
    }

    // Cek tabel user
    try {
        $stmt = $koneksi->prepare("SELECT id, name, email, password, is_online, allowed, first_login FROM user WHERE name = :name OR email = :email LIMIT 1");
        $stmt->bindParam(':name', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $username, PDO::PARAM_STR);
        if (!$stmt->execute()) {
            error_log("[" . date('Y-m-d H:i:s') . "] Gagal menjalankan query pengguna untuk username/email: $username");
            return [
                'success' => false,
                'session_data' => [
                    'error' => 'Terjadi kesalahan saat memeriksa pengguna'
                ],
                'redirect' => null
            ];
        }
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && is_array($user)) {
            if (password_verify($password, $user['password'])) {
                if (!$user['allowed']) {
                    error_log("[" . date('Y-m-d H:i:s') . "] Pengguna $username tidak diizinkan (allowed = 0)");
                    return [
                        'success' => false,
                        'session_data' => [
                            'error' => 'Akun Anda belum menunggu persetujuan admin'
                        ],
                        'redirect' => null
                    ];
                }
                $first_login = isset($user['first_login']) ? (bool)$user['first_login'] : true; // Default ke true jika NULL
                error_log("[" . date('Y-m-d H:i:s') . "] Login pengguna berhasil: $username, first_login: " . ($first_login ? '1' : '0') . ", redirect: " . ($first_login ? '/user/wizard.php' : '/user/index.php'));
                $stmt = $koneksi->prepare("UPDATE user SET is_online = 1, last_activity = NOW() WHERE id = :id");
                $stmt->execute(['id' => $user['id']]);
                session_regenerate_id(true);
                $redirect = $first_login ? '/user/wizard.php' : '/user/index.php';
                return [
                    'success' => true,
                    'session_data' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'is_online' => true,
                        'allowed' => (bool)$user['allowed'],
                        'first_login' => $first_login
                    ],
                    'redirect' => $redirect
                ];
            }
            error_log("[" . date('Y-m-d H:i:s') . "] Password salah untuk pengguna: $username");
            return [
                'success' => false,
                'session_data' => [
                    'error' => 'Kata sandi salah'
                ],
                'redirect' => null
            ];
        }
    } catch (PDOException $e) {
        error_log("[" . date('Y-m-d H:i:s') . "] Error query pengguna: " . $e->getMessage());
        return [
            'success' => false,
            'session_data' => [
                'error' => 'Terjadi kesalahan server'
            ],
            'redirect' => null
        ];
    }

    error_log("[" . date('Y-m-d H:i:s') . "] Username/email tidak ditemukan: $username");
    return [
        'success' => false,
        'session_data' => [
            'error' => 'Username/email tidak ditemukan'
        ],
        'redirect' => null
    ];
}
?>