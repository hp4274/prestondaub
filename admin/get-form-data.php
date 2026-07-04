<?php
$id = $_GET['id'] ?? '';
if (!$id) {
    header("Content-Type: application/json");
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Form ID is required"
    ]);
    exit();
}

header("Location: /api/admin/forms/" . rawurlencode($id) . "/detail", true, 307);
exit();
