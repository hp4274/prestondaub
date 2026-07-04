// Unified Notification System
// Automatically initializes on page load and provides showNotification() function

(function() {
    'use strict';

    // Create container if it doesn't exist
    function ensureContainer() {
        let container = document.getElementById('notifications-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notifications-container';
            container.className = 'notifications-container';
            document.body.appendChild(container);
        }
        return container;
    }

    // Show notification
    window.showNotification = function(message, type = 'info', title = null) {
        const container = ensureContainer();

        // Determine title based on type if not provided
        if (!title) {
            const titles = {
                success: 'Success',
                error: 'Error',
                info: 'Info',
                warning: 'Warning'
            };
            title = titles[type] || 'Notification';
        }

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.setAttribute('role', 'alert');

        // Determine icon based on type
        const icons = {
            success: 'check_circle',
            error: 'error',
            info: 'info',
            warning: 'warning'
        };
        const icon = icons[type] || 'info';

        // Build HTML
        notification.innerHTML = `
            <div class="notification-icon">
                <span class="material-symbols-rounded">${icon}</span>
            </div>
            <div class="notification-content">
                ${title ? `<div class="notification-title">${escapeHtml(title)}</div>` : ''}
                <div class="notification-message">${escapeHtml(message)}</div>
            </div>
            <button type="button" class="notification-close" aria-label="Close notification">
                <span class="material-symbols-rounded">close</span>
            </button>
        `;

        // Add to container
        container.appendChild(notification);

        // Close button handler
        const closeBtn = notification.querySelector('.notification-close');
        const removeNotification = () => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 300);
        };
        closeBtn.addEventListener('click', removeNotification);

        // Auto-dismiss after 3500ms (3.5 seconds)
        const dismissTimeout = setTimeout(() => {
            removeNotification();
        }, 3500);

        // Clear timeout if manually closed
        notification.addEventListener('click', () => {
            clearTimeout(dismissTimeout);
        }, true);

        return notification;
    };

    // Helper function to escape HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Auto-detect and show notifications from URL parameters on page load
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);

        // Check for notification triggers
        if (urlParams.has('added') || urlParams.has('added_success')) {
            showNotification('Item added successfully!', 'success', 'Success');
        }

        if (urlParams.has('updated') || urlParams.has('updated_success')) {
            showNotification('Item updated successfully!', 'success', 'Success');
        }

        if (urlParams.has('deleted') || urlParams.has('deleted_success')) {
            showNotification('Item deleted successfully!', 'success', 'Success');
        }

        // NOTE: 'status_updated' is handled by individual pages (e.g., team.php)
        // Don't auto-detect it here to avoid duplicate notifications

        if (urlParams.has('action_done')) {
            showNotification('Action completed successfully!', 'success', 'Success');
        }

        if (urlParams.has('error')) {
            const errorMsg = urlParams.get('error');
            showNotification(decodeURIComponent(errorMsg), 'error', 'Error');
        }

        // Clean up URL bar by removing notification parameters
        if (urlParams.has('added') || urlParams.has('updated') || 
            urlParams.has('deleted') || urlParams.has('status_updated') || 
            urlParams.has('action_done') || urlParams.has('error')) {
            
            // Remove notification params from URL
            const newParams = new URLSearchParams();
            for (const [key, value] of urlParams) {
                if (!['added', 'updated', 'deleted', 'status_updated', 'action_done', 'error', 'added_success', 'updated_success', 'deleted_success'].includes(key)) {
                    newParams.set(key, value);
                }
            }
            const newUrl = newParams.toString() ? `${window.location.pathname}?${newParams.toString()}` : window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
    });

})();
