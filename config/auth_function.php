<?php
function authenticateUser($koneksi, $username, $password) {
    // Check admin table first
    $stmt = $koneksi->prepare("SELECT * FROM admin WHERE name = :name LIMIT 1");
    $stmt->execute(['name' => $username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    error_log("Debug - Admin query result: " . ($admin ? "found" : "not found"));
    
    // If admin credentials are valid, return immediately with success
    if ($admin && $password === $admin['password']) {
        error_log("Debug - Admin authentication successful");
        return [
            'success' => true,
            'session_data' => [
                'id' => $admin['id'],
                'username' => $admin['name'],
                'name' => $admin['name'],
                'is_admin' => true
            ],
            'redirect' => '../admin/index.php'
        ];
    }

    // If admin credentials are invalid, return error without checking users table
    if ($admin) {
        error_log("Debug - Admin password mismatch");
        return [
            'success' => false,
            'session_data' => [
                'error' => 'Password salah'
            ]
        ];
    }

    // Only check users table if username not found in admin table
    $stmt = $koneksi->prepare("SELECT * FROM users WHERE name = :name AND allowed = 1 LIMIT 1");
    $stmt->execute(['name' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $password === $user['password']) {
        error_log("Debug - User authentication successful");
        return [
            'success' => true,
            'session_data' => [
                'id' => $user['id'],
                'username' => $user['name'],
                'name' => $user['name'],
                'is_online' => $user['is_online'],
                'allowed' => $user['allowed'],
                'is_admin' => false
            ],
            'redirect' => '../user/index.php'
        ];
    }

    error_log("Debug - Authentication failed for username: " . $username);
    return [
        'success' => false,
        'session_data' => [
            'error' => 'Username atau password salah'
        ]
    ];
}
?>