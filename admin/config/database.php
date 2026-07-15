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

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'preston_daub');

// Disable strict exception throwing on MySQL errors so we can handle connection errors gracefully
mysqli_report(MYSQLI_REPORT_OFF);

try {
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $conn->set_charset('utf8mb4');
} catch (Throwable $e) {
    // If accessed via an API script or JSON request, return valid JSON instead of 500 HTML to prevent JS syntax errors
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($script, 'api-') !== false || strpos($script, 'submit-form.php') !== false || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(200); // Return 200 with fallback JSON so frontend JS never throws "Unexpected end of JSON input"
        }
        echo json_encode([
            'success' => false,
            'enabled' => false,
            'maintenance_mode' => false,
            'message' => 'Database connection unavailable or not yet configured: ' . $e->getMessage()
        ]);
        exit();
    } else {
        die("Database connection failed: " . $e->getMessage());
    }
}
?>
