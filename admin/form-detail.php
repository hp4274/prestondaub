<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$formId = $_GET['id'] ?? '';
if (!$formId) {
    echo "<div class='main-content'><div class='content-body'><div class='alert alert-error'>Form ID is required.</div></div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

$parts = explode('-', $formId, 2);
$type = $parts[0] ?? '';
$realId = $parts[1] ?? '';

$realId_escaped = $conn->real_escape_string($realId);
$table = '';
$form_name_display = '';

if ($type === 'contact') {
    $table = 'contact_submissions';
    $form_name_display = 'Contact Form Inquiry';
} elseif ($type === 'financing') {
    $table = 'financing_submissions';
    $form_name_display = 'Financing Application Form';
} elseif ($type === 'mosaic') {
    $table = 'mosaic_submissions';
    $form_name_display = 'Mosaic Software Demo Request';
} elseif ($type === 'prospera') {
    $table = 'prospera_submissions';
    $form_name_display = 'Prospera Form Inquiry';
}

if (!$table) {
    echo "<div class='main-content'><div class='content-body'><div class='alert alert-error'>Invalid Form ID format.</div></div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

$result = $conn->query("SELECT * FROM $table WHERE id = '$realId_escaped' LIMIT 1");
if (!$result || $result->num_rows === 0) {
    echo "<div class='main-content'><div class='content-body'><div class='alert alert-error'>Inquiry not found.</div></div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

$form = $result->fetch_assoc();

// Mark as read automatically when opened if it's currently marked 'new'
if ($form['status'] === 'new') {
    $conn->query("UPDATE $table SET status = 'read' WHERE id = '$realId_escaped'");
    $form['status'] = 'read';
}
?>
<div class="main-content">
    <header class="main-header">
        <div class="header-title">Inquiry Details</div>
        <div class="user-profile">
            <div class="user-avatar"><?= $admin_initial ?></div>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($admin_name) ?></span>
                <span class="user-role">Administrator</span>
            </div>
        </div>
    </header>
    
    <div class="content-body">
        <div style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
            <a href="forms.php" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Submissions
            </a>
            
            <div style="display: flex; gap: 12px;">
                <?php if ($form['status'] !== 'spam'): ?>
                    <a href="forms.php?action=status&id=<?= $formId ?>&status=spam" class="btn btn-secondary btn-sm" style="background-color: #64748b;" title="Mark Spam">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Spam
                    </a>
                <?php else: ?>
                    <a href="forms.php?action=status&id=<?= $formId ?>&status=new" class="btn btn-secondary btn-sm" title="Mark Unread">
                        <i class="fa-solid fa-envelope"></i>
                        Mark Unread
                    </a>
                <?php endif; ?>
                <a href="forms.php?action=delete&id=<?= $formId ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this inquiry permanently?')" title="Delete">
                    <i class="fa-solid fa-trash"></i>
                    Delete
                </a>
            </div>
        </div>

        <div class="section-card">
            <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 32px;">
                <h2 class="card-title">
                    <i class="fa-solid fa-file-invoice"></i>
                    <?= htmlspecialchars($form_name_display) ?>
                </h2>
                <span class="badge badge-<?= $form['status'] ?>"><?= htmlspecialchars($form['status']) ?></span>
            </div>
            
            <div class="detail-grid">
                <?php if ($type === 'financing' || $type === 'prospera'): ?>
                    <div class="detail-item">
                        <div class="detail-label">First Name</div>
                        <div class="detail-val"><?= htmlspecialchars($form['first_name'] ?? '') ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Last Name</div>
                        <div class="detail-val"><?= htmlspecialchars($form['last_name'] ?? '') ?></div>
                    </div>
                <?php else: ?>
                    <div class="detail-item full">
                        <div class="detail-label">Full Name</div>
                        <div class="detail-val"><?= htmlspecialchars($form['name'] ?? '') ?></div>
                    </div>
                <?php endif; ?>

                <div class="detail-item">
                    <div class="detail-label">Email Address</div>
                    <div class="detail-val"><?= htmlspecialchars($form['email'] ?? '') ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Phone Number</div>
                    <div class="detail-val"><?= htmlspecialchars($form['phone'] ?? '-') ?></div>
                </div>

                <?php if ($type === 'mosaic'): ?>
                    <div class="detail-item">
                        <div class="detail-label">Organization</div>
                        <div class="detail-val"><?= htmlspecialchars($form['organization'] ?? '-') ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Organization Type</div>
                        <div class="detail-val"><?= htmlspecialchars($form['organization_type'] ?? '-') ?></div>
                    </div>
                <?php else: ?>
                    <div class="detail-item">
                        <div class="detail-label">Company</div>
                        <div class="detail-val"><?= htmlspecialchars($form['company'] ?? '-') ?></div>
                    </div>
                    
                    <?php if (isset($form['job_title'])): ?>
                        <div class="detail-item">
                            <div class="detail-label">Job Title</div>
                            <div class="detail-val"><?= htmlspecialchars($form['job_title'] ?? '-') ?></div>
                        </div>
                    <?php else: ?>
                        <div class="detail-item">
                            <div class="detail-label">Priority</div>
                            <div class="detail-val" style="text-transform: capitalize;"><?= htmlspecialchars($form['priority'] ?? 'Low') ?></div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($type === 'financing' && isset($form['form_subtype'])): ?>
                    <div class="detail-item">
                        <div class="detail-label">Form Category Subtype</div>
                        <div class="detail-val" style="text-transform: uppercase; font-weight: 600; color: var(--accent-indigo);">
                            <?= htmlspecialchars($form['form_subtype']) ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Submitted At</div>
                        <div class="detail-val"><?= date('F d, Y g:i A', strtotime($form['created_at'])) ?></div>
                    </div>
                <?php else: ?>
                    <div class="detail-item full">
                        <div class="detail-label">Submitted At</div>
                        <div class="detail-val"><?= date('F d, Y g:i A', strtotime($form['created_at'])) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (isset($form['interests']) && $form['interests'] !== ''): ?>
                    <div class="detail-item full">
                        <div class="detail-label">Interests</div>
                        <div class="detail-val"><?= nl2br(htmlspecialchars($form['interests'])) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (isset($form['goals_challenges']) && $form['goals_challenges'] !== ''): ?>
                    <div class="detail-item full">
                        <div class="detail-label">Goals & Challenges</div>
                        <div class="detail-val"><?= nl2br(htmlspecialchars($form['goals_challenges'])) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (isset($form['message']) && $form['message'] !== ''): ?>
                    <div class="detail-item full">
                        <div class="detail-label">Message / Details</div>
                        <div class="detail-val" style="min-height: 100px;"><?= nl2br(htmlspecialchars($form['message'])) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
