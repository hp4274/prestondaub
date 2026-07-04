<?php
/**
 * Database Setup - Run once to create tables
 */

require_once __DIR__ . '/../../config/database.php';

$setup_messages = [];
$setup_errors = [];

// Create admins table
$admins_sql = "CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($admins_sql) === TRUE) {
    $setup_messages[] = "✓ Admins table created successfully";
} else {
    $setup_errors[] = "✗ Error creating admins table: " . $conn->error;
}

// Create contact_forms table
$contact_forms_sql = "CREATE TABLE IF NOT EXISTS contact_forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    company VARCHAR(255),
    organization VARCHAR(255) COMMENT 'For Mosaic form',
    organization_type VARCHAR(100) COMMENT 'For Mosaic form - Professional Team, Investment Firm, etc.',
    service VARCHAR(255),
    job_title VARCHAR(255) COMMENT 'Also used as title in Mosaic',
    interests LONGTEXT COMMENT 'For Mosaic - JSON array or comma-separated',
    goals_challenges LONGTEXT COMMENT 'For Mosaic - stored as challenges textarea',
    message LONGTEXT NOT NULL,
    checkbox VARCHAR(10),
    form_type VARCHAR(50),
    form_data JSON COMMENT 'Module-specific fields stored as JSON',
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    status VARCHAR(50) DEFAULT 'new',
    priority VARCHAR(50) DEFAULT 'low',
    notes LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (email),
    INDEX (status),
    INDEX (priority),
    INDEX (form_type),
    INDEX (created_at)
)";

if ($conn->query($contact_forms_sql) === TRUE) {
    $setup_messages[] = "✓ Contact forms table created successfully";
} else {
    $setup_errors[] = "✗ Error creating contact forms table: " . $conn->error;
}

// Create news table
$news_sql = "CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    slug VARCHAR(500) UNIQUE NOT NULL,
    excerpt VARCHAR(500),
    content LONGTEXT NOT NULL,
    image_url VARCHAR(500),
    category VARCHAR(100),
    author INT,
    featured BOOLEAN DEFAULT FALSE,
    status VARCHAR(50) DEFAULT 'draft',
    views INT DEFAULT 0,
    published_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author) REFERENCES admins(id),
    INDEX (status),
    INDEX (featured),
    INDEX (published_at),
    INDEX (slug)
)";

if ($conn->query($news_sql) === TRUE) {
    $setup_messages[] = "✓ News table created successfully";
} else {
    $setup_errors[] = "✗ Error creating news table: " . $conn->error;
}

// Create news_categories table
$news_categories_sql = "CREATE TABLE IF NOT EXISTS news_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($news_categories_sql) === TRUE) {
    $setup_messages[] = "✓ News categories table created successfully";
} else {
    $setup_errors[] = "✗ Error creating news categories table: " . $conn->error;
}

// Create admin logs table
$logs_sql = "CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    module VARCHAR(100),
    description LONGTEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX (admin_id),
    INDEX (action),
    INDEX (created_at)
)";

if ($conn->query($logs_sql) === TRUE) {
    $setup_messages[] = "✓ Admin logs table created successfully";
} else {
    $setup_errors[] = "✗ Error creating admin logs table: " . $conn->error;
}

// Create settings table
$settings_sql = "CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value LONGTEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($settings_sql) === TRUE) {
    $setup_messages[] = "✓ Settings table created successfully";
} else {
    $setup_errors[] = "✗ Error creating settings table: " . $conn->error;
}

// Insert default admin user — set LEGACY_SETUP_ADMIN_PASSWORD (see legacy-php-support/.env.example)
$admin_email = 'admin@prestondaub.com';
$admin_name = 'Admin User';
$admin_plain = getenv('LEGACY_SETUP_ADMIN_PASSWORD');

$check_admin = "SELECT id FROM admins WHERE email = '$admin_email'";
$result = $conn->query($check_admin);

if ($result->num_rows == 0) {
    if ($admin_plain === false || $admin_plain === '') {
        $setup_errors[] = '✗ LEGACY_SETUP_ADMIN_PASSWORD is not set — cannot create default admin (see admin/_archive/legacy-php-support/.env.example).';
    } else {
        $admin_password = password_hash($admin_plain, PASSWORD_BCRYPT);
        $insert_admin = "INSERT INTO admins (email, password, name) VALUES ('$admin_email', '$admin_password', '$admin_name')";
        if ($conn->query($insert_admin) === TRUE) {
            $setup_messages[] = '✓ Default admin user created (email: admin@prestondaub.com; password from LEGACY_SETUP_ADMIN_PASSWORD — not shown here)';
        } else {
            $setup_errors[] = "✗ Error creating admin user: " . $conn->error;
        }
    }
} else {
    $setup_messages[] = "⚠ Admin user already exists";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .setup-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        .setup-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 20px;
        }
        .setup-header h1 {
            color: #667eea;
            font-weight: bold;
        }
        .message {
            padding: 12px 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid;
        }
        .message.success {
            background-color: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .setup-complete {
            text-align: center;
            margin-top: 30px;
        }
        .setup-complete a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .setup-complete a:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1>🔧 Admin Panel Setup</h1>
            <p class="text-muted">Database Configuration & Initialization</p>
        </div>

        <?php if (!empty($setup_messages)): ?>
            <div class="messages">
                <?php foreach ($setup_messages as $message): ?>
                    <div class="message success"><?php echo $message; ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($setup_errors)): ?>
            <div class="messages">
                <?php foreach ($setup_errors as $error): ?>
                    <div class="message error"><?php echo $error; ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="setup-complete">
            <h3 class="text-success">✓ Setup Complete!</h3>
            <p class="mt-3">Your database has been configured successfully.</p>
            <p><strong>Login:</strong></p>
            <p class="text-muted">Email: admin@prestondaub.com<br>Use the password you set in <code>LEGACY_SETUP_ADMIN_PASSWORD</code>.</p>
            <a href="../../login.html">Go to login</a>
        </div>
    </div>
</body>
</html>
