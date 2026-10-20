<?php
session_start();
session_unset();
session_destroy();
error_log("[" . date('Y-m-d H:i:s') . "] Sesi dibersihkan");
header("Location: /login/index.php");
exit();
?>