<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$flash = '';
$flash_class = 'alert-success';

// Handle URL GET Actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $news_id = intval($_GET['id'] ?? 0);
    
    if ($news_id > 0) {
        if ($action === 'delete') {
            $conn->query("DELETE FROM news WHERE id = $news_id");
            header("Location: news.php?msg=deleted");
            exit();
        }
    }
}

// Handle Form Submissions (Add / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $new_category = trim($_POST['new_category'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $cover_image_url = trim($_POST['cover_image_url'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = trim($_POST['status'] ?? 'draft');
    $published_at = trim($_POST['published_at'] ?? '');
    
    if ($new_category !== '') {
        $category = $new_category;
        $new_cat_esc = $conn->real_escape_string($new_category);
        $chk_cat = $conn->query("SELECT id FROM news_categories WHERE name = '$new_cat_esc' LIMIT 1")->fetch_assoc();
        if (!$chk_cat) {
            $cat_slug = strtolower(preg_replace('/[^a-zA-Z0-9\-]+/', '-', $new_category));
            $cat_slug = trim($cat_slug, '-');
            $cat_slug_esc = $conn->real_escape_string($cat_slug);
            $conn->query("INSERT INTO news_categories (name, slug) VALUES ('$new_cat_esc', '$cat_slug_esc')");
        }
    }

    if ($title && $content) {
        // Generate slug from title
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9\-]+/', '-', $title));
        $slug = trim($slug, '-');
        
        $title_esc = $conn->real_escape_string($title);
        $slug_esc = $conn->real_escape_string($slug);
        $category_esc = $conn->real_escape_string($category);
        $excerpt_esc = $conn->real_escape_string($excerpt);
        $content_esc = $conn->real_escape_string($content);
        $cover_image_esc = $conn->real_escape_string($cover_image_url);
        $status_esc = $conn->real_escape_string($status);
        
        $published_at_val = "NULL";
        if ($published_at !== '') {
            $published_at_val = "'" . $conn->real_escape_string($published_at) . "'";
        } elseif ($status === 'published') {
            $published_at_val = "NOW()";
        }
        
        if ($id > 0) {
            // Edit
            $query = "UPDATE news SET 
                title = '$title_esc', 
                slug = '$slug_esc', 
                category = '$category_esc', 
                excerpt = '$excerpt_esc', 
                content = '$content_esc', 
                cover_image_url = '$cover_image_esc', 
                featured = $featured, 
                status = '$status_esc', 
                published_at = $published_at_val
                WHERE id = $id";
            $conn->query($query);
            header("Location: news.php?msg=updated");
            exit();
        } else {
            // Add new
            $query = "INSERT INTO news 
                (title, slug, category, excerpt, content, cover_image_url, featured, status, published_at, author) 
                VALUES 
                ('$title_esc', '$slug_esc', '$category_esc', '$excerpt_esc', '$content_esc', '$cover_image_esc', $featured, '$status_esc', $published_at_val, 1)";
            $conn->query($query);
            header("Location: news.php?msg=added");
            exit();
        }
    } else {
        $flash = 'Title and Content are required fields.';
        $flash_class = 'alert-error';
    }
}

// Fetch query param messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added') $flash = 'Article added successfully.';
    elseif ($_GET['msg'] === 'updated') $flash = 'Article updated successfully.';
    elseif ($_GET['msg'] === 'deleted') $flash = 'Article deleted successfully.';
}

// Check if in edit mode
$edit_news = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_res = $conn->query("SELECT * FROM news WHERE id = $edit_id LIMIT 1");
    if ($edit_res && $edit_res->num_rows > 0) {
        $edit_news = $edit_res->fetch_assoc();
    }
}

// Fetch categories for form select
$categories_res = $conn->query("SELECT * FROM news_categories ORDER BY name ASC");

// Fetch all news articles
$result = $conn->query("SELECT * FROM news ORDER BY published_at DESC, created_at DESC");
?>
<div class="main-content">
    <header class="main-header">
        <div class="header-title">News & Blog Portal</div>
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

        <div style="display: grid; grid-template-columns: 1fr; gap: 32px; align-items: start;">
            <!-- Add / Edit Form Card -->
            <div class="section-card">
                <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px;">
                    <h2 class="card-title">
                        <i class="fa-solid <?= $edit_news ? 'fa-file-pen' : 'fa-plus' ?>"></i>
                        <?= $edit_news ? 'Edit News Article: ' . htmlspecialchars($edit_news['title']) : 'Publish / Add News Article' ?>
                    </h2>
                    <?php if ($edit_news): ?>
                        <a href="news.php" class="btn btn-secondary btn-sm" style="padding: 4px 8px;">Cancel Edit</a>
                    <?php endif; ?>
                </div>

                <form action="news.php" method="POST">
                    <input type="hidden" name="id" value="<?= $edit_news ? $edit_news['id'] : 0 ?>">
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                        <div class="form-group" style="grid-column: span 2;">
                            <label for="title">Article Title *</label>
                            <input type="text" id="title" name="title" class="form-control" placeholder="Market Analysis: Q1 Financial Performance Report" required value="<?= htmlspecialchars($edit_news ? $edit_news['title'] : '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" class="form-control" style="margin-bottom: 8px;">
                                <option value="">-- Select Existing Category --</option>
                                <?php if ($categories_res && $categories_res->num_rows > 0): ?>
                                    <?php 
                                    $categories_res->data_seek(0);
                                    while ($cat = $categories_res->fetch_assoc()): 
                                    ?>
                                        <option value="<?= htmlspecialchars($cat['name']) ?>" <?= ($edit_news && $edit_news['category'] === $cat['name']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                            <input type="text" name="new_category" class="form-control" placeholder="Or enter new category...">
                        </div>

                        <div class="form-group">
                            <label for="status">Publication Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="draft" <?= ($edit_news && $edit_news['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                                <option value="published" <?= ($edit_news && $edit_news['status'] === 'published') ? 'selected' : '' ?>>Published</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="cover_image_url">Cover Image URL</label>
                            <input type="text" id="cover_image_url" name="cover_image_url" class="form-control" placeholder="../assets/img/service/cst/thumb.jpg" value="<?= htmlspecialchars($edit_news ? $edit_news['cover_image_url'] : '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="published_at">Publish Schedule (Leave blank for immediate/default)</label>
                            <input type="datetime-local" id="published_at" name="published_at" class="form-control" value="<?= ($edit_news && $edit_news['published_at']) ? date('Y-m-d\TH:i', strtotime($edit_news['published_at'])) : '' ?>">
                        </div>
                        
                        <div class="form-group" style="grid-column: span 2; display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" id="featured" name="featured" value="1" <?= ($edit_news && $edit_news['featured']) ? 'checked' : '' ?> style="width: 18px; height: 18px; cursor: pointer;">
                            <label for="featured" style="margin-bottom: 0; cursor: pointer; user-select: none;">Mark this article as Featured / Top Headline</label>
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label for="excerpt">Brief Excerpt / Summary</label>
                            <textarea id="excerpt" name="excerpt" class="form-control" placeholder="Write a short summary or hook of the article..." style="min-height: 70px;"><?= htmlspecialchars($edit_news ? $edit_news['excerpt'] : '') ?></textarea>
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label for="content">Article Body (Supports HTML tags like &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;)</label>
                            <textarea id="content" name="content" class="form-control" placeholder="Write the full content of the news article here..." style="min-height: 250px;" required><?= htmlspecialchars($edit_news ? $edit_news['content'] : '') ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 12px; width: 100%;">
                        <i class="fa-solid fa-save"></i>
                        <?= $edit_news ? 'Save Changes' : 'Publish Article' ?>
                    </button>
                </form>
            </div>

            <!-- Published Articles List Card -->
            <div class="section-card">
                <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px;">
                    <h2 class="card-title">
                        <i class="fa-solid fa-newspaper"></i>
                        Articles Archive
                    </h2>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Published At</th>
                                <th>Status</th>
                                <th style="text-align: right; width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($row['title']) ?></div>
                                            <span style="font-size: 0.8rem; color: var(--text-muted);">
                                                Slug: <?= htmlspecialchars($row['slug']) ?>
                                                <?= $row['featured'] ? ' | <span style="color: var(--accent-indigo); font-weight:600;">★ Featured</span>' : '' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-read" style="background-color: rgba(99, 102, 241, 0.1); color: var(--accent-indigo);">
                                                <?= htmlspecialchars($row['category'] ?? 'Uncategorized') ?>
                                            </span>
                                        </td>
                                        <td><?= $row['published_at'] ? date('M d, Y g:i A', strtotime($row['published_at'])) : 'Draft (Not Published)' ?></td>
                                        <td>
                                            <span class="badge badge-<?= $row['status'] === 'published' ? 'read' : 'new' ?>">
                                                <?= htmlspecialchars($row['status']) ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <div style="display: inline-flex; gap: 8px;">
                                                <a href="news.php?edit_id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm" title="Edit">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                                <a href="news.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this article permanently?')" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 32px;">
                                        No news articles published yet.
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
