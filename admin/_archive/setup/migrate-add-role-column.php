<?php
/**
 * Migration: Add role column to admins table and create two fixed users
 * Run once to set up the two-user system
 */

require_once __DIR__ . '/../../config/database.php';

$password1 = getenv('LEGACY_MIGRATE_PASSWORD_USER1');
$password2 = getenv('LEGACY_MIGRATE_PASSWORD_USER2');
if ($password1 === false || $password1 === '' || $password2 === false || $password2 === '') {
    fwrite(STDERR, "Set LEGACY_MIGRATE_PASSWORD_USER1 and LEGACY_MIGRATE_PASSWORD_USER2 (see admin/_archive/legacy-php-support/.env.example).\n");
    exit(1);
}

echo "=== MIGRATION: Add Role Column and Create Fixed Users ===\n\n";

// Step 1: Add role column if it doesn't exist
echo "Step 1: Checking/Adding role column...\n";
$check_column = $conn->query("SHOW COLUMNS FROM admins LIKE 'role'");
if ($check_column->num_rows === 0) {
    $alter_sql = "ALTER TABLE admins ADD COLUMN role VARCHAR(50) DEFAULT 'admin' AFTER name";
    if ($conn->query($alter_sql) === TRUE) {
        echo "✓ Role column added successfully\n\n";
    } else {
        echo "✗ Error adding role column: " . $conn->error . "\n\n";
        exit;
    }
} else {
    echo "✓ Role column already exists\n\n";
}

// Step 2: Create/Update the two fixed users
echo "Step 2: Setting up fixed users...\n\n";

// User 1: Andrew Cisel (Admin - can change password)
$email1 = 'andrew.cisel@gmail.com';
$name1 = 'Andrew Cisel';
$role1 = 'admin';
$hashed_password1 = password_hash($password1, PASSWORD_BCRYPT);

// Check if user exists
$check1 = $conn->query("SELECT id FROM admins WHERE email = '$email1'");
if ($check1->num_rows > 0) {
    // Update existing user
    $update1 = "UPDATE admins SET password = '$hashed_password1', name = '$name1', role = '$role1' WHERE email = '$email1'";
    if ($conn->query($update1) === TRUE) {
        echo "✓ User 1 updated: $email1 (Admin)\n";
        echo "  - Name: $name1\n";
        echo "  - Password: (from LEGACY_MIGRATE_PASSWORD_USER1; not echoed)\n";
        echo "  - Role: $role1 (Can change password)\n\n";
    } else {
        echo "✗ Error updating User 1: " . $conn->error . "\n\n";
    }
} else {
    // Create new user
    $insert1 = "INSERT INTO admins (email, password, name, role) VALUES ('$email1', '$hashed_password1', '$name1', '$role1')";
    if ($conn->query($insert1) === TRUE) {
        echo "✓ User 1 created: $email1 (Admin)\n";
        echo "  - Name: $name1\n";
        echo "  - Password: (from LEGACY_MIGRATE_PASSWORD_USER1; not echoed)\n";
        echo "  - Role: $role1 (Can change password)\n\n";
    } else {
        echo "✗ Error creating User 1: " . $conn->error . "\n\n";
    }
}

// User 2: Rumit (Restricted - cannot change password)
$email2 = 'rumit@keryar.com';
$name2 = 'Rumit';
$role2 = 'restricted';
$hashed_password2 = password_hash($password2, PASSWORD_BCRYPT);

// Check if user exists
$check2 = $conn->query("SELECT id FROM admins WHERE email = '$email2'");
if ($check2->num_rows > 0) {
    // Update existing user
    $update2 = "UPDATE admins SET password = '$hashed_password2', name = '$name2', role = '$role2' WHERE email = '$email2'";
    if ($conn->query($update2) === TRUE) {
        echo "✓ User 2 updated: $email2 (Restricted)\n";
        echo "  - Name: $name2\n";
        echo "  - Password: (from LEGACY_MIGRATE_PASSWORD_USER2; not echoed)\n";
        echo "  - Role: $role2 (Cannot change password)\n\n";
    } else {
        echo "✗ Error updating User 2: " . $conn->error . "\n\n";
    }
} else {
    // Create new user
    $insert2 = "INSERT INTO admins (email, password, name, role) VALUES ('$email2', '$hashed_password2', '$name2', '$role2')";
    if ($conn->query($insert2) === TRUE) {
        echo "✓ User 2 created: $email2 (Restricted)\n";
        echo "  - Name: $name2\n";
        echo "  - Password: (from LEGACY_MIGRATE_PASSWORD_USER2; not echoed)\n";
        echo "  - Role: $role2 (Cannot change password)\n\n";
    } else {
        echo "✗ Error creating User 2: " . $conn->error . "\n\n";
    }
}

// Step 3: Verify both users
echo "Step 3: Verification...\n\n";
$verify = $conn->query("SELECT id, email, name, role FROM admins WHERE email IN ('andrew.cisel@gmail.com', 'rumit@keryar.com')");
if ($verify->num_rows === 2) {
    echo "✓ Both users are properly set up:\n";
    while ($row = $verify->fetch_assoc()) {
        echo "  - " . $row['email'] . " (" . $row['name'] . ") - Role: " . $row['role'] . "\n";
    }
} else {
    echo "✗ Warning: Not all users are set up correctly. Found " . $verify->num_rows . " users.\n";
}

echo "\n=== MIGRATION COMPLETE ===\n";
echo "\n✅ Login Restrictions Active:\n";
echo "   - Only andrew.cisel@gmail.com and rumit@keryar.com can log in\n";
echo "   - All other emails will be rejected\n";
echo "   - Andrew can change password from Profile & Settings\n";
echo "   - Rumit cannot change password (fixed backend password)\n";
?>
