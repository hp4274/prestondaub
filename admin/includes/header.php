<?php
require_once __DIR__ . '/../config/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_email = $_SESSION['admin_email'] ?? '';
$admin_initial = strtoupper(substr($admin_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preston Daub - Admin Dashboard</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/admin-custom.css">
</head>
<body>
    <div class="admin-wrapper">
