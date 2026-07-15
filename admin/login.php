<?php
require_once __DIR__ . '/config/auth.php';

if (is_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (!$email || !$password) {
        $login_error = 'Email and password are required';
    } else {
        require_once __DIR__ . '/config/database.php';
        
        $email_escaped = $conn->real_escape_string($email);
        $result = $conn->query("SELECT * FROM admins WHERE email = '$email_escaped' LIMIT 1");
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $hash = $user['password_hash'] ?? $user['password'] ?? '';
            
            if (password_verify($password, $hash)) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['admin_name'] = $user['name'] ?? 'Admin';
                $_SESSION['admin_role'] = $user['role'] ?? 'admin';
                
                header("Location: dashboard.php");
                exit();
            }
        }
        
        $login_error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preston Daub - Admin Login</title>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/favicon.png">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-dark: #f8fafc;
            --bg-card: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #0f172a;
            --text-muted: #64748b;
            --accent-indigo: #0f172a;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        body {
            background-color: var(--bg-dark);
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .login-logo {
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 8px;
            color: var(--accent-indigo);
        }
        .login-logo span {
            color: var(--text-muted);
            font-weight: 400;
            margin-left: 2px;
        }
        .login-subtitle {
            font-size: 0.85rem;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 32px;
        }
        .form-group {
            margin-bottom: 24px;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: var(--accent-indigo);
            box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.05);
        }
        .alert {
            background-color: #fef2f2;
            border: 1px solid rgba(220, 38, 38, 0.2);
            color: #b91c1c;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 24px;
            text-align: center;
            font-weight: 500;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: var(--accent-indigo);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .btn-submit:hover {
            background-color: #1e293b;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">Preston<span>Daub</span></div>
        <div class="login-subtitle">Sign in to the Admin Dashboard</div>
        
        <?php if ($login_error): ?>
            <div class="alert"><?= htmlspecialchars($login_error) ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="admin@prestondaub.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn-submit">Sign In</button>
        </form>
    </div>
</body>
</html>
