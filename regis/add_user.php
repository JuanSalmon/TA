<?php
// Sertakan file koneksi
include 'koneksi.php';

// Periksa apakah form sudah disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $name = $_POST['nama'];
    $password = $_POST['password']; // Hash password
    
    // Query untuk menambahkan user
    $sql = "INSERT INTO user_db (username, password) VALUES (? ,?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ss", $name, $password);

        // Eksekusi query
        if ($stmt->execute()) {
            header("Location: ../../../login2/index.html");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

$conn->close();
?>
