<?php
// CORS headers to support cross-origin API calls from Live Server
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function legacy_admin_login_url() {
    return 'login.php';
}

function is_logged_in() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_email']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

function get_current_admin() {
    return isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
}

function get_current_admin_email() {
    return isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : null;
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>
