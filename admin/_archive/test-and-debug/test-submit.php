<?php
// Simulate a form submission to test
$_POST = [
    "name" => "Test User",
    "email" => "test@example.com",
    "phone" => "1234567890",
    "organization" => "Test Org",
    "organization_type" => "General Info",
    "form_type" => "contact",
    "challenges" => "This is a test message"
];

// Now run the submit logic
require 'submit-form.php';
?>
