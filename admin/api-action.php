<?php
require_once __DIR__ . '/config/auth.php';
require_login();
require_once __DIR__ . '/config/database.php';
header('Content-Type: application/json');

$formId = $_POST['form_id'] ?? '';
$action = $_POST['action'] ?? '';
$newStatus = $_POST['new_status'] ?? '';

if (!$formId) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $formId = $data['form_id'] ?? '';
    $action = $data['action'] ?? '';
    $newStatus = $data['new_status'] ?? '';
}

if (!$formId) {
    echo json_encode(['success' => false, 'message' => 'Form ID is required']);
    exit();
}

$formId_escaped = $conn->real_escape_string($formId);

if ($action === 'delete') {
    $conn->query("DELETE FROM contact_forms WHERE id = '$formId_escaped'");
    echo json_encode(['success' => true, 'message' => 'Deleted successfully']);
    exit();
} else if ($newStatus) {
    $status_escaped = $conn->real_escape_string($newStatus);
    $conn->query("UPDATE contact_forms SET status = '$status_escaped' WHERE id = '$formId_escaped'");
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
