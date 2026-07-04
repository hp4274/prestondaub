<?php
/**
 * Enable Team Module Setting
 * Run this once to enable the team module on the frontend
 */

require_once 'config/database.php';

try {
    // Enable team module
    $query = "INSERT INTO settings (setting_key, setting_value, created_at, updated_at) 
              VALUES ('team_module_enabled', '1', NOW(), NOW()) 
              ON DUPLICATE KEY UPDATE setting_value = '1', updated_at = NOW()";
    
    $conn->query($query);
    
    echo '<div style="padding: 40px; background: #f0fdf4; border: 2px solid #22c55e; border-radius: 12px; max-width: 600px; margin: 40px auto;">';
    echo '<h2 style="color: #16a34a; margin-top: 0;">✅ Team Module Enabled</h2>';
    echo '<p style="color: #15803d; font-size: 16px; line-height: 1.6;">The team module has been successfully enabled. Team members added in the admin panel will now appear on the frontend team page.</p>';
    echo '<p style="color: #15803d; font-size: 14px; margin-bottom: 0;"><a href="team.php" style="color: #16a34a; text-decoration: none; font-weight: 600;">View Team Members</a> | <a href="../../about/team.html" style="color: #16a34a; text-decoration: none; font-weight: 600; margin-left: 16px;">View Frontend Team Page</a></p>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div style="padding: 40px; background: #fef2f2; border: 2px solid #ef4444; border-radius: 12px; max-width: 600px; margin: 40px auto;">';
    echo '<h2 style="color: #dc2626; margin-top: 0;">❌ Error</h2>';
    echo '<p style="color: #991b1b; font-size: 16px;">' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
}
?>
