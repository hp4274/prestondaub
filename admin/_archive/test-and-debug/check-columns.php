<?php
$conn = new mysqli('localhost', 'root', '', 'preston_daub');
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

$result = $conn->query("DESCRIBE contact_forms");
echo "Columns in contact_forms table:\n";
echo "================================\n";
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

$conn->close();
?>
