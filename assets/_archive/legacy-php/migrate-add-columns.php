<?php
/**
 * Add missing columns to contact_forms table
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

echo "Adding missing columns to contact_forms table...\n";
echo "==============================================\n\n";

// Array of columns to add with their positions
$columns_to_add = [
    [
        'name' => 'organization',
        'definition' => "VARCHAR(255) COMMENT 'For Mosaic form'",
        'after' => 'company'
    ],
    [
        'name' => 'organization_type',
        'definition' => "VARCHAR(100) COMMENT 'For Mosaic form - Professional Team, Investment Firm, etc.'",
        'after' => 'organization'
    ]
];

foreach ($columns_to_add as $col) {
    // Check if column already exists
    $check = $conn->query("SHOW COLUMNS FROM contact_forms LIKE '{$col['name']}'");
    
    if ($check && $check->num_rows === 0) {
        $query = "ALTER TABLE contact_forms ADD COLUMN {$col['name']} {$col['definition']} AFTER {$col['after']}";
        
        if ($conn->query($query) === TRUE) {
            echo "✓ Added column: {$col['name']}\n";
        } else {
            echo "✗ Error adding column {$col['name']}: " . $conn->error . "\n";
        }
    } else {
        echo "• Column already exists: {$col['name']}\n";
    }
}

echo "\n✓ Migration complete!\n";
echo "\nCurrent schema:\n";
echo "==============\n";

$result = $conn->query("DESCRIBE contact_forms");
while ($col = $result->fetch_assoc()) {
    echo $col['Field'] . " - " . $col['Type'] . "\n";
}

$conn->close();
?>
