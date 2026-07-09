<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$flash = '';
$flash_class = 'alert-success';
$admin_id = intval($_SESSION['admin_id']);

// Handle GET Actions (Delete, Edit mode)
if (isset($_GET['action'])) {
    if (($_SESSION['admin_role'] ?? 'admin') !== 'admin') {
        header("Location: profile.php");
        exit();
    }
    $action = $_GET['action'];
    $target_id = intval($_GET['id'] ?? 0);
    
    if ($target_id > 0) {
        if ($action === 'delete') {
            if ($target_id === $admin_id) {
                $flash = 'You cannot delete your own logged-in administrator account.';
                $flash_class = 'alert-error';
            } else {
                $conn->query("DELETE FROM admins WHERE id = $target_id");
                header("Location: profile.php?msg=admin_deleted");
                exit();
            }
        }
    }
}

// Handle Form POST Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if ($name && $email) {
            $name_esc = $conn->real_escape_string($name);
            $email_esc = $conn->real_escape_string($email);
            
            // Check email uniqueness
            $chk_email = $conn->query("SELECT id FROM admins WHERE email = '$email_esc' AND id != $admin_id")->num_rows;
            if ($chk_email > 0) {
                $flash = 'This email is already in use by another administrator.';
                $flash_class = 'alert-error';
            } else {
                $conn->query("UPDATE admins SET name = '$name_esc', email = '$email_esc' WHERE id = $admin_id");
                $_SESSION['admin_name'] = $name;
                $_SESSION['admin_email'] = $email;
                header("Location: profile.php?msg=profile_updated");
                exit();
            }
        } else {
            $flash = 'Name and Email are required fields.';
            $flash_class = 'alert-error';
        }
    } elseif ($action === 'change_password') {
        // Load logged-in admin data
        $admin_res = $conn->query("SELECT * FROM admins WHERE id = $admin_id LIMIT 1");
        $admin_curr = $admin_res->fetch_assoc();

        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if ($current_password && $new_password && $confirm_password) {
            $hash = $admin_curr['password_hash'] ?? $admin_curr['password'] ?? '';
            
            if (!password_verify($current_password, $hash)) {
                $flash = 'Current password is incorrect.';
                $flash_class = 'alert-error';
            } elseif ($new_password !== $confirm_password) {
                $flash = 'New password and confirmation password do not match.';
                $flash_class = 'alert-error';
            } elseif (strlen($new_password) < 6) {
                $flash = 'New password must be at least 6 characters long.';
                $flash_class = 'alert-error';
            } else {
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $new_hash_esc = $conn->real_escape_string($new_hash);
                $conn->query("UPDATE admins SET password = '$new_hash_esc', password_hash = '$new_hash_esc' WHERE id = $admin_id");
                header("Location: profile.php?msg=password_updated");
                exit();
            }
        } else {
            $flash = 'All password fields are required.';
            $flash_class = 'alert-error';
        }
    } elseif ($action === 'save_admin_account') {
        if (($_SESSION['admin_role'] ?? 'admin') !== 'admin') {
            header("Location: profile.php");
            exit();
        }
        // Add or Edit another admin
        $target_admin_id = intval($_POST['target_admin_id'] ?? 0);
        $name = trim($_POST['admin_name'] ?? '');
        $email = trim($_POST['admin_email'] ?? '');
        $password = trim($_POST['admin_password'] ?? '');
        $role = trim($_POST['admin_role'] ?? 'admin');
        
        if ($name && $email) {
            $name_esc = $conn->real_escape_string($name);
            $email_esc = $conn->real_escape_string($email);
            $role_esc = $conn->real_escape_string($role);
            
            if ($target_admin_id > 0) {
                // Update Admin
                $chk_email = $conn->query("SELECT id FROM admins WHERE email = '$email_esc' AND id != $target_admin_id")->num_rows;
                if ($chk_email > 0) {
                    $flash = 'This email is already in use.';
                    $flash_class = 'alert-error';
                } else {
                    $pass_sql = "";
                    if ($password !== '') {
                        $new_hash = password_hash($password, PASSWORD_DEFAULT);
                        $new_hash_esc = $conn->real_escape_string($new_hash);
                        $pass_sql = ", password = '$new_hash_esc', password_hash = '$new_hash_esc'";
                    }
                    $conn->query("UPDATE admins SET name = '$name_esc', email = '$email_esc', role = '$role_esc' $pass_sql WHERE id = $target_admin_id");
                    
                    if ($target_admin_id === $admin_id) {
                        $_SESSION['admin_name'] = $name;
                        $_SESSION['admin_email'] = $email;
                    }
                    header("Location: profile.php?msg=admin_updated");
                    exit();
                }
            } else {
                // Add new Admin
                if (!$password) {
                    $flash = 'Password is required for new accounts.';
                    $flash_class = 'alert-error';
                } else {
                    $chk_email = $conn->query("SELECT id FROM admins WHERE email = '$email_esc'")->num_rows;
                    if ($chk_email > 0) {
                        $flash = 'This email is already registered.';
                        $flash_class = 'alert-error';
                    } else {
                        $new_hash = password_hash($password, PASSWORD_DEFAULT);
                        $new_hash_esc = $conn->real_escape_string($new_hash);
                        $conn->query("INSERT INTO admins (name, email, password, password_hash, role) VALUES ('$name_esc', '$email_esc', '$new_hash_esc', '$new_hash_esc', '$role_esc')");
                        header("Location: profile.php?msg=admin_added");
                        exit();
                    }
                }
            }
        } else {
            $flash = 'Name and Email are required fields.';
            $flash_class = 'alert-error';
        }
    }
}

// Load current admin details for My Profile form
$admin_res = $conn->query("SELECT * FROM admins WHERE id = $admin_id LIMIT 1");
$admin = $admin_res->fetch_assoc();

// Load target admin if editing
$edit_admin = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_res = $conn->query("SELECT * FROM admins WHERE id = $edit_id LIMIT 1");
    if ($edit_res && $edit_res->num_rows > 0) {
        $edit_admin = $edit_res->fetch_assoc();
    }
}

// Fetch all admin users
$admins_list = $conn->query("SELECT * FROM admins ORDER BY name ASC");

// Fetch query messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'profile_updated') $flash = 'Profile info updated successfully.';
    elseif ($_GET['msg'] === 'password_updated') $flash = 'Password changed successfully.';
    elseif ($_GET['msg'] === 'admin_added') $flash = 'New administrator registered successfully.';
    elseif ($_GET['msg'] === 'admin_updated') $flash = 'Administrator updated successfully.';
    elseif ($_GET['msg'] === 'admin_deleted') $flash = 'Administrator account deleted successfully.';
}
?>
<div class="main-content">
    <header class="main-header">
        <div class="header-title">My Profile & Administrators</div>
        <div class="user-profile">
            <div class="user-avatar"><?= $admin_initial ?></div>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($admin_name) ?></span>
                <span class="user-role">Administrator</span>
            </div>
        </div>
    </header>
    
    <div class="content-body">
        <?php if ($flash): ?>
            <div class="alert <?= $flash_class ?>">
                <i class="fa-solid <?= $flash_class === 'alert-success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <?= htmlspecialchars($flash) ?>
            </div>
        <?php endif; ?>

        <!-- Active Profile Grid -->
        <div class="detail-grid" style="margin-bottom: 32px;">
            <!-- Profile Info Card -->
            <div class="section-card" style="margin-bottom: 0;">
                <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 24px;">
                    <h2 class="card-title">
                        <i class="fa-solid fa-user-gear"></i>
                        Profile Details
                    </h2>
                </div>

                <form action="profile.php" method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Admin Name" required value="<?= htmlspecialchars($admin['name'] ?? $admin_name) ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="admin@prestondaub.com" required value="<?= htmlspecialchars($admin['email'] ?? $admin_email) ?>">
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 12px; width: 100%;">
                        <i class="fa-solid fa-user-check"></i>
                        Update Details
                    </button>
                </form>
            </div>

            <!-- Change Password Card -->
            <div class="section-card" style="margin-bottom: 0;">
                <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 24px;">
                    <h2 class="card-title">
                        <i class="fa-solid fa-shield-halved"></i>
                        Change Password
                    </h2>
                </div>

                <form action="profile.php" method="POST">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required placeholder="••••••••">
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required placeholder="••••••••">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 12px; width: 100%; background-color: var(--accent-indigo);">
                        <i class="fa-solid fa-key"></i>
                        Update Password
                    </button>
                </form>
            </div>
        </div>

        <?php if (($_SESSION['admin_role'] ?? 'admin') === 'admin'): ?>
        <!-- Manage Other Admins Grid -->
        <div class="detail-grid">
            <!-- Left Side: Add / Edit Admin Form -->
            <div class="section-card" style="margin-bottom: 0;">
                <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 24px;">
                    <h2 class="card-title">
                        <i class="fa-solid <?= $edit_admin ? 'fa-user-pen' : 'fa-user-plus' ?>"></i>
                        <?= $edit_admin ? 'Edit Admin: ' . htmlspecialchars($edit_admin['name']) : 'Add New Admin Account' ?>
                    </h2>
                    <?php if ($edit_admin): ?>
                        <a href="profile.php" class="btn btn-secondary btn-sm" style="padding: 4px 8px;">Cancel Edit</a>
                    <?php endif; ?>
                </div>

                <form action="profile.php" method="POST">
                    <input type="hidden" name="action" value="save_admin_account">
                    <input type="hidden" name="target_admin_id" value="<?= $edit_admin ? $edit_admin['id'] : 0 ?>">
                    
                    <div class="form-group">
                        <label for="admin_name">Full Name *</label>
                        <input type="text" id="admin_name" name="admin_name" class="form-control" placeholder="Jane Doe" required value="<?= htmlspecialchars($edit_admin ? $edit_admin['name'] : '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="admin_email">Email Address *</label>
                        <input type="email" id="admin_email" name="admin_email" class="form-control" placeholder="jane@prestondaub.com" required value="<?= htmlspecialchars($edit_admin ? $edit_admin['email'] : '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="admin_password">Password <?= $edit_admin ? '(Leave blank to keep unchanged)' : '*' ?></label>
                        <input type="password" id="admin_password" name="admin_password" class="form-control" placeholder="••••••••" <?= $edit_admin ? '' : 'required' ?>>
                    </div>

                    <div class="form-group">
                        <label for="admin_role">Privilege Role</label>
                        <select id="admin_role" name="admin_role" class="form-control">
                            <option value="admin" <?= ($edit_admin && $edit_admin['role'] === 'admin') ? 'selected' : '' ?>>Full Admin</option>
                            <option value="restricted" <?= ($edit_admin && $edit_admin['role'] === 'restricted') ? 'selected' : '' ?>>Restricted Reader</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fa-solid fa-save"></i>
                        <?= $edit_admin ? 'Save Administrator' : 'Create Administrator' ?>
                    </button>
                </form>
            </div>

            <!-- Right Side: Administrators Directory List -->
            <div class="section-card" style="margin-bottom: 0;">
                <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 24px;">
                    <h2 class="card-title">
                        <i class="fa-solid fa-users-shield"></i>
                        Administrators Directory
                    </h2>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Administrator</th>
                                <th>Role</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($admins_list && $admins_list->num_rows > 0): ?>
                                <?php while ($row = $admins_list->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-primary);">
                                                <?= htmlspecialchars($row['name']) ?>
                                                <?php if ($row['id'] === $admin_id): ?>
                                                    <span style="font-size: 0.75rem; color: var(--accent-indigo); margin-left: 4px;">(You)</span>
                                                <?php endif; ?>
                                            </div>
                                            <span style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($row['email']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $row['role'] === 'admin' ? 'read' : 'new' ?>" style="text-transform: capitalize;">
                                                <?= htmlspecialchars($row['role']) ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <div style="display: inline-flex; gap: 8px;">
                                                <a href="profile.php?edit_id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm" title="Edit Admin">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                                <?php if ($row['id'] !== $admin_id): ?>
                                                    <a href="profile.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete administrator <?= htmlspecialchars($row['name']) ?>?')" title="Delete Admin">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="btn btn-secondary btn-sm" style="opacity: 0.3; cursor: not-allowed;" title="Self deletion disabled">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 32px;">
                                        No administrators found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
