<?php
require_once __DIR__ . '/config/auth.php';
require_login();
require_once __DIR__ . '/config/database.php';
header('Content-Type: application/json');

$formId = $_GET['id'] ?? '';
if (!$formId) {
    echo json_encode(['success' => false, 'message' => 'Form ID is required']);
    exit();
}

$formId_escaped = $conn->real_escape_string($formId);
$result = $conn->query("SELECT * FROM contact_forms WHERE id = '$formId_escaped' LIMIT 1");

if ($result && $result->num_rows > 0) {
    $form = $result->fetch_assoc();
    
    // Parse form_data if stored as JSON
    $form_data = [];
    if (isset($form['form_data']) && $form['form_data']) {
        $form_data = json_decode($form['form_data'], true) ?? [];
    }
    
    // Merge form_data back as flat properties or construct a standard response
    $response_data = array_merge($form, $form_data);
    
    echo json_encode([
        'success' => true,
        'form' => $response_data
    ]);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Form not found']);
}
