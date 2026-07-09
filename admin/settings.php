<?php
require_once __DIR__ . '/includes/header.php';

// Access Control: Only full admins can access Site Settings
if (($_SESSION['admin_role'] ?? 'admin') !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/includes/sidebar.php';

$flash = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Array of settings to update
    $settings_to_update = [
        'site_name' => trim($_POST['site_name'] ?? 'Preston Daub'),
        'admin_email' => trim($_POST['admin_email'] ?? ''),
        'team_module_enabled' => isset($_POST['team_module_enabled']) ? '1' : '0',
        'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0'
    ];
    
    foreach ($settings_to_update as $key => $value) {
        $key_esc = $conn->real_escape_string($key);
        $value_esc = $conn->real_escape_string($value);
        
        // Check if key exists
        $chk = $conn->query("SELECT id FROM settings WHERE setting_key = '$key_esc'")->num_rows;
        if ($chk > 0) {
            $conn->query("UPDATE settings SET setting_value = '$value_esc' WHERE setting_key = '$key_esc'");
        } else {
            $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('$key_esc', '$value_esc')");
        }
    }
    
    header("Location: settings.php?msg=updated");
    exit();
}

// Fetch success messages
if (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
    $flash = 'Settings saved successfully.';
}

// Load current settings from database
$settings_res = $conn->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
if ($settings_res) {
    while ($row = $settings_res->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Set defaults if empty
$site_name = $settings['site_name'] ?? 'Preston Daub';
$admin_email_setting = $settings['admin_email'] ?? '';
$team_module_enabled = $settings['team_module_enabled'] ?? '1';
$maintenance_mode = $settings['maintenance_mode'] ?? '0';
?>
<div class="main-content">
    <header class="main-header">
        <div class="header-title">System Settings</div>
        <div class="user-profile">
            <div class="user-avatar"><?= $admin_initial ?></div>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($admin_name) ?></span>
                <span class="user-role">Administrator</span>
            </div>
        </div>
    </header>
    
    <div class="content-body" style="max-width: 800px;">
        <?php if ($flash): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <?= htmlspecialchars($flash) ?>
            </div>
        <?php endif; ?>

        <div class="section-card">
            <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 24px;">
                <h2 class="card-title">
                    <i class="fa-solid fa-sliders"></i>
                    Configure Platform Settings
                </h2>
            </div>

            <form action="settings.php" method="POST">
                <div class="form-group">
                    <label for="site_name">Website/Site Name</label>
                    <input type="text" id="site_name" name="site_name" class="form-control" placeholder="Preston Daub" value="<?= htmlspecialchars($site_name) ?>" required>
                </div>

                <div class="form-group">
                    <label for="admin_email">Contact / Notification Email</label>
                    <input type="email" id="admin_email" name="admin_email" class="form-control" placeholder="notifications@prestondaub.com" value="<?= htmlspecialchars($admin_email_setting) ?>">
                    <span style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-top: 4px;">Used for form notifications and sender records.</span>
                </div>

                <div class="switch-group">
                    <div class="switch-label-desc">
                        <span class="switch-title">Enable Team Roster Module</span>
                        <span class="switch-subtitle">Controls the visibility of the Team Roster page link on the website header and footer.</span>
                    </div>
                    <label class="switch" for="team_module_enabled">
                        <input type="checkbox" id="team_module_enabled" name="team_module_enabled" value="1" <?= $team_module_enabled === '1' ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="switch-group">
                    <div class="switch-label-desc">
                        <span class="switch-title">Enable Maintenance Mode</span>
                        <span class="switch-subtitle" style="color: var(--accent-red);">Restricts public access to all website pages and displays a construction page.</span>
                    </div>
                    <label class="switch" for="maintenance_mode">
                        <input type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" <?= $maintenance_mode === '1' ? 'checked' : '' ?>>
                        <span class="slider slider-danger"></span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 12px 24px;">
                    <i class="fa-solid fa-save"></i>
                    Save Configurations
                </button>
            </form>
        </div>
    </div>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
