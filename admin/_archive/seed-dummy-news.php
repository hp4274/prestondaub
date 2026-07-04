<?php
/**
 * Seed Dummy News Articles
 * This script adds sample news articles to the database for testing
 */

require_once 'config/auth.php';
require_login();
require_once 'config/database.php';

header('Content-Type: application/json');

// Check if we already have dummy data - using a unique identifier
$existing = $conn->query("SELECT COUNT(*) as count FROM news WHERE title LIKE '%Market Analysis: Q1 Financial%'");
$count = $existing->fetch_assoc()['count'];

if ($count > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Sample articles already exist. Click "Clear Sample Articles" first if you want to re-seed.'
    ]);
    exit();
}

// Get admin user ID (logged-in user)
$admin_result = $conn->query("SELECT id FROM admins LIMIT 1");
$admin = $admin_result->fetch_assoc();
$admin_id = $admin['id'] ?? 1;

// Dummy news data
$dummy_news = [
    [
        'title' => 'Market Analysis: Q1 Financial Performance Report',
        'excerpt' => 'The first quarter of 2026 has shown remarkable growth across all major financial sectors. Our comprehensive analysis reveals emerging opportunities and strategic insights.',
        'content' => '<h2>Executive Summary</h2><p>The first quarter of 2026 has shown remarkable growth across all major financial sectors. Our comprehensive analysis reveals emerging opportunities and strategic insights for investors.</p><h2>Key Findings</h2><ul><li>Growth in technology sector reached 24% YoY</li><li>Financial services showed resilience with 15% expansion</li><li>Real estate investments maintained stability</li><li>Emerging markets demonstrated strong momentum</li></ul><p>These trends indicate a healthy market environment for strategic investors.</p>',
        'category' => 'Market Analysis',
        'cover_image_url' => '../assets/img/service/cst/thumb.jpg',
        'status' => 'published'
    ],
    [
        'title' => 'Expert Interview: Future of Wealth Management',
        'excerpt' => 'Industry leaders discuss the evolving landscape of wealth management and what investors should expect in the coming years.',
        'content' => '<h2>Interview Highlights</h2><p>We sat down with leading wealth management experts to discuss the future of the industry.</p><h2>Key Insights</h2><ul><li>Personalization is becoming increasingly important</li><li>Technology integration is reshaping client relationships</li><li>ESG considerations are influencing investment strategies</li><li>Multi-generational wealth transfer presents new opportunities</li></ul><p>The future of wealth management looks promising with these innovations.</p>',
        'category' => 'Expert Insights',
        'cover_image_url' => '../assets/img/service/cst/thumb.jpg',
        'status' => 'published'
    ],
    [
        'title' => 'Guide: Diversification Strategies for Modern Investors',
        'excerpt' => 'Learn how to build a diversified portfolio that aligns with your investment goals and risk tolerance.',
        'content' => '<h2>Understanding Diversification</h2><p>Diversification is a fundamental strategy for managing investment risk. This guide will walk you through key principles.</p><h2>Diversification Techniques</h2><ul><li>Asset allocation across different classes</li><li>Geographic diversification</li><li>Sector-specific investments</li><li>Time-based diversification strategies</li></ul><p>By following these strategies, you can build a robust investment portfolio.</p>',
        'category' => 'Investment Strategy',
        'cover_image_url' => '../assets/img/service/cst/thumb.jpg',
        'status' => 'published'
    ],
    [
        'title' => 'Business Financing Solutions: What\'s New in 2026',
        'excerpt' => 'Explore the latest financing options available to businesses, from SBA loans to alternative lending sources.',
        'content' => '<h2>2026 Financing Landscape</h2><p>The business financing landscape continues to evolve with new options emerging for entrepreneurs.</p><h2>Available Options</h2><ul><li>Traditional bank loans with improved terms</li><li>SBA loan programs with reduced paperwork</li><li>Equipment financing solutions</li><li>Working capital solutions for growing businesses</li></ul><p>Find the right financing solution for your business needs.</p>',
        'category' => 'Business Financing',
        'cover_image_url' => '../assets/img/service/cst/thumb.jpg',
        'status' => 'published'
    ],
    [
        'title' => 'Sports Investment Trends: Opportunities in 2026',
        'excerpt' => 'Discover the growing opportunities in sports investment and how investors are capitalizing on this expanding market.',
        'content' => '<h2>Sports Investment Growth</h2><p>Sports investment has become increasingly attractive to institutional and individual investors alike.</p><h2>Investment Opportunities</h2><ul><li>Professional sports franchises</li><li>Sports technology companies</li><li>Athlete management and endorsements</li><li>Sports venue development</li></ul><p>The sports investment sector offers unique opportunities for diversified portfolios.</p>',
        'category' => 'Sports Investments',
        'cover_image_url' => '../assets/img/service/cst/thumb.jpg',
        'status' => 'published'
    ],
    [
        'title' => 'Technology Innovation: Reshaping Financial Services',
        'excerpt' => 'How fintech innovations are transforming the way we manage money and invest for the future.',
        'content' => '<h2>FinTech Revolution</h2><p>Technology continues to reshape the financial services industry at an unprecedented pace.</p><h2>Key Innovations</h2><ul><li>Artificial intelligence in portfolio management</li><li>Blockchain technology for transparency</li><li>Mobile-first financial platforms</li><li>Real-time data analytics and insights</li></ul><p>These innovations are making financial services more accessible and efficient.</p>',
        'category' => 'Technology',
        'cover_image_url' => '../assets/img/service/cst/thumb.jpg',
        'status' => 'published'
    ],
    [
        'title' => 'Sustainable Investing: Building Wealth Responsibly',
        'excerpt' => 'Learn how sustainable investment practices can generate returns while making a positive impact on society.',
        'content' => '<h2>Sustainable Investment Approach</h2><p>Sustainable investing aligns financial returns with positive environmental and social impact.</p><h2>Benefits of Sustainable Investing</h2><ul><li>Long-term portfolio resilience</li><li>Reduced exposure to regulatory risks</li><li>Positive environmental and social impact</li><li>Growing investor interest and demand</li></ul><p>Sustainable investing is no longer just an option—it\'s becoming a necessity for responsible investors.</p>',
        'category' => 'Sustainable Finance',
        'cover_image_url' => '../assets/img/service/cst/thumb.jpg',
        'status' => 'published'
    ]
];

// Insert dummy news
$inserted = 0;
$failed = 0;

foreach ($dummy_news as $news) {
    $title = escape_string($news['title']);
    $excerpt = escape_string($news['excerpt']);
    $content = escape_string($news['content']);
    $category = escape_string($news['category']);
    $cover_image_url = escape_string($news['cover_image_url']);
    $status = escape_string($news['status']);
    $slug = strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9\s]/', '', $news['title'])));
    
    $query = "INSERT INTO news (title, excerpt, content, category, cover_image_url, status, slug, author, views, featured, created_at, published_at, updated_at) 
              VALUES ('$title', '$excerpt', '$content', '$category', '$cover_image_url', '$status', '$slug', $admin_id, 0, 0, NOW(), NOW(), NOW())";
    
    if ($conn->query($query)) {
        $inserted++;
    } else {
        $failed++;
        error_log("Failed to insert: " . $news['title'] . " - Error: " . $conn->error);
    }
}

// Return JSON response
echo json_encode([
    'success' => true,
    'inserted' => $inserted,
    'failed' => $failed,
    'message' => "$inserted dummy news articles have been added successfully!",
    'redirect_url' => 'news-list.php'
]);
?>


// Get admin user ID (logged-in user)
$admin_result = $conn->query("SELECT id FROM admins LIMIT 1");
$admin = $admin_result->fetch_assoc();
$admin_id = $admin['id'] ?? 1;

// Dummy news data
$dummy_news = [
    [
        'title' => 'Market Analysis: Q1 Financial Performance Report',
        'excerpt' => 'The first quarter of 2026 has shown remarkable growth across all major financial sectors. Our comprehensive analysis reveals emerging opportunities and strategic insights.',
        'content' => '<h2>Executive Summary</h2><p>The first quarter of 2026 has shown remarkable growth across all major financial sectors. Our comprehensive analysis reveals emerging opportunities and strategic insights for investors.</p><h2>Key Findings</h2><ul><li>Growth in technology sector reached 24% YoY</li><li>Financial services showed resilience with 15% expansion</li><li>Real estate investments maintained stability</li><li>Emerging markets demonstrated strong momentum</li></ul><p>These trends indicate a healthy market environment for strategic investors.</p>',
        'category' => 'Market Analysis',
        'cover_image_url' => '../assets/img/service/cst/thumb.jpg',
        'status' => 'published'
    ],
    [
        'title' => 'Expert Interview: Future of Wealth Management',
        'excerpt' => 'Industry leaders discuss the evolving landscape of wealth management and what investors should expect in the coming years.',
        'content' => '<h2>Interview Highlights</h2><p>We sat down with leading wealth management experts to discuss the future of the industry.</p><h2>Key Insights</h2><ul><li>Personalization is becoming increasingly important</li><li>Technology integration is reshaping client relationships</li><li>ESG considerations are influencing investment strategies</li><li>Multi-generational wealth transfer presents new opportunities</li></ul><p>The future of wealth management looks promising with these innovations.</p>',
        'category' => 'Expert Insights',
        'cover_image_url' => '../assets/img/service/cst/thumb.jpg',
        'status' => 'published'
    ],
    [
        'title' => 'Guide: Diversification Strategies for Modern Investors',
        'excerpt' => 'Learn how to build a diversified portfolio that aligns with your investment goals and risk tolerance.',
        'content' => '<h2>Understanding Diversification</h2><p>Diversification is a fundamental strategy for managing investment risk. This guide will walk you through key principles.</p><h2>Diversification Techniques</h2><ul><li>Asset allocation across different classes</li><li>Geographic diversification</li><li>Sector-specific investments</li><li>Time-based diversification strategies</li></ul><p>By following these strategies, you can build a robust investment portfolio.</p>',
        'category' => 'Investment Strategy',
        'cover_image_url' => '../assets/img/service/cst/thumb.jpg',
        'status' => 'published'
    ],
    [
        'title' => 'Business Financing Solutions: What\'s New in 2026',
        'excerpt' => 'Explore the latest financing options available to businesses, from SBA loans to alternative lending sources.',
        'content' => '<h2>2026 Financing Landscape</h2><p>The business financing landscape continues to evolve with new options emerging for entrepreneurs.</p><h2>Available Options</h2><ul><li>Traditional bank loans with improved terms</li><li>SBA loan programs with reduced paperwork</li><li>Equipment financing solutions</li><li>Working capital solutions for growing businesses</li></ul><p>Find the right financing solution for your business needs.</p>',
        'category' => 'Business Financing',
        'cover_image_url' => '../assets/img/service/cst/thumb.jpg',
        'status' => 'published'
    ],
    [
        'title' => 'Sports Investment Trends: Opportunities in 2026',
        'excerpt' => 'Discover the growing opportunities in sports investment and how investors are capitalizing on this expanding market.',
        'content' => '<h2>Sports Investment Growth</h2><p>Sports investment has become increasingly attractive to institutional and individual investors alike.</p><h2>Investment Opportunities</h2><ul><li>Professional sports franchises</li><li>Sports technology companies</li><li>Athlete management and endorsements</li><li>Sports venue development</li></ul><p>The sports investment sector offers unique opportunities for diversified portfolios.</p>',
        'category' => 'Sports Investments',
        'cover_image_url' => '../assets/img/service/cst/thumb.jpg',
        'status' => 'published'
    ],
    [
        'title' => 'Technology Innovation: Reshaping Financial Services',
        'excerpt' => 'How fintech innovations are transforming the way we manage money and invest for the future.',
        'content' => '<h2>FinTech Revolution</h2><p>Technology continues to reshape the financial services industry at an unprecedented pace.</p><h2>Key Innovations</h2><ul><li>Artificial intelligence in portfolio management</li><li>Blockchain technology for transparency</li><li>Mobile-first financial platforms</li><li>Real-time data analytics and insights</li></ul><p>These innovations are making financial services more accessible and efficient.</p>',
        'category' => 'Technology',
        'cover_image_url' => '../assets/img/service/cst/thumb.jpg',
        'status' => 'published'
    ],
    [
        'title' => 'Sustainable Investing: Building Wealth Responsibly',
        'excerpt' => 'Learn how sustainable investment practices can generate returns while making a positive impact on society.',
        'content' => '<h2>Sustainable Investment Approach</h2><p>Sustainable investing aligns financial returns with positive environmental and social impact.</p><h2>Benefits of Sustainable Investing</h2><ul><li>Long-term portfolio resilience</li><li>Reduced exposure to regulatory risks</li><li>Positive environmental and social impact</li><li>Growing investor interest and demand</li></ul><p>Sustainable investing is no longer just an option—it\'s becoming a necessity for responsible investors.</p>',
        'category' => 'Sustainable Finance',
        'cover_image_url' => '../assets/img/service/cst/thumb.jpg',
        'status' => 'published'
    ]
];

// Insert dummy news
$inserted = 0;
$failed = 0;

foreach ($dummy_news as $news) {
    $title = escape_string($news['title']);
    $excerpt = escape_string($news['excerpt']);
    $content = escape_string($news['content']);
    $category = escape_string($news['category']);
    $cover_image_url = escape_string($news['cover_image_url']);
    $status = escape_string($news['status']);
    $slug = strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9\s]/', '', $news['title'])));
    
    $query = "INSERT INTO news (title, excerpt, content, category, cover_image_url, status, slug, author, views, featured, created_at, published_at, updated_at) 
              VALUES ('$title', '$excerpt', '$content', '$category', '$cover_image_url', '$status', '$slug', $admin_id, 0, 0, NOW(), NOW(), NOW())";
    
    if ($conn->query($query)) {
        $inserted++;
    } else {
        $failed++;
        error_log("Failed to insert: " . $news['title'] . " - Error: " . $conn->error);
    }
}


// Return JSON response
echo json_encode([
    'success' => true,
    'inserted' => $inserted,
    'failed' => $failed,
    'message' => "$inserted dummy news articles have been added successfully!",
    'redirect_url' => 'news-list.php'
]);
?>
