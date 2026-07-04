<?php
/**
 * Image Upload Handler for News Articles
 * Handles uploading and validating images for news articles
 */

/**
 * Upload image file
 * 
 * @param array $file $_FILES array for the image
 * @param string $field_name Name of the form field
 * @return array ['success' => bool, 'url' => string, 'error' => string]
 */
function upload_news_image($file, $field_name = 'image') {
    $result = ['success' => false, 'url' => '', 'error' => ''];
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return $result;
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds maximum upload size',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds maximum form size',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary directory',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload was stopped by extension'
        ];
        $result['error'] = $errors[$file['error']] ?? 'Unknown upload error';
        return $result;
    }
    
    // Validate file size (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        $result['error'] = 'File size exceeds 5MB limit';
        return $result;
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        $result['error'] = 'Invalid file type. Only JPG, PNG, WebP, and GIF are allowed';
        return $result;
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = dirname(__DIR__, 4) . '/assets/uploads/news';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            $result['error'] = 'Failed to create uploads directory';
            return $result;
        }
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('news_') . '_' . time() . '.' . $ext;
    $file_path = $upload_dir . '/' . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        $result['error'] = 'Failed to move uploaded file';
        return $result;
    }
    
    // Set proper permissions
    chmod($file_path, 0644);
    
    // Return relative URL path
    $result['success'] = true;
    $result['url'] = '/assets/uploads/news/' . $filename;
    
    return $result;
}

/**
 * Delete image file
 * 
 * @param string $image_url Relative URL of the image
 * @return bool
 */
function delete_news_image($image_url) {
    if (empty($image_url)) {
        return true;
    }
    
    // Convert URL to file path
    $file_path = dirname(__DIR__, 4) . '/' . ltrim($image_url, '/');
    
    if (file_exists($file_path) && strpos(realpath($file_path), realpath(dirname(__DIR__, 4) . '/assets/uploads/news')) === 0) {
        return unlink($file_path);
    }
    
    return false;
}

/**
 * Validate image URL
 * 
 * @param string $image_url URL to validate
 * @return bool
 */
function is_valid_news_image_url($image_url) {
    if (empty($image_url)) {
        return false;
    }
    
    $file_path = dirname(__DIR__, 4) . '/' . ltrim($image_url, '/');
    
    if (!file_exists($file_path)) {
        return false;
    }
    
    // Ensure file is in the news uploads directory
    $real_path = realpath($file_path);
    $upload_dir = realpath(dirname(__DIR__, 4) . '/assets/uploads/news');
    
    return $real_path && strpos($real_path, $upload_dir) === 0;
}
?>
