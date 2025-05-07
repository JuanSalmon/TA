<?php
// Sertakan file koneksi
include 'koneksi.php';

// Set header untuk respons JSON
header('Content-Type: application/json');

// Periksa apakah form sudah disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $name = $_POST['nama'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validasi input
    if (empty($name) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username dan password tidak boleh kosong.']);
        exit;
    }

    if (!preg_match('/^[A-Za-z0-9]+$/', $name)) {
        echo json_encode(['success' => false, 'message' => 'Username hanya boleh berisi huruf atau angka.']);
        ob_end_flush();
        exit;
    }

    if (!preg_match('/^[A-Za-z0-9]{8,}$/', $password)) {
        echo json_encode(['success' => false, 'message' => 'Password harus tepat 8 karakter huruf atau angka.']);
        exit;
    }

    // Cek apakah username sudah ada
    $sql = "SELECT COUNT(*) FROM users WHERE name = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            echo json_encode(['success' => false, 'message' => 'Username sudah terdaftar.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memeriksa username: ' . $conn->error]);
        exit;
    }

    // Hash password untuk keamanan
    // $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Query untuk menambahkan user
    $sql = "INSERT INTO users (name, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ss", $name, $password);

        // Eksekusi query
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Registrasi berhasil! Silakan login.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mempersiapkan query: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
}

$conn->close();
exit;
?>