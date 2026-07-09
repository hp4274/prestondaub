<?php
// Public read-only endpoint for published news articles
require_once __DIR__ . '/config/database.php';
header('Content-Type: application/json');

// Check if specific article is requested by slug
if (isset($_GET['slug'])) {
    $slug = trim($_GET['slug']);
    $slug_esc = $conn->real_escape_string($slug);
    
    // Increment view count if read
    $conn->query("UPDATE news SET views = views + 1 WHERE slug = '$slug_esc' AND status = 'published'");
    
    $result = $conn->query("SELECT * FROM news WHERE slug = '$slug_esc' AND status = 'published' LIMIT 1");
    
    if ($result && $result->num_rows > 0) {
        $article = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'data' => $article
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Article not found.'
        ]);
    }
    exit();
}

// Fetch list of articles
$page = intval($_GET['page'] ?? 1);
$per_page = intval($_GET['per_page'] ?? 6);
$category = trim($_GET['category'] ?? 'all');
$search = trim($_GET['search'] ?? '');

$where = ["status = 'published'"];

if ($category !== 'all' && $category !== '') {
    $category_esc = $conn->real_escape_string($category);
    $where[] = "category = '$category_esc'";
}

if ($search !== '') {
    $search_esc = $conn->real_escape_string($search);
    $where[] = "(title LIKE '%$search_esc%' OR excerpt LIKE '%$search_esc%' OR content LIKE '%$search_esc%')";
}

$where_clause = "WHERE " . implode(" AND ", $where);

// Count total
$count_res = $conn->query("SELECT COUNT(*) as count FROM news $where_clause");
$total_rows = $count_res->fetch_assoc()['count'] ?? 0;
$total_pages = ceil($total_rows / $per_page);
if ($total_pages < 1) $total_pages = 1;
if ($page > $total_pages) $page = $total_pages;

// Fetch list
$offset = ($page - 1) * $per_page;
$query = "SELECT * FROM news $where_clause ORDER BY published_at DESC, created_at DESC LIMIT $per_page OFFSET $offset";
$news_res = $conn->query($query);

$articles = [];
if ($news_res) {
    while ($row = $news_res->fetch_assoc()) {
        $articles[] = $row;
    }
}

// Fetch categories for filter list
$cats_res = $conn->query("SELECT name, slug FROM news_categories ORDER BY name ASC");
$categories = [];
if ($cats_res) {
    while ($row = $cats_res->fetch_assoc()) {
        $categories[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'data' => $articles,
    'categories' => $categories,
    'pagination' => [
        'total' => (int)$total_rows,
        'current_page' => $page,
        'per_page' => $per_page,
        'total_pages' => $total_pages
    ]
]);
exit();
?>
