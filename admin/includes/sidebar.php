<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        Preston<span>Daub</span>
    </div>
    <ul class="sidebar-menu">
        <li class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
            <a href="dashboard.php">
                <i class="fa-solid fa-chart-pie"></i>
                Dashboard
            </a>
        </li>
        <li class="<?= $current_page === 'forms.php' || $current_page === 'form-detail.php' ? 'active' : '' ?>">
            <a href="forms.php">
                <i class="fa-solid fa-inbox"></i>
                Submissions
            </a>
        </li>
        <li class="<?= $current_page === 'team.php' ? 'active' : '' ?>">
            <a href="team.php">
                <i class="fa-solid fa-user-group"></i>
                Team Members
            </a>
        </li>
        <li class="<?= $current_page === 'news.php' ? 'active' : '' ?>">
            <a href="news.php">
                <i class="fa-solid fa-newspaper"></i>
                News Articles
            </a>
        </li>
        <?php if (($_SESSION['admin_role'] ?? 'admin') === 'admin'): ?>
        <li class="<?= $current_page === 'settings.php' ? 'active' : '' ?>">
            <a href="settings.php">
                <i class="fa-solid fa-sliders"></i>
                Site Settings
            </a>
        </li>
        <?php endif; ?>
        <li class="<?= $current_page === 'profile.php' ? 'active' : '' ?>">
            <a href="profile.php">
                <i class="fa-solid fa-circle-user"></i>
                Admin Profile
            </a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <a href="logout.php">
            <i class="fa-solid fa-right-from-bracket"></i>
            Logout
        </a>
    </div>
</aside>
