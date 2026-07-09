<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Union query for counts
$subquery = "
    SELECT id, status, created_at FROM contact_submissions
    UNION ALL
    SELECT id, status, created_at FROM financing_submissions
    UNION ALL
    SELECT id, status, created_at FROM mosaic_submissions
    UNION ALL
    SELECT id, status, created_at FROM prospera_submissions
";

// Fetch overall metric counts
$total_count = $conn->query("SELECT COUNT(*) as count FROM ($subquery) as combined")->fetch_assoc()['count'] ?? 0;
$new_count = $conn->query("SELECT COUNT(*) as count FROM ($subquery) as combined WHERE status = 'new'")->fetch_assoc()['count'] ?? 0;
$read_count = $conn->query("SELECT COUNT(*) as count FROM ($subquery) as combined WHERE status = 'read'")->fetch_assoc()['count'] ?? 0;
$spam_count = $conn->query("SELECT COUNT(*) as count FROM ($subquery) as combined WHERE status = 'spam'")->fetch_assoc()['count'] ?? 0;

// Fetch 10 most recent submissions
$recent_query = "
    SELECT * FROM (
        SELECT CONCAT('contact-', id) as id, name, email, phone, company, message, 'contact' as form_type, status, created_at FROM contact_submissions
        UNION ALL
        SELECT CONCAT('financing-', id) as id, CONCAT(first_name, ' ', last_name) as name, email, phone, company, IFNULL(goals_challenges, 'Financing Application') as message, form_subtype as form_type, status, created_at FROM financing_submissions
        UNION ALL
        SELECT CONCAT('mosaic-', id) as id, name, email, phone, organization as company, message, 'mosaic' as form_type, status, created_at FROM mosaic_submissions
        UNION ALL
        SELECT CONCAT('prospera-', id) as id, CONCAT(first_name, ' ', last_name) as name, email, phone, company, IFNULL(goals_challenges, 'Prospera Application') as message, 'prospera' as form_type, status, created_at FROM prospera_submissions
    ) as combined
    ORDER BY created_at DESC
    LIMIT 10
";
$recent_result = $conn->query($recent_query);
?>
<div class="main-content">
    <header class="main-header">
        <div class="header-title">Dashboard Overview</div>
        <div class="user-profile">
            <div class="user-avatar"><?= $admin_initial ?></div>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($admin_name) ?></span>
                <span class="user-role">Administrator</span>
            </div>
        </div>
    </header>
    
    <div class="content-body">
        <!-- Metrics -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-info">
                    <h3>Total Inquiries</h3>
                    <div class="value"><?= $total_count ?></div>
                </div>
                <div class="metric-icon indigo">
                    <i class="fa-solid fa-inbox"></i>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-info">
                    <h3>New Inquiries</h3>
                    <div class="value"><?= $new_count ?></div>
                </div>
                <div class="metric-icon blue">
                    <i class="fa-solid fa-envelope-open-text"></i>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-info">
                    <h3>Processed</h3>
                    <div class="value"><?= $read_count ?></div>
                </div>
                <div class="metric-icon green">
                    <i class="fa-solid fa-check"></i>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-info">
                    <h3>Spam</h3>
                    <div class="value"><?= $spam_count ?></div>
                </div>
                <div class="metric-icon red">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
        </div>
        
        <!-- Recent Submissions -->
        <div class="section-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Recent Submissions
                </h2>
                <a href="forms.php" class="btn btn-primary btn-sm">View All</a>
            </div>
            
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Form Type</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Submitted At</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_result && $recent_result->num_rows > 0): ?>
                            <?php while ($row = $recent_result->fetch_assoc()): ?>
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
                                        <a href="form-detail.php?id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm">
                                            <i class="fa-solid fa-eye"></i>
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 32px;">
                                    No submissions found yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
