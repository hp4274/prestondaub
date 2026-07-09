<?php
require_once __DIR__ . '/config/database.php';
header('Content-Type: application/json');

// Parse request payload
$input = [];
$contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

if (stripos($contentType, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? [];
} else {
    $input = $_REQUEST; // Merges $_GET and $_POST
}

// Extract form type
$formType = strtolower(trim($input['form_type'] ?? $_GET['form_type'] ?? 'contact'));

// Map name (handling split first/last names)
$name = trim($input['name'] ?? $input['fullName'] ?? $input['fullNameInput'] ?? '');
if ($name === '') {
    $firstName = trim($input['firstName'] ?? $input['first_name'] ?? '');
    $lastName = trim($input['lastName'] ?? $input['last_name'] ?? '');
    if ($firstName !== '' || $lastName !== '') {
        $name = trim($firstName . ' ' . $lastName);
    }
}

// Map standard fields
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? $input['phoneNumber'] ?? $input['phone_number'] ?? '');
$company = trim($input['company'] ?? $input['legal_business_name'] ?? '');
$organization = trim($input['organization'] ?? '');
$organization_type = trim($input['organization_type'] ?? $input['organizationType'] ?? '');
$service = trim($input['service'] ?? '');
$job_title = trim($input['job_title'] ?? $input['jobTitle'] ?? '');
$interests = trim($input['interests'] ?? '');
$goals_challenges = trim($input['goals_challenges'] ?? $input['goalsChallenges'] ?? '');
$message = trim($input['message'] ?? '');
$checkbox = trim($input['checkbox'] ?? $input['privacy'] ?? '');

// Client meta information
$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

$executed = false;

if ($formType === 'contact') {
    if ($name === '' || $email === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Name and email are required fields.']);
        exit();
    }
    if ($message === '') {
        $message = 'Contact form submission';
    }
    
    $stmt = $conn->prepare("INSERT INTO contact_submissions (name, email, phone, company, message, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssssss", $name, $email, $phone, $company, $message, $ip_address, $user_agent);
        $executed = $stmt->execute();
        $stmt->close();
    }
}
elseif (in_array($formType, ['sba-loans', 'equipment-loans', 'bridge-loans', 'working-capital', 'abl-loans', 'commercial-real-estate', 'financing', 'business-loans', 'line-of-credit', 'conventional'])) {
    $firstName = trim($input['firstName'] ?? $input['first_name'] ?? '');
    $lastName = trim($input['lastName'] ?? $input['last_name'] ?? '');
    if ($firstName === '' && $lastName === '') {
        $parts = explode(' ', $name, 2);
        $firstName = $parts[0] ?? $name;
        $lastName = $parts[1] ?? '';
    }
    
    if ($firstName === '' || $email === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'First name and email are required.']);
        exit();
    }

    $formSubtype = strtolower(trim($input['financing_solution_type'] ?? $formType));

    // Compile interests details from application form fields
    $interests_parts = [];
    if (isset($input['years_in_business']) && trim($input['years_in_business']) !== '') {
        $interests_parts[] = "Years in Business: " . trim($input['years_in_business']);
    }
    if (isset($input['yearly_revenue']) && trim($input['yearly_revenue']) !== '') {
        $interests_parts[] = "Yearly Revenue: " . trim($input['yearly_revenue']);
    }
    if (isset($input['loan_amount']) && trim($input['loan_amount']) !== '') {
        $interests_parts[] = "Loan Amount Needed: " . trim($input['loan_amount']);
    }
    if (isset($input['equipment_manufacturer']) && trim($input['equipment_manufacturer']) !== '') {
        $interests_parts[] = "\n--- Equipment Details ---";
        $interests_parts[] = "Manufacturer: " . trim($input['equipment_manufacturer']);
        if (isset($input['equipment_model']) && trim($input['equipment_model']) !== '') $interests_parts[] = "Model: " . trim($input['equipment_model']);
        if (isset($input['equipment_year']) && trim($input['equipment_year']) !== '') $interests_parts[] = "Year: " . trim($input['equipment_year']);
        if (isset($input['equipment_serial_number']) && trim($input['equipment_serial_number']) !== '') $interests_parts[] = "Serial Number: " . trim($input['equipment_serial_number']);
        if (isset($input['equipment_specifications']) && trim($input['equipment_specifications']) !== '') $interests_parts[] = "Specs: " . trim($input['equipment_specifications']);
    }
    $interests = implode("\n", $interests_parts);

    // Compile goals and guarantor details
    $goals_parts = [];
    if (isset($input['loan_purpose']) && trim($input['loan_purpose']) !== '') {
        $goals_parts[] = "Loan Purpose: " . trim($input['loan_purpose']);
    }
    if (isset($input['guarantor_first_name']) && trim($input['guarantor_first_name']) !== '') {
        $goals_parts[] = "\n--- Guarantor Details ---";
        $g_name = trim($input['guarantor_first_name'] . ' ' . ($input['guarantor_last_name'] ?? ''));
        $goals_parts[] = "Name: " . $g_name;
        if (isset($input['guarantor_email']) && trim($input['guarantor_email']) !== '') $goals_parts[] = "Email: " . trim($input['guarantor_email']);
        if (isset($input['guarantor_phone']) && trim($input['guarantor_phone']) !== '') $goals_parts[] = "Phone: " . trim($input['guarantor_phone']);
        if (isset($input['guarantor_home_address']) && trim($input['guarantor_home_address']) !== '') $goals_parts[] = "Home Address: " . trim($input['guarantor_home_address']);
        if (isset($input['guarantor_ssn']) && trim($input['guarantor_ssn']) !== '') $goals_parts[] = "SSN: " . trim($input['guarantor_ssn']);
        if (isset($input['guarantor_dob']) && trim($input['guarantor_dob']) !== '') $goals_parts[] = "DOB: " . trim($input['guarantor_dob']);
        if (isset($input['guarantor_credit_score']) && trim($input['guarantor_credit_score']) !== '') $goals_parts[] = "Credit Score: " . trim($input['guarantor_credit_score']);
        if (isset($input['guarantor_ownership_percentage']) && trim($input['guarantor_ownership_percentage']) !== '') $goals_parts[] = "Ownership %: " . trim($input['guarantor_ownership_percentage']);
    }
    $goals_challenges = implode("\n", $goals_parts);
    
    $stmt = $conn->prepare("INSERT INTO financing_submissions (first_name, last_name, email, phone, company, job_title, interests, goals_challenges, form_subtype, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssssssssss", $firstName, $lastName, $email, $phone, $company, $job_title, $interests, $goals_challenges, $formSubtype, $ip_address, $user_agent);
        $executed = $stmt->execute();
        $stmt->close();
    }
}
elseif ($formType === 'mosaic') {
    if ($name === '' || $email === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Name and email are required.']);
        exit();
    }
    if ($message === '') {
        $message = 'Mosaic software demo interest';
    }
    
    $stmt = $conn->prepare("INSERT INTO mosaic_submissions (name, email, phone, organization, organization_type, message, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssssssss", $name, $email, $phone, $organization, $organization_type, $message, $ip_address, $user_agent);
        $executed = $stmt->execute();
        $stmt->close();
    }
}
elseif ($formType === 'prospera') {
    $firstName = trim($input['firstName'] ?? $input['first_name'] ?? '');
    $lastName = trim($input['lastName'] ?? $input['last_name'] ?? '');
    if ($firstName === '' && $lastName === '') {
        $parts = explode(' ', $name, 2);
        $firstName = $parts[0] ?? $name;
        $lastName = $parts[1] ?? '';
    }
    
    if ($firstName === '' || $email === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'First name and email are required.']);
        exit();
    }
    
    $stmt = $conn->prepare("INSERT INTO prospera_submissions (first_name, last_name, email, phone, company, job_title, interests, goals_challenges, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssssssssss", $firstName, $lastName, $email, $phone, $company, $job_title, $interests, $goals_challenges, $ip_address, $user_agent);
        $executed = $stmt->execute();
        $stmt->close();
    }
}

if ($executed) {
    echo json_encode([
        'success' => true,
        'message' => 'Your submission was received successfully!'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save submission: ' . $conn->error
    ]);
}
exit();
?>
