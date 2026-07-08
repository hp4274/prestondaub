<?php
require_once __DIR__ . '/config/auth.php';
require_login();
require_once __DIR__ . '/config/database.php';
header('Content-Type: application/json');

$module = $_GET['module'] ?? '';
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = intval($_GET['page'] ?? 1);
$per_page = intval($_GET['per_page'] ?? 20);

// Build query
$where = [];
if ($module) {
    if ($module === 'financing') {
        $where[] = "form_type IN ('sba-loans', 'equipment-loans', 'bridge-loans', 'working-capital', 'abl-loans', 'commercial-real-estate')";
    } else {
        $module_escaped = $conn->real_escape_string($module);
        $where[] = "form_type = '$module_escaped'";
    }
}
if ($status && $status !== 'all') {
    $status_escaped = $conn->real_escape_string($status);
    $where[] = "status = '$status_escaped'";
}
if ($search) {
    $search_escaped = $conn->real_escape_string($search);
    $where[] = "(name LIKE '%$search_escaped%' OR email LIKE '%$search_escaped%' OR phone LIKE '%$search_escaped%')";
}

$where_clause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Count total
$count_result = $conn->query("SELECT COUNT(*) as count FROM contact_forms $where_clause");
$total_rows = $count_result->fetch_assoc()['count'] ?? 0;
$total_pages = ceil($total_rows / $per_page);
if ($total_pages < 1) $total_pages = 1;

// Fetch forms
$offset = ($page - 1) * $per_page;
$query = "SELECT * FROM contact_forms $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$result = $conn->query($query);
$forms = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $forms[] = $row;
    }
}

// Stats (always based on module filter)
$stats_where = [];
if ($module) {
    if ($module === 'financing') {
        $stats_where[] = "form_type IN ('sba-loans', 'equipment-loans', 'bridge-loans', 'working-capital', 'abl-loans', 'commercial-real-estate')";
    } else {
        $module_escaped = $conn->real_escape_string($module);
        $stats_where[] = "form_type = '$module_escaped'";
    }
}
$stats_clause = count($stats_where) > 0 ? "WHERE " . implode(" AND ", $stats_where) : "";

$total_stat = $conn->query("SELECT COUNT(*) as count FROM contact_forms $stats_clause")->fetch_assoc()['count'] ?? 0;

$new_stat_where = $stats_where;
$new_stat_where[] = "status = 'new'";
$new_clause = "WHERE " . implode(" AND ", $new_stat_where);
$new_stat = $conn->query("SELECT COUNT(*) as count FROM contact_forms $new_clause")->fetch_assoc()['count'] ?? 0;

$read_stat_where = $stats_where;
$read_stat_where[] = "status = 'read'";
$read_clause = "WHERE " . implode(" AND ", $read_stat_where);
$read_stat = $conn->query("SELECT COUNT(*) as count FROM contact_forms $read_clause")->fetch_assoc()['count'] ?? 0;

$spam_stat_where = $stats_where;
$spam_stat_where[] = "status = 'spam'";
$spam_clause = "WHERE " . implode(" AND ", $spam_stat_where);
$spam_stat = $conn->query("SELECT COUNT(*) as count FROM contact_forms $spam_clause")->fetch_assoc()['count'] ?? 0;

echo json_encode([
    'success' => true,
    'forms' => $forms,
    'stats' => [
        'total' => intval($total_stat),
        'new' => intval($new_stat),
        'read' => intval($read_stat),
        'spam' => intval($spam_stat)
    ],
    'pagination' => [
        'total' => intval($total_rows),
        'current_page' => $page,
        'per_page' => $per_page,
        'total_pages' => $total_pages
    ]
]);
