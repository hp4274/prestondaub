<?php
// Public read-only endpoint for team roster
require_once __DIR__ . '/config/database.php';
header('Content-Type: application/json');

// Check settings to see if team module is enabled
$enabled = true;
$settings_res = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'team_module_enabled' LIMIT 1");
if ($settings_res && $settings_res->num_rows > 0) {
    $enabled = ($settings_res->fetch_assoc()['setting_value'] === '1');
}

if (!$enabled) {
    echo json_encode([
        'success' => false,
        'enabled' => false,
        'message' => 'Team roster is currently disabled.'
    ]);
    exit();
}

$result = $conn->query("SELECT id, name, designation, bio, photo_url, email FROM team_members WHERE status = 'active' ORDER BY display_order ASC, name ASC");

$members = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'enabled' => true,
    'members' => $members
]);
exit();
?>
