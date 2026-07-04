<?php
/**
 * Debug script to check form data in database
 */

require_once 'config/database.php';

// Get all financing forms
$query = "SELECT id, form_type, name, email, form_data FROM contact_forms 
          WHERE form_type IN ('financing', 'business-loans', 'sba-loans', 'equipment-loans', 'bridge-loans', 'working-capital')
          ORDER BY id DESC LIMIT 5";

$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Financing Forms Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .form-entry { background: white; padding: 15px; margin: 10px 0; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .id { color: #0752c5; font-weight: bold; }
        .form-data { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; margin: 10px 0; white-space: pre-wrap; word-wrap: break-word; }
        .empty { color: #dc3545; }
        .filled { color: #28a745; }
    </style>
</head>
<body>
<h1>Last 5 Financing Forms</h1>";

$count = 0;
while ($form = $result->fetch_assoc()) {
    $count++;
    $form_data = json_decode($form['form_data'], true);
    $has_data = !empty($form_data) && count(array_filter($form_data)) > 0;
    $status = $has_data ? '<span class="filled">✓ HAS DATA</span>' : '<span class="empty">✗ EMPTY</span>';
    
    echo "<div class='form-entry'>
        <p><span class='id'>Form ID:</span> {$form['id']} | <span class='id'>Type:</span> {$form['form_type']} | {$status}</p>
        <p><span class='id'>Name:</span> {$form['name']}</p>
        <p><span class='id'>Email:</span> {$form['email']}</p>
        <div class='form-data'>" . htmlspecialchars(json_encode($form_data, JSON_PRETTY_PRINT)) . "</div>
    </div>";
}

if ($count === 0) {
    echo "<p>No financing forms found in database.</p>";
}

echo "</body></html>";
?>
