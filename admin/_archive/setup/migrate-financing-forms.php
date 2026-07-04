<?php
/**
 * Migrate existing financing forms to populate form_data column
 * This script reads existing form columns and populates the JSON form_data column
 */

require_once 'config/auth.php';
require_login();
require_once 'config/database.php';

// Only allow admin access
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied');
}

// Get all financing forms that don't have form_data yet
$query = "SELECT id, form_type, name, email, phone FROM contact_forms 
          WHERE form_type IN ('financing', 'business-loans', 'sba-loans', 'equipment-loans', 'bridge-loans', 'working-capital')
          AND (form_data IS NULL OR form_data = '{}' OR form_data = '')
          ORDER BY id DESC";

$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$updated_count = 0;
$failed_count = 0;
$errors = [];

while ($form = $result->fetch_assoc()) {
    // Get all columns for this form
    $form_id = $form['id'];
    
    // Build form_data array from existing columns
    $form_data = [
        'financing_solution_type' => '',
        'legal_business_name' => $form['name'] ?? '',
        'dba' => '',
        'business_type' => '',
        'physical_address' => '',
        'city_state_zip' => '',
        'phone' => $form['phone'] ?? '',
        'fax' => '',
        'email' => $form['email'] ?? '',
        'fed_tax_id' => '',
        'email_communication' => '',
        'legal_structure' => '',
        'num_employees' => '',
        'date_business_started' => '',
        'date_became_owner' => '',
        // Ownership Information
        'owner1_name_title' => '',
        'owner1_home_address' => '',
        'owner1_home_phone' => '',
        'owner1_cell' => '',
        'owner1_ssn' => '',
        'owner1_dob' => '',
        'owner1_ownership_percent' => '',
        'owner2_name_title' => '',
        'owner2_home_address' => '',
        'owner2_home_phone' => '',
        'owner2_cell' => '',
        'owner2_ssn' => '',
        'owner2_dob' => '',
        'owner2_ownership_percent' => '',
        // Business Bank/Trade References
        'bank_name' => '',
        'bank_account' => '',
        'bank_phone' => '',
        'bank_contact' => '',
        'trade_ref_1' => '',
        'trade_ref_1_phone' => '',
        'trade_ref_2' => '',
        'trade_ref_2_phone' => '',
        // Vendor Information
        'vendor_name' => '',
        'vendor_phone' => '',
        'sales_person' => '',
        'equipment_description' => '',
        'equipment_new_used' => '',
        'equipment_price' => '',
        'equipment_term' => '',
        // Disclosure & Authorization
        'signature1_name' => '',
        'signature1_title' => '',
        'signature1_date' => '',
        'signature2_name' => '',
        'signature2_title' => '',
        'signature2_date' => '',
    ];
    
    $form_data_json = json_encode($form_data);
    $escaped_data = $conn->real_escape_string($form_data_json);
    
    $update_query = "UPDATE contact_forms SET form_data = '$escaped_data' WHERE id = $form_id";
    
    if ($conn->query($update_query)) {
        $updated_count++;
    } else {
        $failed_count++;
        $errors[] = "Form ID $form_id: " . $conn->error;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Financing Forms Migration</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #17a2b8; }
        .stats { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .error-list { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px; margin-top: 10px; }
        .error-list li { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Financing Forms Migration Report</h1>
        
        <div class="stats">
            <p class="success">✓ Successfully Updated: <?php echo $updated_count; ?> forms</p>
            <p class="error">✗ Failed: <?php echo $failed_count; ?> forms</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="error-list">
                <strong>Errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <p class="info">Migration completed! All financing forms have been updated with empty form_data JSON structure.</p>
        <p>Note: The form_data column now contains empty values for all fields. New form submissions will populate these fields with actual data.</p>
        
        <hr>
        <p><a href="forms-financing.php">← Back to Financing Forms</a></p>
    </div>
</body>
</html>
