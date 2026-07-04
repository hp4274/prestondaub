<?php
/**
 * Simple Test for Prospera Form Handler
 */

// Test 1: Check if file exists and can be accessed
echo "Test 1: File Access\n";
echo "prospera-submit.php exists: " . (file_exists('prospera-submit.php') ? "YES ✓" : "NO ✗") . "\n\n";

// Test 2: Check database connection
echo "Test 2: Database Connection\n";
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'preston_daub';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($conn->connect_error) {
    echo "Database ERROR: " . $conn->connect_error . "\n";
} else {
    echo "Database connected successfully ✓\n";
    
    // Check if contact_forms table exists
    $result = $conn->query("SHOW TABLES LIKE 'contact_forms'");
    if ($result && $result->num_rows > 0) {
        echo "contact_forms table exists ✓\n";
        
        // Show table structure
        $columns = $conn->query("DESCRIBE contact_forms");
        echo "\nTable columns:\n";
        while ($col = $columns->fetch_assoc()) {
            echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    } else {
        echo "contact_forms table NOT found ✗\n";
    }
}

echo "\n\nTest 3: Test POST Data\n";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "POST data received:\n";
    echo "  name: " . ($_POST['name'] ?? 'EMPTY') . "\n";
    echo "  email: " . ($_POST['email'] ?? 'EMPTY') . "\n";
    echo "  phone: " . ($_POST['phone'] ?? 'EMPTY') . "\n";
    echo "  company: " . ($_POST['company'] ?? 'EMPTY') . "\n";
    echo "  service: " . ($_POST['service'] ?? 'EMPTY') . "\n";
    echo "  message: " . ($_POST['message'] ?? 'EMPTY') . "\n";
    echo "  form_type: " . ($_POST['form_type'] ?? 'EMPTY') . "\n";
} else {
    echo "No POST data - send a form submission\n";
}

echo "\n✓ Test file complete\n";
?>
