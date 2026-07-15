<?php
require_once __DIR__ . '/includes/Mailer.php';

echo "========================================================\n";
echo "Testing User Confirmation / Thank You Email Receipt\n";
echo "========================================================\n";

try {
    $mailer = new Mailer();
    
    $testFormType = 'business-financing';
    $testUserEmail = 'harshlpatel.4274@gmail.com';
    $testUserName = 'Harsh Patel';
    $testData = [
        'company' => 'Preston Daub & Co.',
        'service' => 'SBA 7(a) & Commercial Financing',
        'phone' => '+1 (555) 382-9102',
        'loan_amount' => '$2,500,000',
        'yearly_revenue' => '$5,000,000+',
        'message' => 'We are seeking structured expansion financing for our new regional advisory offices.'
    ];

    echo "Sending branded User Confirmation to: {$testUserEmail}...\n";
    $sent = $mailer->sendUserConfirmation($testFormType, $testUserEmail, $testUserName, $testData);

    if ($sent) {
        echo "\n✅ SUCCESS: User confirmation email sent to {$testUserEmail}!\n";
        echo "Check your inbox right now to view the confirmation receipt.\n";
    } else {
        echo "\n❌ FAILED to send email.\n";
    }
} catch (Exception $e) {
    echo "\n❌ EXCEPTION: " . $e->getMessage() . "\n";
}
?>
