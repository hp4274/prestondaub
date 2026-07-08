<?php
require_once __DIR__ . '/config/auth.php';
require_login();
require_once __DIR__ . '/config/database.php';
header('Content-Type: application/json');

// Get stats
$total = $conn->query("SELECT COUNT(*) as count FROM contact_forms")->fetch_assoc()['count'] ?? 0;
$new = $conn->query("SELECT COUNT(*) as count FROM contact_forms WHERE status = 'new'")->fetch_assoc()['count'] ?? 0;
$read = $conn->query("SELECT COUNT(*) as count FROM contact_forms WHERE status = 'read'")->fetch_assoc()['count'] ?? 0;
$spam = $conn->query("SELECT COUNT(*) as count FROM contact_forms WHERE status = 'spam'")->fetch_assoc()['count'] ?? 0;

$news_total = 0;
$news_check = $conn->query("SHOW TABLES LIKE 'news'");
if ($news_check && $news_check->num_rows > 0) {
    $news_total = $conn->query("SELECT COUNT(*) as count FROM news")->fetch_assoc()['count'] ?? 0;
}

// Get recent forms
$recent_forms = [];
$result = $conn->query("SELECT name, email, form_type, status, created_at FROM contact_forms ORDER BY created_at DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent_forms[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'stats' => [
        'total' => intval($total),
        'new' => intval($new),
        'read' => intval($read),
        'spam' => intval($spam),
        'news_total' => intval($news_total)
    ],
    'recent_forms' => $recent_forms
]);
