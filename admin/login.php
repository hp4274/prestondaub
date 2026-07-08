<?php
require_once __DIR__ . '/config/auth.php';

if (is_logged_in()) {
    header("Location: dashboard.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Read JSON payload
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $email = trim($data['email'] ?? '');
    $password = trim($data['password'] ?? '');
    
    if (!$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit();
    }
    
    // Connect to database
    require_once __DIR__ . '/config/database.php';
    
    // Search in admin_users or admins table
    $email_escaped = $conn->real_escape_string($email);
    $result = $conn->query("SELECT * FROM admin_users WHERE email = '$email_escaped' LIMIT 1");
    if (!$result || $result->num_rows === 0) {
        $result = $conn->query("SELECT * FROM admins WHERE email = '$email_escaped' LIMIT 1");
    }
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $hash = $user['password_hash'] ?? $user['password'] ?? '';
        
        if (password_verify($password, $hash)) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_name'] = $user['name'] ?? 'Admin';
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'admin' => [
                    'email' => $user['email'],
                    'name' => $user['name'] ?? 'Admin'
                ]
            ]);
            exit();
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit();
}

// GET request: show login.html content
readfile(__DIR__ . '/login.html');
