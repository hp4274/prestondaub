<?php
/**
 * Test News Database Connection and Create Sample Article
 */

require_once 'config/database.php';

echo "=== NEWS DATABASE TEST ===\n\n";

// Test 1: Check if news table exists
echo "Test 1: Checking news table...\n";
$result = $conn->query("SHOW TABLES LIKE 'news'");
if ($result && $result->num_rows > 0) {
    echo "✓ News table exists\n\n";
} else {
    echo "✗ News table does not exist\n";
    exit;
}

// Test 2: Check table structure
echo "Test 2: Checking table columns...\n";
$columns = $conn->query("SHOW COLUMNS FROM news");
$col_names = [];
while ($col = $columns->fetch_assoc()) {
    $col_names[] = $col['Field'];
    echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
}
echo "\n";

// Test 3: Count articles
echo "Test 3: Article count...\n";
$count_all = $conn->query("SELECT COUNT(*) as cnt FROM news")->fetch_assoc()['cnt'];
$count_published = $conn->query("SELECT COUNT(*) as cnt FROM news WHERE status = 'published'")->fetch_assoc()['cnt'];
echo "  Total: $count_all\n";
echo "  Published: $count_published\n\n";

// Test 4: Create a sample article if none exist
if ($count_published == 0) {
    echo "Test 4: Creating sample article...\n";
    $title = "Welcome to Preston Daub News";
    $slug = "welcome-to-preston-daub-news";
    $excerpt = "Stay updated with the latest news and insights from Preston Daub";
    $content = "<p>Welcome to our news section! Here you'll find the latest updates on business financing, sports investments, and market insights.</p><p>This is a sample article to demonstrate our news system.</p>";
    $category = "Updates";
    $status = "published";
    $now = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO news (title, slug, excerpt, content, category, status, published_at, created_at) 
            VALUES ('$title', '$slug', '$excerpt', '$content', '$category', '$status', '$now', '$now')";
    
    if ($conn->query($sql)) {
        echo "✓ Sample article created successfully (ID: " . $conn->insert_id . ")\n\n";
    } else {
        echo "✗ Error creating sample article: " . $conn->error . "\n\n";
    }
}

// Test 5: List recent articles
echo "Test 5: Recent published articles...\n";
$articles = $conn->query("SELECT id, title, status, published_at FROM news WHERE status = 'published' ORDER BY published_at DESC LIMIT 5");
if ($articles && $articles->num_rows > 0) {
    while ($article = $articles->fetch_assoc()) {
        echo "  - [ID: " . $article['id'] . "] " . $article['title'] . " (" . $article['published_at'] . ")\n";
    }
} else {
    echo "  No published articles found.\n";
}
echo "\n";

// Test 6: Test the query used in news.php
echo "Test 6: Testing news.php query...\n";
$page = 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;

$total_result = $conn->query("SELECT COUNT(*) as count FROM news WHERE status = 'published'");
if (!$total_result) {
    echo "✗ Error in count query: " . $conn->error . "\n";
} else {
    $total_count = $total_result->fetch_assoc()['count'];
    echo "  Total count query result: $total_count\n";
}

$articles = $conn->query("SELECT * FROM news WHERE status = 'published' ORDER BY published_at DESC LIMIT $per_page OFFSET $offset");
if (!$articles) {
    echo "✗ Error in articles query: " . $conn->error . "\n";
} else {
    echo "  Articles query returned: " . $articles->num_rows . " rows\n";
    echo "✓ news.php query works!\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
