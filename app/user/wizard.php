<?php
namespace Jubox\Web;

use PDOException;

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/enkrip.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['id']) || !isset($_SESSION['name'])) {
    error_log("[" . date('Y-m-d H:i:s') . "] Akses wizard tanpa sesi: " . print_r($_SESSION, true));
    header('Location: /login/index.php?error=not_logged_in');
    exit();
}

// Inisialisasi koneksi database
try {
    $configResult = include __DIR__ . '/../config/config.php';
    if (!is_array($configResult) || !isset($configResult['success']) || !$configResult['success']) {
        error_log("[" . date('Y-m-d H:i:s') . "] Konfigurasi tidak valid: " . print_r($configResult, true));
        header('Location: /login/index.php?error=config_error');
        exit();
    }
    $config = $configResult['config'];
    $db = new Database($config);
    $conn = $db->getConnection();
} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Error inisialisasi database: " . $e->getMessage());
    header('Location: /login/index.php?error=db_error');
    exit();
}

// Cek apakah ini login pertama
$user_id = $_SESSION['id'];
$query = "SELECT first_login FROM user WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$first_login = isset($user['first_login']) ? (bool)$user['first_login'] : true;
error_log("[" . date('Y-m-d H:i:s') . "] Memeriksa first_login untuk user_id=$user_id, first_login=" . ($first_login ? '1' : '0'));

// Cek apakah pengguna sudah memiliki kredensial MQTT
$query = "SELECT id FROM kredensial_mqtt WHERE user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

error_log("[" . date('Y-m-d H:i:s') . "] Cek wizard: user_id=$user_id, first_login=" . ($first_login ? '1' : '0') . ", kredensial_mqtt_exists=" . $stmt->rowCount());
if ($stmt->rowCount() > 0 || !$first_login) {
    error_log("[" . date('Y-m-d H:i:s') . "] Redirect ke /user/index.php karena kredensial_mqtt_exists=" . $stmt->rowCount() . " atau first_login=" . ($first_login ? '1' : '0'));
    header('Location: /user/index.php');
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mqtt_username = trim($_POST['mqtt_username'] ?? '');
    $mqtt_password = trim($_POST['mqtt_password'] ?? '');

    // Validasi input
    if (empty($mqtt_username) || empty($mqtt_password)) {
        $errors[] = 'Username dan password MQTT harus diisi.';
    } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $mqtt_username)) {
        $errors[] = 'Username MQTT hanya boleh berisi huruf, angka, tanda hubung, atau garis bawah.';
    } elseif (strlen($mqtt_password) < 6) {
        $errors[] = 'Password MQTT harus minimal 6 karakter.';
    } else {
        // Validasi unik untuk mqtt_username
        $query = "SELECT id FROM kredensial_mqtt WHERE mqtt_username = :mqtt_username";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':mqtt_username', $mqtt_username, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $errors[] = 'Username MQTT sudah digunakan.';
        } else {
            // Enkripsi password MQTT
            try {
                $key = base64_decode(getenv('AES_KEY') ?: $config['aes_key']);
                if (strlen($key) !== 32) {
                    throw new Exception('Kunci enkripsi tidak valid');
                }
                $encrypted_password = encrypt($mqtt_password, $key);

                // Simpan kredensial ke tabel kredensial_mqtt
                $conn->beginTransaction();
                $query = "INSERT INTO kredensial_mqtt (user_id, mqtt_username, mqtt_password) VALUES (:user_id, :mqtt_username, :mqtt_password)";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':mqtt_username', $mqtt_username, PDO::PARAM_STR);
                $stmt->bindParam(':mqtt_password', $encrypted_password, PDO::PARAM_STR);
                $stmt->execute();

                // Update first_login di tabel user
                $query = "UPDATE user SET first_login = 0 WHERE id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                $stmt->execute();

                $conn->commit();

                $success = 'Kredensial MQTT berhasil disimpan!';
                error_log("[" . date('Y-m-d H:i:s') . "] Kredensial MQTT disimpan untuk user_id=$user_id, mqtt_username=$mqtt_username");
                header('Refresh: 2; URL=/user/index.php');
            } catch (Exception $e) {
                $conn->rollBack();
                $errors[] = 'Gagal menyimpan kredensial: ' . $e->getMessage();
                error_log("[" . date('Y-m-d H:i:s') . "] Error enkripsi/simpan: " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfigurasi MQTT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .password-container {
            position: relative;
        }
        .password-container input {
            padding-right: 40px;
        }
        .password-container i {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #aaa;
        }
    </style>
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-lg w-100" style="max-width: 500px;">
        <div class="card-body p-5">
            <h2 class="text-center mb-4">Konfigurasi MQTT</h2>
            <p class="text-center text-muted mb-4">Silakan masukkan username dan password untuk MQTT Mosquitto.</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="mqtt_username" class="form-label">Username MQTT</label>
                    <input type="text" class="form-control" id="mqtt_username" name="mqtt_username" required>
                    <div class="invalid-feedback">Username MQTT harus diisi.</div>
                </div>
                <div class="mb-3 password-container">
                    <label for="mqtt_password" class="form-label">Password MQTT</label>
                    <input type="password" class="form-control" id="mqtt_password" name="mqtt_password" required>
                    <i class="fas fa-eye" id="toggleMqttPassword"></i>
                    <div class="invalid-feedback">Password MQTT harus diisi.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Simpan</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validasi form Bootstrap
        (function () {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
        // Toggle visibilitas password
        const toggleMqttPassword = document.getElementById('toggleMqttPassword');
        const mqttPasswordField = document.getElementById('mqtt_password');
        toggleMqttPassword.addEventListener('click', function () {
            if (mqttPasswordField.type === 'password') {
                mqttPasswordField.type = 'text';
                toggleMqttPassword.classList.remove('fa-eye');
                toggleMqttPassword.classList.add('fa-eye-slash');
            } else {
                mqttPasswordField.type = 'password';
                toggleMqttPassword.classList.remove('fa-eye-slash');
                toggleMqttPassword.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>