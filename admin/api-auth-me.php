<?php
require_once __DIR__ . '/config/auth.php';
header('Content-Type: application/json');

if (is_logged_in()) {
    echo json_encode([
        'success' => true,
        'admin' => [
            'id' => $_SESSION['admin_id'],
            'email' => $_SESSION['admin_email'],
            'name' => $_SESSION['admin_name'] ?? 'Admin',
            'role' => 'admin'
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
}
