<?php
require_once 'config/database.php';

echo '<style>body { font-family: Arial; margin: 20px; } table { border-collapse: collapse; margin: 20px 0; } td, th { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background: #f0f0f0; }</style>';
echo '<h2>Database Diagnostic - Forms Analysis</h2>';

echo '<h3>All form types in database:</h3>';
$result = $conn->query('SELECT DISTINCT form_type FROM contact_forms ORDER BY form_type');
echo '<table><tr><th>Form Type</th><th>Total Count</th><th>New</th><th>Read</th><th>Spam</th></tr>';
$total_all = 0;
while($row = $result->fetch_assoc()) {
    $form_type = $row['form_type'];
    $ft_escaped = $conn->real_escape_string($form_type);
    $count = $conn->query("SELECT COUNT(*) as c FROM contact_forms WHERE form_type = '$ft_escaped'")->fetch_assoc()['c'];
    $new = $conn->query("SELECT COUNT(*) as c FROM contact_forms WHERE form_type = '$ft_escaped' AND status = 'new'")->fetch_assoc()['c'];
    $read = $conn->query("SELECT COUNT(*) as c FROM contact_forms WHERE form_type = '$ft_escaped' AND status = 'read'")->fetch_assoc()['c'];
    $spam = $conn->query("SELECT COUNT(*) as c FROM contact_forms WHERE form_type = '$ft_escaped' AND status = 'spam'")->fetch_assoc()['c'];
    $total_all += $count;
    echo "<tr><td>$form_type</td><td>$count</td><td>$new</td><td>$read</td><td>$spam</td></tr>";
}
echo "</table>";
echo "<p><strong>TOTAL ALL FORMS:</strong> $total_all</p>";

echo '<h3>Financing Page Query Analysis:</h3>';
$query = "form_type LIKE '%financing%' OR form_type LIKE '%loan%' OR form_type = 'business-loans' OR form_type = 'sba-loans' OR form_type = 'equipment-loans' OR form_type = 'bridge-loans' OR form_type = 'working-capital'";

$total = $conn->query("SELECT COUNT(*) as count FROM contact_forms WHERE $query")->fetch_assoc()['count'];
$new = $conn->query("SELECT COUNT(*) as count FROM contact_forms WHERE ($query) AND status = 'new'")->fetch_assoc()['count'];
$read = $conn->query("SELECT COUNT(*) as count FROM contact_forms WHERE ($query) AND status = 'read'")->fetch_assoc()['count'];
$spam = $conn->query("SELECT COUNT(*) as count FROM contact_forms WHERE ($query) AND status = 'spam'")->fetch_assoc()['count'];

echo '<table><tr><th>Metric</th><th>Count</th></tr>';
echo "<tr><td>Total Financing Forms</td><td>$total</td></tr>";
echo "<tr><td>New (Unread)</td><td>$new</td></tr>";
echo "<tr><td>Read (Reviewed)</td><td>$read</td></tr>";
echo "<tr><td>Spam/Invalid</td><td>$spam</td></tr>";
echo '</table>';

echo '<h3>All Records with Status = "new":</h3>';
$result = $conn->query("SELECT id, form_type, name, email, status FROM contact_forms WHERE status = 'new' ORDER BY created_at DESC");
echo '<table><tr><th>ID</th><th>Form Type</th><th>Name</th><th>Email</th><th>Status</th></tr>';
$newCount = 0;
while($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['form_type']}</td><td>{$row['name']}</td><td>{$row['email']}</td><td>{$row['status']}</td></tr>";
    $newCount++;
}
echo '</table>';
echo "<p><strong>Total NEW records found:</strong> $newCount</p>";

echo '<h3>Financing Forms Matching Current Query:</h3>';
$result = $conn->query("SELECT id, form_type, name, email, status, created_at FROM contact_forms WHERE $query ORDER BY created_at DESC LIMIT 20");
echo '<table><tr><th>ID</th><th>Form Type</th><th>Name</th><th>Email</th><th>Status</th><th>Created</th></tr>';
while($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['form_type']}</td><td>{$row['name']}</td><td>{$row['email']}</td><td>{$row['status']}</td><td>{$row['created_at']}</td></tr>";
}
echo '</table>';
?>
