<?php
/**
 * Auto-migration for Mosaic form data
 * Extracts combined form data from message field into separate columns
 * Runs once per page load for old records
 */

require_once dirname(__DIR__, 3) . '/config/database.php';

// Find all Mosaic forms with combined data in message but empty columns
$result = $conn->query("
    SELECT id, message, job_title, interests, goals_challenges 
    FROM contact_forms 
    WHERE form_type = 'mosaic' 
    AND (job_title = '' OR interests = '' OR goals_challenges = '')
    AND message LIKE '%Job Title:%'
    LIMIT 50
");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $message = $row['message'];
        
        $job_title = $row['job_title'];
        $interests = $row['interests'];
        $goals_challenges = $row['goals_challenges'];
        
        // Extract job_title
        if (empty($job_title) && preg_match('/Job Title:\s*([^\n]+)/', $message, $m)) {
            $job_title = trim($m[1]);
        }
        
        // Extract interests
        if (empty($interests) && preg_match('/Interests:\s*([^\n]+?)(?:\n|$)/', $message, $m)) {
            $interests = trim($m[1]);
        }
        
        // Extract goals_challenges
        if (empty($goals_challenges) && preg_match('/Goals\/Challenges:\s*([^\n]+?)(?:\n|$)/', $message, $m)) {
            $goals_challenges = trim($m[1]);
        }
        
        // Update database
        $job_title_esc = $conn->real_escape_string($job_title);
        $interests_esc = $conn->real_escape_string($interests);
        $goals_esc = $conn->real_escape_string($goals_challenges);
        
        $conn->query("
            UPDATE contact_forms 
            SET job_title = '$job_title_esc',
                interests = '$interests_esc',
                goals_challenges = '$goals_esc',
                message = ''
            WHERE id = $id
        ");
    }
}

// Close connection
$conn->close();
?>
