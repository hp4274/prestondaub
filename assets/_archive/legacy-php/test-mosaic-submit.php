<?php
/**
 * Simulate Mosaic form submission to test the complete flow
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

// Simulate POST data from the Mosaic form
$_POST = [
    'name' => 'John Smith',
    'email' => 'john.smith@company.com',
    'phone' => '(555) 123-4567',
    'organization' => 'Acme Sports Management',
    'title' => 'Director of Technology',
    'organization_type' => 'Professional Team',
    'interest' => ['Roster Optimization', 'Investment Intelligence'],
    'challenges' => 'We need better analytics and team management tools',
    'form_type' => 'mosaic'
];

$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = '192.168.1.100';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)';

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'preston_daub';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get and sanitize form data
    $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
    $email = isset($_POST["email"]) ? filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL) : "";
    $phone = isset($_POST["phone"]) ? trim($_POST["phone"]) : "";
    $organization = isset($_POST["organization"]) ? trim($_POST["organization"]) : "";
    $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
    $organization_type = isset($_POST["organization_type"]) ? trim($_POST["organization_type"]) : "";
    $interest = isset($_POST["interest"]) ? $_POST["interest"] : [];
    $challenges = isset($_POST["challenges"]) ? trim($_POST["challenges"]) : "";
    $form_type = "mosaic";
    
    // Validation
    if (empty($name) OR empty($email) OR empty($phone) OR empty($organization) OR empty($title) OR empty($organization_type) OR !filter_var($email, FILTER_VALIDATE_EMAIL) OR empty($interest)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Convert interest array to JSON string
    $interest_json = is_array($interest) ? json_encode($interest) : json_encode(array_filter(explode(",", $interest)));

    // Escape data for database
    $name = $conn->real_escape_string($name);
    $email = $conn->real_escape_string($email);
    $phone = $conn->real_escape_string($phone);
    $organization = $conn->real_escape_string($organization);
    $title = $conn->real_escape_string($title);
    $organization_type = $conn->real_escape_string($organization_type);
    $interest_json = $conn->real_escape_string($interest_json);
    $challenges = $conn->real_escape_string($challenges);
    $form_type = $conn->real_escape_string($form_type);
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $conn->real_escape_string($_SERVER['HTTP_USER_AGENT']);

    // Insert into database
    $query = "INSERT INTO contact_forms (name, email, phone, company, organization, organization_type, job_title, interests, goals_challenges, message, form_type, ip_address, user_agent, status, priority, created_at) 
              VALUES ('$name', '$email', '$phone', '$organization', '$organization', '$organization_type', '$title', '$interest_json', '$challenges', '', '$form_type', '$ip_address', '$user_agent', 'new', NULL, NOW())";

    echo json_encode([
        'test' => 'Mosaic Form Submission Simulation',
        'query' => $query,
        'form_data' => [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'organization' => $organization,
            'organization_type' => $organization_type,
            'job_title' => $title,
            'interests' => $interest_json,
            'goals_challenges' => $challenges,
            'form_type' => $form_type
        ]
    ]);

    if ($conn->query($query) === TRUE) {
        echo "\n✓ Insert successful! Record ID: " . $conn->insert_id;
        
        // Clean up test record
        $conn->query("DELETE FROM contact_forms WHERE id = " . $conn->insert_id);
        echo "\n✓ Test record cleaned up";
    } else {
        echo "\n✗ Insert failed: " . $conn->error;
    }
}

$conn->close();
?>
