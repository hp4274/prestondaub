<?php
/**
 * Seed Sample News Articles
 * Run this once to create sample published articles for testing
 * Then delete this file
 */

// For development/testing only
// If articles already exist, this script will skip them
if (!isset($_GET['confirm'])) {
    echo "<h2>Create Sample News Articles?</h2>";
    echo "<p>This will create 5 sample published articles.</p>";
    echo "<p><a href='?confirm=yes' style='padding: 10px 20px; background: #0066cc; color: white; text-decoration: none; border-radius: 4px;'>Create Sample Articles</a></p>";
    exit;
}

require_once 'config/database.php';

echo "<h2>Seeding Sample News Articles</h2>\n";

// Sample articles
$articles = [
    [
        'title' => 'Navigating Business Financing in 2024',
        'excerpt' => 'Explore the latest trends in business financing and how to choose the right solution for your company.',
        'content' => '<p>Business financing landscape continues to evolve with new opportunities and challenges. Whether you\'re looking for working capital, equipment financing, or expansion loans, understanding your options is crucial.</p><p>Preston Daub brings years of expertise in connecting businesses with the right financing solutions tailored to their unique needs.</p>',
        'category' => 'Business Financing',
        'featured' => 1
    ],
    [
        'title' => 'Sports Investment Opportunities 2024',
        'excerpt' => 'Discover emerging opportunities in sports investments across multiple leagues and franchises.',
        'content' => '<p>The sports investment sector continues to grow with new franchises, leagues, and investment opportunities emerging globally.</p><p>From cricket to NFL, MLB to Formula 1, there are numerous ways to participate in this thriving market. Learn about the latest opportunities and how to get started.</p>',
        'category' => 'Sports Investments',
        'featured' => 1
    ],
    [
        'title' => 'SBA Loans: What You Need to Know',
        'excerpt' => 'A comprehensive guide to Small Business Administration loans and how they can fuel your business growth.',
        'content' => '<p>SBA loans remain one of the most popular financing options for small to medium-sized businesses. With favorable terms and government backing, they offer stability and predictability for business owners.</p><p>Learn about different SBA loan types, eligibility requirements, and application processes.</p>',
        'category' => 'Business Financing',
        'featured' => 0
    ],
    [
        'title' => 'Bridge Loans: Bridging the Gap to Success',
        'excerpt' => 'Understand how bridge loans can provide the short-term capital you need to reach long-term goals.',
        'content' => '<p>Bridge loans serve as a valuable intermediate financing solution for businesses facing cash flow gaps or timing mismatches between major transactions.</p><p>Whether you\'re in real estate, business acquisition, or expansion, bridge loans can provide the flexible financing you need.</p>',
        'category' => 'Business Financing',
        'featured' => 0
    ],
    [
        'title' => 'Equipment Financing Essentials',
        'excerpt' => 'Maximize your business potential with strategic equipment financing solutions.',
        'content' => '<p>Equipment is often the backbone of business operations, from manufacturing to services. Equipment financing allows you to acquire essential assets without depleting working capital.</p><p>Discover different equipment financing options and how to leverage them for business growth.</p>',
        'category' => 'Business Financing',
        'featured' => 0
    ]
];

$created = 0;
$skipped = 0;

foreach ($articles as $article) {
    $title = $conn->real_escape_string($article['title']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $article['title']), '-'));
    $excerpt = $conn->real_escape_string($article['excerpt']);
    $content = $conn->real_escape_string($article['content']);
    $category = $conn->real_escape_string($article['category']);
    $featured = $article['featured'] ? 1 : 0;
    
    // Check if article already exists
    $check = $conn->query("SELECT id FROM news WHERE slug = '$slug'");
    if ($check && $check->num_rows > 0) {
        echo "<p>⊘ Skipped: '{$article['title']}' (already exists)</p>\n";
        $skipped++;
        continue;
    }
    
    // Create article
    $now = date('Y-m-d H:i:s');
    $sql = "INSERT INTO news (title, slug, excerpt, content, category, status, featured, published_at, created_at, updated_at, views) 
            VALUES ('$title', '$slug', '$excerpt', '$content', '$category', 'published', $featured, '$now', '$now', '$now', 0)";
    
    if ($conn->query($sql)) {
        echo "<p>✓ Created: '{$article['title']}'</p>\n";
        $created++;
    } else {
        echo "<p>✗ Error: '{$article['title']}' - " . $conn->error . "</p>\n";
    }
}

echo "\n<hr>\n";
echo "<p><strong>Summary:</strong> Created $created articles, Skipped $skipped</p>\n";
echo "<p><a href='../about/news.php'>View News Page →</a></p>\n";
echo "<p style='color: #999; font-size: 12px; margin-top: 20px;'>You can now delete this file: assets/seed-news.php</p>\n";

$conn->close();
?>
