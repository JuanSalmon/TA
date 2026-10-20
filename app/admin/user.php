<?php

include '../config/config.php';

// Fetch all users from the database
$sql = "SELECT * FROM users";
$result = $koneksi->query($sql);

// Read allowed users from the file
$allowedUsers = [];
if (file_exists("user.txt")) {
    $lines = file("user.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Extract username from each line in the file
        if (preg_match('/^(\w+)$/', $line, $matches)) {
            $allowedUsers[] = $matches[1];
        }
    }
}
?>