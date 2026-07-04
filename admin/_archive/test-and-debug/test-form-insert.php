<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'preston_daub';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Test data from contact form
$test_data = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '1234567890',
    'organization' => 'Test Organization',
    'organization_type' => 'General Info',
    'service' => '',
    'job_title' => '',
    'interests' => '[]',
    'goals_challenges' => 'This is a test message',
    'message' => '', // Empty message
    'checkbox' => '',
    'form_type' => 'contact',
    'module_data' => []
];

// Escape all data
$name = $conn->real_escape_string($test_data['name']);
$email = $conn->real_escape_string($test_data['email']);
$phone = $conn->real_escape_string($test_data['phone']);
$company = $conn->real_escape_string($test_data['company'] ?? '');
$organization = $conn->real_escape_string($test_data['organization']);
$organization_type = $conn->real_escape_string($test_data['organization_type']);
$service = $conn->real_escape_string($test_data['service']);
$job_title = $conn->real_escape_string($test_data['job_title']);
$interests = $conn->real_escape_string($test_data['interests']);
$goals_challenges = $conn->real_escape_string($test_data['goals_challenges']);
$message = $conn->real_escape_string($test_data['message'] ?? '');
$checkbox = $conn->real_escape_string($test_data['checkbox']);
$form_type = $conn->real_escape_string($test_data['form_type']);
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$user_agent = $conn->real_escape_string($_SERVER['HTTP_USER_AGENT'] ?? 'Test');
$form_data_json = $conn->real_escape_string(json_encode($test_data['module_data']));

echo "<h2>Testing Form Submission</h2>";
echo "<p>Inserting test data...</p>";

$query = "INSERT INTO contact_forms (name, email, phone, company, organization, organization_type, service, job_title, interests, goals_challenges, message, checkbox, form_type, form_data, ip_address, user_agent, status) 
          VALUES ('$name', '$email', '$phone', '$company', '$organization', '$organization_type', '$service', '$job_title', '$interests', '$goals_challenges', '$message', '$checkbox', '$form_type', '$form_data_json', '$ip_address', '$user_agent', 'new')";

echo "<p><strong>Query:</strong><br>";
echo "<pre>" . htmlspecialchars($query) . "</pre>";

if ($conn->query($query)) {
    echo "<p style='color: green;'><strong>✅ Success!</strong> Form submitted successfully!</p>";
    echo "<p>Last Insert ID: " . $conn->insert_id . "</p>";
} else {
    echo "<p style='color: red;'><strong>❌ Error:</strong> " . $conn->error . "</p>";
}

$conn->close();
?>
