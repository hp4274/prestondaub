<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Handle Actions (delete, status change)
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $formId = $_GET['id'] ?? '';
    
    if ($formId) {
        $parts = explode('-', $formId, 2);
        $type = $parts[0] ?? '';
        $realId = $parts[1] ?? '';
        $realId_escaped = $conn->real_escape_string($realId);
        
        $table = '';
        if ($type === 'contact') $table = 'contact_submissions';
        elseif ($type === 'financing') $table = 'financing_submissions';
        elseif ($type === 'mosaic') $table = 'mosaic_submissions';
        elseif ($type === 'prospera') $table = 'prospera_submissions';
        
        if ($table) {
            if ($action === 'delete') {
                $conn->query("DELETE FROM $table WHERE id = '$realId_escaped'");
                header("Location: forms.php?msg=deleted");
                exit();
            } elseif ($action === 'status') {
                $newStatus = $_GET['status'] ?? '';
                if (in_array($newStatus, ['new', 'read', 'spam'])) {
                    $status_escaped = $conn->real_escape_string($newStatus);
                    $conn->query("UPDATE $table SET status = '$status_escaped' WHERE id = '$realId_escaped'");
                    header("Location: forms.php?msg=status_updated");
                    exit();
                }
            }
        }
    }
}

// Fetch query filters
$module = $_GET['module'] ?? '';
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = intval($_GET['page'] ?? 1);
$per_page = 15;

// Subquery union to construct a unified view of all submissions
$subquery = "
    SELECT 
        CONCAT('contact-', id) as id,
        name,
        email,
        phone,
        company,
        message,
        'contact' as form_type,
        status,
        priority,
        created_at
    FROM contact_submissions

    UNION ALL

    SELECT 
        CONCAT('financing-', id) as id,
        CONCAT(first_name, ' ', last_name) as name,
        email,
        phone,
        company,
        IFNULL(goals_challenges, 'Financing Application') as message,
        form_subtype as form_type,
        status,
        priority,
        created_at
    FROM financing_submissions

    UNION ALL

    SELECT 
        CONCAT('mosaic-', id) as id,
        name,
        email,
        phone,
        organization as company,
        message,
        'mosaic' as form_type,
        status,
        priority,
        created_at
    FROM mosaic_submissions

    UNION ALL

    SELECT 
        CONCAT('prospera-', id) as id,
        CONCAT(first_name, ' ', last_name) as name,
        email,
        phone,
        company,
        IFNULL(goals_challenges, 'Prospera Application') as message,
        'prospera' as form_type,
        status,
        priority,
        created_at
    FROM prospera_submissions
";

// Build query conditions
$where = [];
if ($module) {
    if ($module === 'financing') {
        $where[] = "form_type IN ('sba-loans', 'equipment-loans', 'bridge-loans', 'working-capital', 'abl-loans', 'commercial-real-estate')";
    } else {
        $module_escaped = $conn->real_escape_string($module);
        $where[] = "form_type = '$module_escaped'";
    }
}
if ($status && $status !== 'all') {
    $status_escaped = $conn->real_escape_string($status);
    $where[] = "status = '$status_escaped'";
}
if ($search) {
    $search_escaped = $conn->real_escape_string($search);
    $where[] = "(name LIKE '%$search_escaped%' OR email LIKE '%$search_escaped%' OR phone LIKE '%$search_escaped%')";
}

$where_clause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Count total
$count_result = $conn->query("SELECT COUNT(*) as count FROM ($subquery) as combined $where_clause");
$total_rows = $count_result->fetch_assoc()['count'] ?? 0;
$total_pages = ceil($total_rows / $per_page);
if ($total_pages < 1) $total_pages = 1;
if ($page > $total_pages) $page = $total_pages;

// Fetch forms
$offset = ($page - 1) * $per_page;
$query = "SELECT * FROM ($subquery) as combined $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$result = $conn->query($query);

// Flash message
$flash = '';
$flash_class = 'alert-success';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') {
        $flash = 'Inquiry deleted successfully.';
    } elseif ($_GET['msg'] === 'status_updated') {
        $flash = 'Inquiry status updated successfully.';
    }
}
?>
<div class="main-content">
    <header class="main-header">
        <div class="header-title">Inquiries & Form Submissions</div>
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
                <i class="fa-solid fa-circle-check"></i>
                <?= htmlspecialchars($flash) ?>
            </div>
        <?php endif; ?>

        <!-- Filters & Search -->
        <div class="section-card" style="padding: 16px 24px; margin-bottom: 24px;">
            <form action="forms.php" method="GET" class="filter-bar">
                <div class="filter-group">
                    <label for="module" style="display: none;">Module</label>
                    <select id="module" name="module" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Form Modules</option>
                        <option value="contact" <?= $module === 'contact' ? 'selected' : '' ?>>Contact Form</option>
                        <option value="financing" <?= $module === 'financing' ? 'selected' : '' ?>>Financing Forms</option>
                        <option value="mosaic" <?= $module === 'mosaic' ? 'selected' : '' ?>>Mosaic Forms</option>
                        <option value="prospera" <?= $module === 'prospera' ? 'selected' : '' ?>>Prospera Forms</option>
                    </select>

                    <label for="status" style="display: none;">Status</label>
                    <select id="status" name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="new" <?= $status === 'new' ? 'selected' : '' ?>>New</option>
                        <option value="read" <?= $status === 'read' ? 'selected' : '' ?>>Read</option>
                        <option value="spam" <?= $status === 'spam' ? 'selected' : '' ?>>Spam</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <input type="text" name="search" class="search-input" placeholder="Search by name, email, phone..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary btn-sm" style="padding: 8px 16px;">Search</button>
                    <?php if ($module || $status !== 'all' || $search): ?>
                        <a href="forms.php" class="btn btn-secondary btn-sm" style="padding: 8px 16px;">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Forms Submissions Table -->
        <div class="section-card">
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Submitted At</th>
                            <th>Status</th>
                            <th style="text-align: right; width: 300px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span style="font-weight: 600; color: var(--accent-indigo);">
                                            <?= htmlspecialchars(ucfirst($row['form_type'])) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['phone'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['company'] ?? '-') ?></td>
                                    <td><?= date('M d, Y g:i A', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $row['status'] ?>">
                                            <?= htmlspecialchars($row['status']) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: inline-flex; gap: 8px; justify-content: flex-end; width: 100%;">
                                            <a href="form-detail.php?id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm" title="View Details">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            
                                            <?php if ($row['status'] === 'new'): ?>
                                                <a href="forms.php?action=status&id=<?= $row['id'] ?>&status=read" class="btn btn-primary btn-sm" style="background-color: var(--accent-green);" title="Mark Read">
                                                    <i class="fa-solid fa-check"></i>
                                                </a>
                                                <a href="forms.php?action=status&id=<?= $row['id'] ?>&status=spam" class="btn btn-secondary btn-sm" style="background-color: #64748b;" title="Mark Spam">
                                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                                </a>
                                            <?php elseif ($row['status'] === 'read'): ?>
                                                <a href="forms.php?action=status&id=<?= $row['id'] ?>&status=new" class="btn btn-secondary btn-sm" title="Mark Unread">
                                                    <i class="fa-solid fa-envelope"></i>
                                                </a>
                                            <?php elseif ($row['status'] === 'spam'): ?>
                                                <a href="forms.php?action=status&id=<?= $row['id'] ?>&status=new" class="btn btn-secondary btn-sm" title="Mark Not Spam">
                                                    <i class="fa-solid fa-envelope"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="forms.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this inquiry permanently?')" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 32px;">
                                    No submissions match your filters.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination links -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <div class="pagination-info">
                        Showing page <strong><?= $page ?></strong> of <strong><?= $total_pages ?></strong> (Total: <?= $total_rows ?> entries)
                    </div>
                    <div class="pagination-links">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php
                            $params = $_GET;
                            $params['page'] = $i;
                            $qs = http_build_query($params);
                            ?>
                            <a href="forms.php?<?= $qs ?>" class="pagination-link <?= $page === $i ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
