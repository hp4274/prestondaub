<?php
/**
 * Admin Panel Helpers
 * Common functions shared across all form pages
 */

/**
 * Get statistics for a specific form type
 * @param object $conn Database connection
 * @param string $filter SQL WHERE condition for form type
 * @return array Statistics with keys: total, new, read, spam
 */
function getFormStats($conn, $filter) {
    try {
        $total = $conn->query("SELECT COUNT(*) as count FROM contact_forms WHERE $filter")->fetch_assoc()['count'] ?? 0;
        $new = $conn->query("SELECT COUNT(*) as count FROM contact_forms WHERE $filter AND status = 'new'")->fetch_assoc()['count'] ?? 0;
        $read = $conn->query("SELECT COUNT(*) as count FROM contact_forms WHERE $filter AND status = 'read'")->fetch_assoc()['count'] ?? 0;
        $spam = $conn->query("SELECT COUNT(*) as count FROM contact_forms WHERE $filter AND status = 'spam'")->fetch_assoc()['count'] ?? 0;
        
        return [
            'total' => intval($total),
            'new' => intval($new),
            'read' => intval($read),
            'spam' => intval($spam)
        ];
    } catch (Exception $e) {
        error_log("Error in getFormStats: " . $e->getMessage());
        return ['total' => 0, 'new' => 0, 'read' => 0, 'spam' => 0];
    }
}

/**
 * Get category statistics with consolidation
 * Runs a single query to get counts for multiple categories
 * @param object $conn Database connection
 * @param string $field Field to GROUP BY (e.g., 'service')
 * @param string $filter Base SQL filter (e.g., "form_type LIKE '%sports%'")
 * @return array Associative array with category counts
 */
function getCategoryStats($conn, $field, $filter) {
    try {
        $query = "SELECT $field,
                  COUNT(*) as total,
                  SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new,
                  SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read,
                  SUM(CASE WHEN status = 'spam' THEN 1 ELSE 0 END) as spam
                  FROM contact_forms 
                  WHERE $filter
                  GROUP BY $field";
        
        $result = $conn->query($query);
        $stats = [];
        
        while ($row = $result->fetch_assoc()) {
            $key = strtolower($row[$field]);
            $stats[$key] = [
                'total' => intval($row['total'] ?? 0),
                'new' => intval($row['new'] ?? 0),
                'read' => intval($row['read'] ?? 0),
                'spam' => intval($row['spam'] ?? 0)
            ];
        }
        
        return $stats;
    } catch (Exception $e) {
        error_log("Error in getCategoryStats: " . $e->getMessage());
        return [];
    }
}

/**
 * Update form status
 * @param object $conn Database connection
 * @param int $id Form ID
 * @param string $status New status
 * @return bool Success
 */
function updateFormStatus($conn, $id, $status) {
    try {
        $id = intval($id);
        $status = $conn->real_escape_string($status);
        $result = $conn->query("UPDATE contact_forms SET status = '$status', updated_at = NOW() WHERE id = $id");
        return $result === true;
    } catch (Exception $e) {
        error_log("Error in updateFormStatus: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete form by ID
 * @param object $conn Database connection
 * @param int $id Form ID
 * @return bool Success
 */
function deleteForm($conn, $id) {
    try {
        $id = intval($id);
        $result = $conn->query("DELETE FROM contact_forms WHERE id = $id");
        return $result === true;
    } catch (Exception $e) {
        error_log("Error in deleteForm: " . $e->getMessage());
        return false;
    }
}

/**
 * Toggle form spam status
 * @param object $conn Database connection
 * @param int $id Form ID
 * @return string|null New status, or null on error
 */
function toggleSpamStatus($conn, $id) {
    try {
        $id = intval($id);
        
        // Get current status
        $result = $conn->query("SELECT status FROM contact_forms WHERE id = $id");
        if (!$result || $result->num_rows === 0) {
            return null;
        }
        
        $row = $result->fetch_assoc();
        $current_status = $row['status'];
        
        // Toggle spam
        $new_status = ($current_status === 'spam') ? 'read' : 'spam';
        
        $update_result = $conn->query("UPDATE contact_forms SET status = '$new_status', updated_at = NOW() WHERE id = $id");
        return $update_result ? $new_status : null;
    } catch (Exception $e) {
        error_log("Error in toggleSpamStatus: " . $e->getMessage());
        return null;
    }
}

/**
 * Mark form as read
 * @param object $conn Database connection
 * @param int $id Form ID
 * @return bool Success
 */
function markFormAsRead($conn, $id) {
    return updateFormStatus($conn, $id, 'read');
}

/**
 * Build WHERE clause from search and filter parameters
 * @param object $conn Database connection
 * @param array $base_conditions Base WHERE conditions
 * @param string $search Search term (optional)
 * @param string $status Status filter (optional)
 * @param array $search_fields Fields to search in
 * @return string WHERE clause
 */
function buildWhereClause($conn, $base_conditions, $search = '', $status = '', $search_fields = ['name', 'email']) {
    $conditions = array_merge($base_conditions, []);
    
    if ($status) {
        $status_escaped = $conn->real_escape_string($status);
        $conditions[] = "status = '$status_escaped'";
    }
    
    if ($search) {
        $search_escaped = $conn->real_escape_string($search);
        $search_conditions = [];
        foreach ($search_fields as $field) {
            $search_conditions[] = "$field LIKE '%$search_escaped%'";
        }
        $conditions[] = "(" . implode(" OR ", $search_conditions) . ")";
    }
    
    return implode(' AND ', $conditions);
}

/**
 * Format date for display
 * @param string $datetime DateTime string from database
 * @param bool $include_time Include time component
 * @return string Formatted date
 */
function formatDate($datetime, $include_time = true) {
    try {
        $date = new DateTime($datetime);
        if ($include_time) {
            return $date->format('M d, Y<br/><small style="font-size: 12px; color: #9ca3af;">H:i</small>');
        }
        return $date->format('M d, Y');
    } catch (Exception $e) {
        return $datetime;
    }
}

/**
 * Validate form ID
 * @param int|string $id Form ID
 * @return int|null Validated ID or null
 */
function validateFormId($id) {
    $id = intval($id);
    return ($id > 0) ? $id : null;
}

/**
 * Get safe string for database query
 * @param object $conn Database connection
 * @param string $string String to escape
 * @return string Escaped string
 */
function safeString($conn, $string) {
    return $conn->real_escape_string(trim($string));
}

/**
 * Log admin action for audit trail
 * @param object $conn Database connection
 * @param int $admin_id Admin user ID
 * @param string $action Action performed
 * @param int $form_id Form ID affected
 * @param array $details Additional details (optional)
 * @return bool Success
 */
function logAdminAction($conn, $admin_id, $action, $form_id, $details = []) {
    try {
        $admin_id = intval($admin_id);
        $form_id = intval($form_id);
        $action = $conn->real_escape_string($action);
        $details_json = json_encode($details);
        $details_json = $conn->real_escape_string($details_json);
        
        $conn->query("INSERT INTO admin_logs (admin_id, action, form_id, details, created_at) 
                      VALUES ($admin_id, '$action', $form_id, '$details_json', NOW())");
        
        return true;
    } catch (Exception $e) {
        error_log("Error in logAdminAction: " . $e->getMessage());
        return false;
    }
}

?>
