<?php
/**
 * Database Column Check & Auto-Migration
 * This ensures required columns exist for Mosaic forms and News articles
 * Automatically adds missing columns without disrupting existing data
 */

// Create news table if it doesn't exist
$news_table_check = $conn->query("SHOW TABLES LIKE 'news'");
if ($news_table_check && $news_table_check->num_rows === 0) {
    $create_news_table = "CREATE TABLE IF NOT EXISTS news (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(500) NOT NULL,
        slug VARCHAR(500) UNIQUE NOT NULL,
        excerpt VARCHAR(500),
        content LONGTEXT NOT NULL,
        image_url VARCHAR(500),
        cover_image_url VARCHAR(500) COMMENT 'Cover image for blog listing and article header',
        content_image_url VARCHAR(500) COMMENT 'Content image displayed inside article body',
        category VARCHAR(100),
        author INT,
        featured BOOLEAN DEFAULT FALSE,
        status VARCHAR(50) DEFAULT 'draft',
        views INT DEFAULT 0,
        published_at DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (status),
        INDEX (featured),
        INDEX (published_at),
        INDEX (slug)
    )";
    $conn->query($create_news_table);
}

// Check if columns exist and add them if needed for contact_forms
$contact_forms_columns = [
    'organization' => "ALTER TABLE contact_forms ADD COLUMN organization VARCHAR(255) COMMENT 'For Mosaic form' AFTER company",
    'organization_type' => "ALTER TABLE contact_forms ADD COLUMN organization_type VARCHAR(100) COMMENT 'For Mosaic form' AFTER organization",
    'job_title' => "ALTER TABLE contact_forms ADD COLUMN job_title VARCHAR(255) AFTER service",
    'interests' => "ALTER TABLE contact_forms ADD COLUMN interests LONGTEXT AFTER job_title",
    'goals_challenges' => "ALTER TABLE contact_forms ADD COLUMN goals_challenges LONGTEXT AFTER interests"
];

foreach ($contact_forms_columns as $column_name => $add_query) {
    $check_column = $conn->query("SHOW COLUMNS FROM contact_forms LIKE '$column_name'");
    if ($check_column && $check_column->num_rows === 0) {
        // Column doesn't exist, add it
        if ($conn->query($add_query) === false) {
            // Silently fail - column might already exist or there might be a permission issue
            // This won't disrupt the application
        }
    }
}

// Check if columns exist and add them if needed for news articles
$news_columns = [
    'cover_image_url' => "ALTER TABLE news ADD COLUMN cover_image_url VARCHAR(500) COMMENT 'Cover image for blog listing and article header' AFTER image_url",
    'content_image_url' => "ALTER TABLE news ADD COLUMN content_image_url VARCHAR(500) COMMENT 'Content image displayed inside article body' AFTER cover_image_url"
];

foreach ($news_columns as $column_name => $add_query) {
    $check_column = $conn->query("SHOW COLUMNS FROM news LIKE '$column_name'");
    if ($check_column && $check_column->num_rows === 0) {
        // Column doesn't exist, add it
        if ($conn->query($add_query) === false) {
            // Silently fail - column might already exist or there might be a permission issue
            // This won't disrupt the application
        }
    }
}

// Create team members table if it doesn't exist
$team_table_check = $conn->query("SHOW TABLES LIKE 'team_members'");
if ($team_table_check && $team_table_check->num_rows === 0) {
    $create_team_table = "CREATE TABLE IF NOT EXISTS team_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        designation VARCHAR(255) NOT NULL,
        short_bio TEXT,
        photo VARCHAR(500),
        linkedin_url VARCHAR(500),
        display_order INT,
        status BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (status),
        INDEX (display_order)
    )";
    $conn->query($create_team_table);
}

// Create settings table for global controls (team visibility, etc.)
$settings_table_check = $conn->query("SHOW TABLES LIKE 'settings'");
if ($settings_table_check && $settings_table_check->num_rows === 0) {
    $create_settings_table = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(255) UNIQUE NOT NULL,
        setting_value LONGTEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (setting_key)
    )";
    $conn->query($create_settings_table);
    
    // Insert default settings
    $check_team_enabled = $conn->query("SELECT * FROM settings WHERE setting_key = 'team_module_enabled'");
    if ($check_team_enabled->num_rows === 0) {
        $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('team_module_enabled', '1')");
    }
}

// Check if team_members table exists and add columns if needed
if ($team_table_check && $team_table_check->num_rows > 0) {
    // Table exists, no additional columns needed
}
?>
