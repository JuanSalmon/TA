<?php
namespace Jubox\Web;

session_start();
error_log("[" . date('Y-m-d H:i:s') . "] Memeriksa akses admin/index.php, sesi: " . print_r($_SESSION, true));

// Pastikan hanya admin yang dapat mengakses
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    error_log("[" . date('Y-m-d H:i:s') . "] Akses ditolak untuk non-admin: " . ($_SESSION['name'] ?? 'tidak ada'));
    $_SESSION['error'] = 'Akses hanya untuk admin';
    header("Location: /login/index.php?error=access_denied");
    exit();
}

// Muat konfigurasi
$configResult = include __DIR__ . '/../config/config.php';
if (!is_array($configResult) || !isset($configResult['success']) || !$configResult['success']) {
    error_log("Konfigurasi tidak valid: " . print_r($configResult, true));
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
    error_log("Koneksi database gagal: " . $e->getMessage());
    $_SESSION['error'] = 'Koneksi database gagal';
    header("Location: /login/index.php?error=db_error");
    exit();
}

// Query untuk mengambil data pengguna
$users = [];
try {
    $stmt = $koneksi->query("SELECT id, name, allowed FROM user");
    $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Query gagal: " . $e->getMessage());
    $_SESSION['error'] = 'Gagal mengambil data pengguna';
    header("Location: /login/index.php?error=db_error");
    exit();
}

// Jumlah pengguna terdaftar
$registeredUsersCount = 0;
try {
    $stmt = $koneksi->query("SELECT COUNT(*) as total FROM user");
    $registeredUsersCount = $stmt->fetchColumn();
} catch (\PDOException $e) {
    error_log("Query gagal: " . $e->getMessage());
    $_SESSION['error'] = 'Gagal menghitung pengguna terdaftar';
    header("Location: /login/index.php?error=db_error");
    exit();
}

// Jumlah pengguna yang diizinkan
$users_allow = 0;
try {
    $stmt = $koneksi->query("SELECT COUNT(*) as count FROM user WHERE allowed = 1");
    $users_allow = $stmt->fetchColumn();
} catch (\PDOException $e) {
    error_log("Query gagal: " . $e->getMessage());
    $_SESSION['error'] = 'Gagal menghitung pengguna yang diizinkan';
    header("Location: /login/index.php?error=db_error");
    exit();
}

// Jumlah pengguna aktif
$activeUser = 0;
try {
    $stmt = $koneksi->query("SELECT COUNT(*) as count FROM user WHERE is_online = 1");
    $activeUser = $stmt->fetchColumn();
} catch (\PDOException $e) {
    error_log("Query gagal: " . $e->getMessage());
    $_SESSION['error'] = 'Gagal menghitung pengguna aktif';
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .preloader img {
            transform: scale(5);
        }
        .broker-text {
            position: absolute !important;
            font-size: 20px;
            font-weight: bold;
            color: #ffffff;
            white-space: nowrap;
        }
        body.light-theme .broker-text {
            color: #000000 !important;
        }
        body.dark-theme .broker-text {
            color: #ffffff;
        }
        #subscriptions-count, #active-users-count, #connected-users-count, #usersAllow {
            transition: all 0.3s ease;
        }
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
                            <span class="hide-menu">Home</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="/admin/index.php" aria-expanded="false">
                                <span><i class="fa fa-home"></i></span>
                                <span class="hide-menu">Home</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="/admin/index2.php" aria-expanded="false">
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
                                                <a class="text-muted text-decoration-none" href="/admin/index.php">Home</a>
                                            </li>
                                        </ol>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card w-100 position-relative overflow-hidden">
                        <div class="px-4 py-3 border-bottom">
                            <h4 class="card-title mb-0">User Statistics</h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-primary text-white h-100">
                                        <div class="card-body d-flex align-items-center justify-content-between">
                                            <div>
                                                <h5 class="card-title mb-0">User Aktif</h5>
                                                <h2 class="fw-bold mb-0" id="active-users-count"><?php echo htmlspecialchars($activeUser); ?></h2>
                                            </div>
                                            <div>
                                                <i class="fas fa-user-check fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-success text-white h-100">
                                        <div class="card-body d-flex align-items-center justify-content-between">
                                            <div>
                                                <h5 class="card-title mb-0">User Terdaftar</h5>
                                                <h2 class="fw-bold mb-0" id="registered-users-count"><?php echo htmlspecialchars($registeredUsersCount); ?></h2>
                                            </div>
                                            <div>
                                                <i class="fas fa-users fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-info text-white h-100">
                                        <div class="card-body d-flex align-items-center justify-content-between">
                                            <div>
                                                <h5 class="card-title mb-0">User Connected</h5>
                                                <h2 class="fw-bold mb-0" id="connected-users-count">0</h2>
                                            </div>
                                            <div>
                                                <i class="fas fa-plug fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-warning text-white h-100">
                                        <div class="card-body d-flex align-items-center justify-content-between">
                                            <div>
                                                <h5 class="card-title mb-0">User Allow</h5>
                                                <h2 class="fw-bold mb-0" id="usersAllow"><?php echo htmlspecialchars($users_allow); ?></h2>
                                            </div>
                                            <div>
                                                <i class="fas fa-user-plus fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                // MQTT Connection
                const brokerHost = 'iot-broker.pnk.ac.id';
const brokerPort = 8083;
const clientId = 'web_juan';
const username = 'juan';
const password = '123';
const topic = '$SYS/broker/clients/connected';

// Membuat koneksi WebSocket ke broker
const ws = new WebSocket(`ws://${brokerHost}:${brokerPort}/mqtt`);

// Fungsi untuk membuat pesan CONNECT MQTT dalam format biner
function createConnectMessage() {
    const protocolName = 'MQTT';
    const protocolLevel = 4; // MQTT 3.1.1
    const flags = 0xC2; // User Name Flag (1), Password Flag (1), Clean Session (1)
    const keepAlive = 60; // Keep alive 60 detik

    const encoder = new TextEncoder();
    const protocolNameBytes = encoder.encode(protocolName);
    const clientIdBytes = encoder.encode(clientId);
    const usernameBytes = encoder.encode(username);
    const passwordBytes = encoder.encode(password);

    // Menghitung panjang payload
    const payloadLength =
        2 + protocolNameBytes.length + // Protocol name
        1 + // Protocol level
        1 + // Flags
        2 + // Keep alive
        2 + clientIdBytes.length + // Client ID
        2 + usernameBytes.length + // Username
        2 + passwordBytes.length; // Password

    // Fixed header: CONNECT (0x10), panjang sisa
    const remainingLength = payloadLength;
    const buffer = new ArrayBuffer(2 + remainingLength);
    const view = new DataView(buffer);

    // Fixed header
    view.setUint8(0, 0x10); // CONNECT
    view.setUint8(1, remainingLength);

    // Payload
    let offset = 2;
    view.setUint16(offset, protocolNameBytes.length); offset += 2;
    protocolNameBytes.forEach((byte, i) => view.setUint8(offset + i, byte)); offset += protocolNameBytes.length;
    view.setUint8(offset, protocolLevel); offset += 1;
    view.setUint8(offset, flags); offset += 1;
    view.setUint16(offset, keepAlive); offset += 2;
    view.setUint16(offset, clientIdBytes.length); offset += 2;
    clientIdBytes.forEach((byte, i) => view.setUint8(offset + i, byte)); offset += clientIdBytes.length;
    view.setUint16(offset, usernameBytes.length); offset += 2;
    usernameBytes.forEach((byte, i) => view.setUint8(offset + i, byte)); offset += usernameBytes.length;
    view.setUint16(offset, passwordBytes.length); offset += 2;
    passwordBytes.forEach((byte, i) => view.setUint8(offset + i, byte)); offset += passwordBytes.length;

    return buffer;
}

// Fungsi untuk membuat pesan SUBSCRIBE MQTT dalam format biner
function createSubscribeMessage() {
    const packetId = 1; // ID paket sembarang
    const encoder = new TextEncoder();
    const topicBytes = encoder.encode(topic);

    // Menghitung panjang payload
    const payloadLength = 2 + topicBytes.length + 1; // Packet ID + topic length + topic + QoS
    const remainingLength = payloadLength;

    const buffer = new ArrayBuffer(2 + remainingLength);
    const view = new DataView(buffer);

    // Fixed header
    view.setUint8(0, 0x82); // SUBSCRIBE
    view.setUint8(1, remainingLength);

    // Packet ID
    view.setUint16(2, packetId);

    // Payload
    let offset = 4;
    view.setUint16(offset, topicBytes.length); offset += 2;
    topicBytes.forEach((byte, i) => view.setUint8(offset + i, byte)); offset += topicBytes.length;
    view.setUint8(offset, 0); // QoS 0

    return buffer;
}

// Event saat koneksi WebSocket terbuka
ws.onopen = () => {
    console.log('Terkoneksi ke broker!');
    // Kirim pesan CONNECT
    ws.send(createConnectMessage());
};

// Event saat menerima pesan
ws.onmessage = (event) => {
    try {
        // Konversi data ke ArrayBuffer
        const buffer = event.data;
        const view = new DataView(buffer);
        const type = (view.getUint8(0) >> 4); // Ambil tipe pesan dari fixed header

        if (type === 2) { // CONNACK
            const returnCode = view.getUint8(3);
            if (returnCode === 0) {
                console.log('Koneksi MQTT diterima');
                // Kirim pesan SUBSCRIBE setelah CONNACK diterima
                ws.send(createSubscribeMessage());
                console.log('Berlangganan ke topik:', topic);
            } else {
                console.error('Koneksi MQTT ditolak, kode:', returnCode);
                document.getElementById('connected-users-count').innerText = `Koneksi MQTT ditolak (kode: ${returnCode})`;
            }
        } else if (type === 3) { // PUBLISH
            let offset = 1;
            const remainingLength = view.getUint8(offset++); // Asumsi remainingLength < 128 untuk kesederhanaan
            const topicLength = view.getUint16(offset); offset += 2;
            const topicName = new TextDecoder().decode(buffer.slice(offset, offset + topicLength));
            offset += topicLength;
            const payload = new TextDecoder().decode(buffer.slice(offset));

            if (topicName === topic) {
                console.log('Pesan dari topik', topicName, ':', payload);
                document.getElementById('connected-users-count').innerText = payload;
            }
        } else if (type === 9) { // SUBACK
            console.log('Langganan diterima');
        }
    } catch (e) {
        console.error('Gagal memproses pesan:', e);
        document.getElementById('connected-users-count').innerText = 'Gagal memproses pesan';
    }
};

// Event saat terjadi error
ws.onerror = (error) => {
    console.error('Gagal koneksi:', error);
    document.getElementById('connected-users-count').innerText = 'Gagal koneksi';
};

// Event saat koneksi ditutup
ws.onclose = () => {
    console.log('Koneksi ditutup');
    document.getElementById('connected-users-count').innerText = 'Koneksi terputus';
};
            </script>
            <script src="../src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
            <script src="../src/assets/libs/simplebar/dist/simplebar.min.js"></script>
            <script src="../src/assets/js/theme/app.dark.init.js"></script>
            <script src="../src/assets/js/theme/theme.js"></script>
            <script src="../src/assets/js/theme/app.min.js"></script>
            <script src="../src/assets/js/theme/sidebarmenu.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js"></script>
        </div>
    </div>
</body>
</html>