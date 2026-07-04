<?php
/**
 * Priority Badge Helper
 * Centralized logic for displaying priority badges across all form pages
 */

/**
 * Render priority badge based on form status and priority value
 * 
 * @param string $status The form status (new, read, archived, spam)
 * @param string|null $priority The priority level (high, medium, low, or null)
 * @return string HTML for the priority badge
 */
function renderPriorityBadge($status, $priority = null) {
    // NEW/UNREAD forms: Show "Pending Review"
    if ($status === 'new') {
        return '<span class="badge" style="padding: 6px 12px; border-radius: 6px; font-weight: 600; background: #F3F4F6; color: #9CA3AF;">Pending Review</span>';
    }
    
    // READ/ARCHIVED forms: Show priority if set
    if (!empty($priority)) {
        $bgColor = $priority === 'high' ? '#FEE2E2' : ($priority === 'medium' ? '#FEF08A' : '#D1FAE5');
        $textColor = $priority === 'high' ? '#DC2626' : ($priority === 'medium' ? '#92400E' : '#065F46');
        $label = $priority === 'high' ? '↑ High' : ($priority === 'medium' ? '- Mid' : '↓ Low');
        
        return sprintf(
            '<span class="badge" style="padding: 6px 12px; border-radius: 6px; font-weight: 600; background: %s; color: %s;">%s</span>',
            $bgColor,
            $textColor,
            $label
        );
    }
    
    // No priority set
    return '<span class="badge" style="padding: 6px 12px; border-radius: 6px; font-weight: 600; background: #f3f4f6; color: #9ca3af;">Not Set</span>';
}

/**
 * Render status badge
 * 
 * @param string $status The form status (new, read, archived, spam)
 * @return string HTML for the status badge
 */
function renderStatusBadge($status) {
    $status = strtolower(trim($status));
    
    $bgColor = $status === 'new' ? '#FEF3C7' : (
        $status === 'read' ? '#DBEAFE' : (
        $status === 'archived' ? '#D1FAE5' : '#FEE2E2'
    ));
    
    $textColor = $status === 'new' ? '#92400E' : (
        $status === 'read' ? '#1E40AF' : (
        $status === 'archived' ? '#065F46' : '#7F1D1D'
    ));
    
    return sprintf(
        '<span class="badge" style="padding: 6px 12px; border-radius: 6px; font-weight: 600; background: %s; color: %s;">%s</span>',
        $bgColor,
        $textColor,
        ucfirst($status)
    );
}

/**
 * Get avatar color based on first letter
 * 
 * @param string $name The person's name
 * @return string Hex color code
 */
function getAvatarColor($name) {
    $colors = array(
        'A' => '#FFB3BA', 'B' => '#BAE7E7', 'C' => '#A8D8E8', 'D' => '#FFD1B3', 'E' => '#C8E6DD',
        'F' => '#FFED99', 'G' => '#E0B8F0', 'H' => '#C9E4F5', 'I' => '#FFDAB3', 'J' => '#A8DCC8',
        'K' => '#FFB3D9', 'L' => '#B3D9F2', 'M' => '#FFD699', 'N' => '#A8D99B', 'O' => '#E8B3E0',
        'P' => '#D1A8F0', 'Q' => '#99CCFF', 'R' => '#FFB399', 'S' => '#FFFF99', 'T' => '#99F0FF',
        'U' => '#D9B3FF', 'V' => '#FF99D1', 'W' => '#99D4B8', 'X' => '#FF9999', 'Y' => '#FFE8B3',
        'Z' => '#99CCFF'
    );
    
    $firstName = explode(' ', trim($name))[0];
    $initial = strtoupper(substr($firstName, 0, 1));
    
    return isset($colors[$initial]) ? $colors[$initial] : '#B3D9F2';
}

/**
 * Get avatar initials from name
 * 
 * @param string $name The person's name
 * @return string First letter in uppercase
 */
function getAvatarInitials($name) {
    $firstName = explode(' ', trim($name))[0];
    return strtoupper(substr($firstName, 0, 1));
}

/**
 * Render avatar circle with initials
 * 
 * @param string $name The person's name
 * @return string HTML for the avatar
 */
function renderAvatar($name) {
    $color = getAvatarColor($name);
    $initials = getAvatarInitials($name);
    
    return sprintf(
        '<div style="width: 40px; height: 40px; border-radius: 50%; background: %s; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; color: #333; margin: 0 auto;">%s</div>',
        $color,
        $initials
    );
}
?>
