<?php
/**
 * Get current table schema
 */

$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'preston_daub';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Columns in contact_forms table:\n";
echo "================================\n";

$result = $conn->query("DESCRIBE contact_forms");

while ($col = $result->fetch_assoc()) {
    echo $col['Field'] . " - " . $col['Type'] . " (Null: " . $col['Null'] . ")\n";
}

$conn->close();
?>
