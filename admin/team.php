<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$flash = '';
$flash_class = 'alert-success';

// Handle URL GET Actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $member_id = intval($_GET['id'] ?? 0);
    
    if ($member_id > 0) {
        if ($action === 'delete') {
            $conn->query("DELETE FROM team_members WHERE id = $member_id");
            header("Location: team.php?msg=deleted");
            exit();
        } elseif ($action === 'toggle') {
            $current = $conn->query("SELECT status FROM team_members WHERE id = $member_id")->fetch_assoc();
            if ($current) {
                $new_status = ($current['status'] === 'active') ? 'inactive' : 'active';
                $conn->query("UPDATE team_members SET status = '$new_status' WHERE id = $member_id");
                header("Location: team.php?msg=status_updated");
                exit();
            }
        }
    }
}

// Handle Form Submissions (Add / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $photo_url = trim($_POST['photo_url'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $display_order = intval($_POST['display_order'] ?? 0);
    $status = trim($_POST['status'] ?? 'active');
    
    if ($name && $designation) {
        $name_esc = $conn->real_escape_string($name);
        $designation_esc = $conn->real_escape_string($designation);
        $bio_esc = $conn->real_escape_string($bio);
        $photo_url_esc = $conn->real_escape_string($photo_url);
        $email_esc = $conn->real_escape_string($email);
        $status_esc = $conn->real_escape_string($status);
        
        if ($id > 0) {
            // Edit
            $query = "UPDATE team_members SET 
                name = '$name_esc', 
                designation = '$designation_esc', 
                bio = '$bio_esc', 
                photo_url = '$photo_url_esc', 
                email = '$email_esc', 
                display_order = $display_order, 
                status = '$status_esc' 
                WHERE id = $id";
            $conn->query($query);
            header("Location: team.php?msg=updated");
            exit();
        } else {
            // Add new
            $query = "INSERT INTO team_members 
                (name, designation, bio, photo_url, email, display_order, status) 
                VALUES 
                ('$name_esc', '$designation_esc', '$bio_esc', '$photo_url_esc', '$email_esc', $display_order, '$status_esc')";
            $conn->query($query);
            header("Location: team.php?msg=added");
            exit();
        }
    } else {
        $flash = 'Name and Designation are required fields.';
        $flash_class = 'alert-error';
    }
}

// Fetch query param messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added') $flash = 'Team member added successfully.';
    elseif ($_GET['msg'] === 'updated') $flash = 'Team member updated successfully.';
    elseif ($_GET['msg'] === 'deleted') $flash = 'Team member deleted successfully.';
    elseif ($_GET['msg'] === 'status_updated') $flash = 'Team member status toggled successfully.';
}

// Check if in edit mode
$edit_member = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_res = $conn->query("SELECT * FROM team_members WHERE id = $edit_id LIMIT 1");
    if ($edit_res && $edit_res->num_rows > 0) {
        $edit_member = $edit_res->fetch_assoc();
    }
}

// Fetch all team members
$result = $conn->query("SELECT * FROM team_members ORDER BY display_order ASC, name ASC");
?>
<div class="main-content">
    <header class="main-header">
        <div class="header-title">Team Management</div>
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

        <div class="detail-grid">
            <!-- Left Side: Add / Edit Form -->
            <div class="section-card" style="margin-bottom: 0;">
                <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px;">
                    <h2 class="card-title">
                        <i class="fa-solid <?= $edit_member ? 'fa-user-pen' : 'fa-user-plus' ?>"></i>
                        <?= $edit_member ? 'Edit Team Member: ' . htmlspecialchars($edit_member['name']) : 'Add Team Member' ?>
                    </h2>
                    <?php if ($edit_member): ?>
                        <a href="team.php" class="btn btn-secondary btn-sm" style="padding: 4px 8px;">Cancel Edit</a>
                    <?php endif; ?>
                </div>

                <form action="team.php" method="POST">
                    <input type="hidden" name="id" value="<?= $edit_member ? $edit_member['id'] : 0 ?>">
                    
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Rahul Mehta" required value="<?= htmlspecialchars($edit_member ? $edit_member['name'] : '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="designation">Designation *</label>
                        <input type="text" id="designation" name="designation" class="form-control" placeholder="Chief Executive Officer" required value="<?= htmlspecialchars($edit_member ? $edit_member['designation'] : '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="photo_url">Photo URL</label>
                        <input type="url" id="photo_url" name="photo_url" class="form-control" placeholder="https://example.com/photo.jpg" value="<?= htmlspecialchars($edit_member ? $edit_member['photo_url'] : '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="name@prestondaub.com" value="<?= htmlspecialchars($edit_member ? $edit_member['email'] : '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="display_order">Display Order</label>
                        <input type="number" id="display_order" name="display_order" class="form-control" placeholder="1" value="<?= htmlspecialchars($edit_member ? $edit_member['display_order'] : '0') ?>">
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="active" <?= ($edit_member && $edit_member['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($edit_member && $edit_member['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="bio">Short Biography</label>
                        <textarea id="bio" name="bio" class="form-control" placeholder="Write a short summary..."><?= htmlspecialchars($edit_member ? $edit_member['bio'] : '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fa-solid fa-save"></i>
                        <?= $edit_member ? 'Update Team Member' : 'Add Team Member' ?>
                    </button>
                </form>
            </div>

            <!-- Right Side: Members List -->
            <div class="section-card" style="margin-bottom: 0;">
                <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px;">
                    <h2 class="card-title">
                        <i class="fa-solid fa-users-viewfinder"></i>
                        Active Roster
                    </h2>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($row['name']) ?></div>
                                            <span style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($row['email'] ?? '') ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($row['designation']) ?></td>
                                        <td><?= $row['display_order'] ?></td>
                                        <td>
                                            <span class="badge badge-<?= $row['status'] ?>">
                                                <?= htmlspecialchars($row['status']) ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <div style="display: inline-flex; gap: 8px;">
                                                <a href="team.php?edit_id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm" title="Edit">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                                <a href="team.php?action=toggle&id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm" style="background-color: <?= $row['status'] === 'active' ? 'var(--accent-red)' : 'var(--accent-green)' ?>;" title="Toggle Status">
                                                    <i class="fa-solid <?= $row['status'] === 'active' ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                                </a>
                                                <a href="team.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($row['name']) ?>?')" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 32px;">
                                        No team members found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
