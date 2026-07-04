<?php
/**
 * Admin Header - Modern SaaS Dashboard
 */

require_once dirname(__DIR__, 3) . '/config/auth.php';
require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Preston Daub</title>
    <link rel="icon" type="image/png" href="../assets/img/logo/favicon.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/modern-dashboard-new.css">
    <!-- Unified Notification System -->
    <link rel="stylesheet" href="assets/css/notifications.css">
    <!-- Centralized Tooltip System -->
    <link rel="stylesheet" href="assets/css/tooltip.css">
    <?php if (basename($_SERVER['PHP_SELF']) === 'news-list.php'): ?>
        <link rel="stylesheet" href="assets/css/news-list.css">
    <?php endif; ?>
    <style>
        .material-symbols-rounded {
            font-family: 'Material Symbols Rounded';
            font-weight: 10px;
            font-style: normal;
            font-size: 22px;
            display: inline-flex;
            line-height: 1;
            text-transform: none;
            letter-spacing: normal;
            word-wrap: normal;
            white-space: nowrap;
            direction: ltr;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-sticky">
                <!-- Logo -->
                <div class="sidebar-logo">
                    <img src="../assets/img/logo/logo.png" alt="Preston Daub" class="sidebar-logo-img">
                </div>

                <!-- Main Navigation -->
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Main</div>
                    <a href="dashboard.html" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'active' : ''; ?>">
                        <span class="material-symbols-rounded">dashboard</span>
                        <span>Dashboard</span>
                    </a>
                </div>

                <!-- Forms Management -->
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Forms Management</div>
                    <a href="forms.html?module=financing" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'forms-financing.php') ? 'active' : ''; ?>">
                        <span class="material-symbols-rounded">apartment</span>
                        <span>Financing</span>
                    </a>
                    <a href="forms.html?module=sports" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'forms-sports.php') ? 'active' : ''; ?>">
                        <span class="material-symbols-rounded">sports_soccer</span>
                        <span>Sports</span>
                    </a>
                    <a href="forms.html?module=mosaic" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'forms-mosaic.php') ? 'active' : ''; ?>">
                        <span class="material-symbols-rounded">dashboard_customize</span>
                        <span>Mosaic</span>
                    </a>
                    <a href="forms.html?module=prospera" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'forms-prospera.php') ? 'active' : ''; ?>">
                        <span class="material-symbols-rounded">eco</span>
                        <span>Prospera</span>
                    </a>
                    <a href="forms.html?module=contact" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'forms-contact.php') ? 'active' : ''; ?>">
                        <span class="material-symbols-rounded">mail</span>
                        <span>Contact Forms</span>
                    </a>
                </div>

                <!-- News Management -->
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Content</div>
                    <a href="news.html" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'news-list.php') ? 'active' : ''; ?>">
                        <span class="material-symbols-rounded">newspaper</span>
                        <span>News</span>
                    </a>
                    <a href="team.html" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'team.php') ? 'active' : ''; ?>">
                        <span class="material-symbols-rounded">group</span>
                        <span>Team Members</span>
                    </a>
                </div>

                <!-- Settings -->
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Admin</div>
                    <a href="profile.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'profile.php') ? 'active' : ''; ?>">
                        <span class="material-symbols-rounded">account_circle</span>
                        <span>Profile & Settings</span>
                    </a>
                    <a href="logout.php" class="nav-link">
                        <span class="material-symbols-rounded">logout</span>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <nav class="navbar">
                <div class="navbar-left"></div>

                <div class="navbar-right">
                    <div class="navbar-user">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['admin_name'], 0, 2)); ?></div>
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
                            <div class="user-role">Admin</div>
                        </div>
                    </div>
                </div>
            </nav>

            <style>
                /* Styles for dashboard components */
            </style>

            <!-- Page Content Area -->
            <div class="page-content">