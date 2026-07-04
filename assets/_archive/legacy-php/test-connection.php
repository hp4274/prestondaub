<?php
/**
 * Test database connection and schema
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'preston_daub';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $conn->connect_error
    ]);
    exit;
}

$response = [
    'success' => true,
    'database' => $db_name,
    'table_exists' => false,
    'columns' => [],
    'required_columns' => [
        'id', 'name', 'email', 'phone', 'company', 'organization', 
        'organization_type', 'job_title', 'interests', 'goals_challenges', 
        'message', 'form_type', 'ip_address', 'user_agent', 'status', 'priority'
    ],
    'missing_columns' => [],
    'errors' => []
];

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'contact_forms'");
if ($result && $result->num_rows > 0) {
    $response['table_exists'] = true;
    
    // Get columns
    $columns_result = $conn->query("DESCRIBE contact_forms");
    $existing_columns = [];
    
    while ($col = $columns_result->fetch_assoc()) {
        $existing_columns[] = $col['Field'];
        $response['columns'][] = [
            'name' => $col['Field'],
            'type' => $col['Type'],
            'null' => $col['Null'],
            'key' => $col['Key'],
            'default' => $col['Default']
        ];
    }
    
    // Check for missing columns
    foreach ($response['required_columns'] as $col) {
        if (!in_array($col, $existing_columns)) {
            $response['missing_columns'][] = $col;
        }
    }
} else {
    $response['errors'][] = "contact_forms table does not exist";
}

// Try a test insert (if table exists)
if ($response['table_exists']) {
    $test_data = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '555-1234',
        'company' => 'Test Company',
        'organization' => 'Test Organization',
        'organization_type' => 'Test Type',
        'job_title' => 'Test Title',
        'interests' => json_encode(['interest1', 'interest2']),
        'goals_challenges' => 'Test goals',
        'message' => 'Test message',
        'form_type' => 'mosaic',
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'status' => 'new'
    ];
    
    $columns = implode(', ', array_keys($test_data));
    $values = implode("', '", array_map(function($v) use ($conn) {
        return $conn->real_escape_string($v);
    }, $test_data));
    
    $test_query = "INSERT INTO contact_forms ($columns) VALUES ('$values')";
    
    if ($conn->query($test_query) === TRUE) {
        $response['test_insert'] = [
            'success' => true,
            'insert_id' => $conn->insert_id,
            'message' => 'Test insert successful'
        ];
        
        // Try to delete the test record
        $conn->query("DELETE FROM contact_forms WHERE id = " . $conn->insert_id);
    } else {
        $response['test_insert'] = [
            'success' => false,
            'error' => $conn->error,
            'query' => $test_query
        ];
    }
}

$conn->close();

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
