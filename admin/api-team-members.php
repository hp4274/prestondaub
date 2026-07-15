<?php
// Public read-only endpoint for team roster
require_once __DIR__ . '/config/database.php';
header('Content-Type: application/json');

// Check settings to see if team module is enabled
$enabled = true;
try {
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $settings_res = @$conn->query("SELECT setting_value FROM settings WHERE setting_key = 'team_module_enabled' LIMIT 1");
        if ($settings_res && $settings_res->num_rows > 0) {
            $enabled = ($settings_res->fetch_assoc()['setting_value'] === '1');
        }
    }
} catch (Throwable $e) {
    // Fallback if settings table does not exist
}

if (!$enabled) {
    echo json_encode([
        'success' => false,
        'enabled' => false,
        'message' => 'Team roster is currently disabled.'
    ]);
    exit();
}

$members = [];
try {
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $result = @$conn->query("SELECT id, name, designation, bio, photo_url, email FROM team_members WHERE status = 'active' ORDER BY display_order ASC, name ASC");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $members[] = $row;
            }
        }
    }
} catch (Throwable $e) {
    // Fallback if team_members table does not exist
}

echo json_encode([
    'success' => true,
    'enabled' => true,
    'members' => $members
]);
exit();
?>
