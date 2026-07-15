<?php
// CORS headers to support cross-origin API calls from Live Server
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }
}

require_once __DIR__ . '/config/database.php';
header('Content-Type: application/json');

$val = '0';
try {
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $res = @$conn->query("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode' LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $val = $res->fetch_assoc()['setting_value'] ?? '0';
        }
    }
} catch (Throwable $e) {
    // Fallback if settings table does not exist yet
}

echo json_encode([
    'maintenance_mode' => ($val === '1')
]);
exit();
?>
