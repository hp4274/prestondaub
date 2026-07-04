<?php
/**
 * Clear Dummy News Articles
 */

require_once 'config/auth.php';
require_login();
require_once 'config/database.php';

header('Content-Type: application/json');

// Delete dummy news articles
$result = $conn->query("DELETE FROM news WHERE title LIKE '%Market%' OR title LIKE '%Expert%' OR title LIKE '%Guide%' OR title LIKE '%Sustainable%' OR title LIKE '%Technology%' OR title LIKE '%Sports%' OR title LIKE '%Business%'");

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Sample articles cleared successfully!'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error clearing articles: ' . $conn->error
    ]);
}
?>
