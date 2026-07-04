<?php
/**
 * Legacy session auth for archived admin PHP only.
 */

session_start();

function legacy_admin_login_url() {
    $script = $_SERVER['SCRIPT_NAME'] ?? '/admin/login.html';
    if (preg_match('#(/admin/).*$#', $script, $m)) {
        return $m[1] . 'login.html';
    }
    return '/admin/login.html';
}

function is_logged_in() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_email']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . legacy_admin_login_url());
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
    header('Location: ' . legacy_admin_login_url());
    exit();
}
