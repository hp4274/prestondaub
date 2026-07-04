<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'preston_daub';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Contact Forms Table Structure</h2>";

$result = $conn->query("DESCRIBE contact_forms");

if (!$result) {
    die("Error: " . $conn->error);
}

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Available Columns:</h3>";
echo "<pre>" . implode(", ", $columns) . "</pre>";

// Test insert query
echo "<h3>Test Insert Query</h3>";

$test_query = "INSERT INTO contact_forms (name, email, phone, company, organization, organization_type, service, job_title, interests, goals_challenges, message, checkbox, form_type, form_data, ip_address, user_agent, status, created_at) 
              VALUES ('Test', 'test@example.com', '1234567890', '', 'Test Org', 'General Info', '', '', '[]', '', '', '', 'contact', '{}', '127.0.0.1', 'Mozilla', 'new', '2026-02-27 12:00:00')";

echo "<pre>" . htmlspecialchars($test_query) . "</pre>";

if ($conn->query($test_query)) {
    echo "<p style='color: green;'><strong>✅ Query succeeded!</strong></p>";
} else {
    echo "<p style='color: red;'><strong>❌ Query failed:</strong> " . $conn->error . "</p>";
}

$conn->close();
?>
