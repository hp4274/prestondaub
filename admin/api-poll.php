<?php
require_once __DIR__ . '/config/auth.php';
require_login();
require_once __DIR__ . '/config/database.php';
header('Content-Type: application/json');

$form_type = $_GET['form_type'] ?? '';
$since = $_GET['since'] ?? '';

$where = [];
if ($since) {
    $since_escaped = $conn->real_escape_string($since);
    $where[] = "created_at > '$since_escaped'";
}
if ($form_type) {
    if ($form_type === 'financing') {
        $where[] = "form_type IN ('sba-loans', 'equipment-loans', 'bridge-loans', 'working-capital', 'abl-loans', 'commercial-real-estate')";
    } else {
        $ft_escaped = $conn->real_escape_string($form_type);
        $where[] = "form_type = '$ft_escaped'";
    }
}

$where_clause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
$result = $conn->query("SELECT * FROM contact_forms $where_clause ORDER BY created_at ASC");

$forms = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $forms[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'count' => count($forms),
    'forms' => $forms,
    'current_time' => date('Y-m-d H:i:s')
]);
