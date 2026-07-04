<?php
/**
 * Legacy MySQL bootstrap for archived admin scripts only.
 * Set LEGACY_MYSQL_* (see .env.example in this folder).
 * Production admin uses Node + Supabase — see server/
 */

define('DB_HOST', getenv('LEGACY_MYSQL_HOST') ?: 'localhost');
define('DB_USER', getenv('LEGACY_MYSQL_USER') ?: 'root');
define('DB_PASSWORD', getenv('LEGACY_MYSQL_PASSWORD') ?: '');
define('DB_NAME', getenv('LEGACY_MYSQL_NAME') ?: '');

if (DB_NAME === '') {
    die('LEGACY_MYSQL_NAME is not set — legacy MySQL tooling is disabled.');
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);

$retry = 0;
while ($conn->connect_error && $retry < 3) {
    sleep(1);
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);
    $retry++;
}

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error . ' — set LEGACY_MYSQL_* env vars for archived tooling.');
}

$create_db = $conn->query('CREATE DATABASE IF NOT EXISTS ' . DB_NAME);

if ($create_db === false) {
    $conn->select_db(DB_NAME);
} else {
    $conn->select_db(DB_NAME);
}

$conn->set_charset('utf8mb4');

require_once __DIR__ . '/../includes/auto-migrate.php';

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function escape_string($data) {
    global $conn;
    return $conn->real_escape_string($data);
}
