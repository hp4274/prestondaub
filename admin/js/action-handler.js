/**
 * Action Handler - AJAX-based actions for form submissions
 * Handles View, Read/Unread, Spam/Unspam, and Delete with real-time UI updates
 */

class FormActionHandler {
    constructor() {
        this.isProcessing = false;
        this.init();
    }

    init() {
        // Delegate action handlers
        document.addEventListener('click', (e) => this.handleActionClick(e));
    }

    /**
     * Handle action button clicks
     */
    handleActionClick(e) {
        // Skip if click is inside the form modal sidebar (modal has its own handlers)
        if (e.target.closest('#formModalSidebar')) {
            return;
        }

        // View button - opens modal, no AJAX needed
        if (e.target.closest('[data-action="view"]')) {
            const formId = e.target.closest('[data-action="view"]').getAttribute('data-form-id');
            if (typeof openFormModal === 'function') {
                openFormModal(formId);
            }
            return;
        }

        // Status action buttons (read, spam, delete)
        const actionBtn = e.target.closest('[data-action]');
        if (actionBtn && !this.isProcessing) {
            e.preventDefault();
            e.stopPropagation(); // Prevent onclick handler from firing
            
            const action = actionBtn.getAttribute('data-action');
            const formId = actionBtn.getAttribute('data-form-id');
            
            // For delete action, show custom confirmation modal first
            if (action === 'delete') {
                this.handleDeleteWithConfirmation(formId, actionBtn);
            } else {
                this.executeAction(action, formId, actionBtn);
            }
        }
    }

    /**
     * Handle delete action with custom confirmation modal
     * IMPORTANT: Only proceed with deletion after user explicitly confirms
     */
    async handleDeleteWithConfirmation(formId, button) {
        console.log('[DELETE] Delete button clicked for form ID:', formId);
        
        // Show custom confirmation modal instead of browser confirm()
        const confirmed = await this.showDeleteConfirmation();
        
        if (!confirmed) {
            console.log('[DELETE] User cancelled delete confirmation for form ID:', formId);
            return; // Stop here - do NOT delete
        }
        
        console.log('[DELETE] User confirmed delete for form ID:', formId);
        
        // Proceed with delete action
        this.executeAction('delete', formId, button);
    }

    /**
     * Show custom delete confirmation modal
     * Returns Promise that resolves to true if user confirms, false if cancels
     */
    showDeleteConfirmation() {
        return new Promise((resolve) => {
            // Use unified confirmation modal system
            if (typeof showConfirmation === 'function') {
                showConfirmation({
                    title: 'Delete Confirmation',
                    message: 'Are you sure you want to permanently delete this submission? This action cannot be undone.',
                    confirmText: 'Delete Permanently',
                    cancelText: 'Cancel',
                    type: 'danger'
                }).then((confirmed) => {
                    resolve(confirmed);
                });
            } else {
                // Fallback to simple confirm if modal not available (shouldn't happen)
                console.warn('[DELETE] Confirmation modal not available, falling back to browser confirm');
                resolve(window.confirm('Are you sure you want to delete this submission? This action cannot be undone.'));
            }
        });
    }

    /**
     * Execute action via AJAX
     */
    async executeAction(action, formId, button) {
        if (this.isProcessing) return;
        
        this.isProcessing = true;
        
        // Show loading state on button
        const originalContent = button.innerHTML;
        button.innerHTML = '<span class="material-symbols-rounded" style="animation: spin 0.8s linear infinite;">hourglass_empty</span>';
        button.style.opacity = '0.6';
        
        try {
            // Get the row
            const row = document.querySelector(`tr[data-form-id="${formId}"]`);
            if (!row) throw new Error('Row not found');

            const currentStatus = row.getAttribute('data-status');

            // Determine next status based on action
            let newStatus;
            if (action === 'toggle-read') {
                // Toggle between 'new' and 'read'
                newStatus = currentStatus === 'read' ? 'new' : 'read';
            } else if (action === 'toggle-spam') {
                // Toggle between 'spam' and 'read'
                newStatus = currentStatus === 'spam' ? 'read' : 'spam';
            } else if (action === 'delete') {
                newStatus = null; // Will be deleted
            } else {
                throw new Error(`Unknown action: ${action}`);
            }

            // Send to server
            const formData = new FormData();
            formData.append('form_id', formId);
            if (newStatus !== null) {
                formData.append('action', action);
                formData.append('new_status', newStatus);
            } else {
                formData.append('action', 'delete');
            }

            const response = await fetch('/api/admin/forms/actions', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`Server error: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                if (action === 'delete') {
                    // Fade out and remove row
                    this.removeRowWithAnimation(row, formId);
                    if (typeof showNotification === 'function') {
                        showNotification('Record deleted successfully', 'success');
                    }
                } else {
                    // Update row status and UI
                    row.setAttribute('data-status', newStatus);
                    this.updateRowUI(row, newStatus);
                    this.updateActionButtons(row, newStatus);
                    
                    if (action === 'toggle-read') {
                        if (typeof showNotification === 'function') {
                            showNotification(newStatus === 'read' ? 'Marked as read' : 'Marked as new', 'success');
                        }
                    } else if (action === 'toggle-spam') {
                        if (typeof showNotification === 'function') {
                            showNotification(newStatus === 'spam' ? 'Marked as spam' : 'Marked as normal', 'success');
                        }
                    }
                    
                    // Restore button opacity after update (updateActionButtons handles the content/icon)
                    button.style.opacity = '1';
                }

                // Refresh statistics
                this.refreshStats();
            } else {
                throw new Error(data.error || 'Action failed');
            }
        } catch (error) {
            console.error('Action error:', error);
            if (typeof showNotification === 'function') {
                showNotification('Error: ' + error.message, 'error');
            }
            // Restore button
            button.innerHTML = originalContent;
            button.style.opacity = '1';
        } finally {
            this.isProcessing = false;
        }
    }

    /**
     * Update row styling based on status
     */
    updateRowUI(row, status) {
        // Remove old styling
        row.style.backgroundColor = '';
        row.style.borderLeft = '';

        // Apply new styling
        if (status === 'spam') {
            row.style.backgroundColor = '#FEE2E2';
            row.style.borderLeft = '4px solid #EF4444';
        } else if (status === 'new') {
            row.style.backgroundColor = '#DBEAFE';
            row.style.borderLeft = '';
        } else if (status === 'read') {
            row.style.backgroundColor = '';
            row.style.borderLeft = '';
        }
    }

    /**
     * Update action buttons for the row based on new status
     */
    updateActionButtons(row, newStatus) {
        const readBtn = row.querySelector('[data-action="toggle-read"]');
        const spamBtn = row.querySelector('[data-action="toggle-spam"]');

        if (readBtn) {
            if (newStatus === 'spam') {
                // Can't mark spam as read - disable
                readBtn.style.opacity = '0.5';
                readBtn.style.pointerEvents = 'none';
                readBtn.style.cursor = 'not-allowed';
                readBtn.style.color = '#6B7280 !important';
            } else {
                // Can toggle read
                readBtn.style.opacity = '1';
                readBtn.style.pointerEvents = 'auto';
                readBtn.style.cursor = 'pointer';
                
                // Update button appearance
                if (newStatus === 'read') {
                    readBtn.innerHTML = '<span class="material-symbols-rounded" style="font-size: 18px;">done_all</span>';
                    // Use setAttribute for more reliable style override
                    readBtn.setAttribute('style', 'background: white; border: 1px solid #e5e7eb; color: #10B981; border-radius: 6px; padding: 6px 10px; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s; cursor: pointer; opacity: 1; pointer-events: auto;');
                    readBtn.title = 'Mark as unread';
                } else {
                    readBtn.innerHTML = '<span class="material-symbols-rounded" style="font-size: 18px;">done</span>';
                    // Use setAttribute for more reliable style override
                    readBtn.setAttribute('style', 'background: white; border: 1px solid #e5e7eb; color: #6B7280; border-radius: 6px; padding: 6px 10px; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s; cursor: pointer; opacity: 1; pointer-events: auto;');
                    readBtn.title = 'Mark as read';
                }
            }
        }

        if (spamBtn) {
            // Update spam button
            if (newStatus === 'spam') {
                spamBtn.innerHTML = '<span class="material-symbols-rounded" style="font-size: 18px;">undo</span>';
                spamBtn.setAttribute('style', 'background: white; border: 1px solid #e5e7eb; color: #10B981; border-radius: 6px; padding: 6px 10px; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s; cursor: pointer;');
                spamBtn.title = 'Mark as normal';
            } else {
                spamBtn.innerHTML = '<span class="material-symbols-rounded" style="font-size: 18px;">flag</span>';
                spamBtn.setAttribute('style', 'background: white; border: 1px solid #e5e7eb; color: #EF4444; border-radius: 6px; padding: 6px 10px; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s; cursor: pointer;');
                spamBtn.title = 'Mark as spam';
            }
        }
    }

    /**
     * Remove row with fade animation
     */
    removeRowWithAnimation(row, formId) {
        row.style.transition = 'opacity 0.3s ease, background-color 0.3s ease';
        row.style.opacity = '0';
        row.style.backgroundColor = '#FEE2E2';
        
        setTimeout(() => {
            row.remove();
            // Update stats if needed
            this.refreshStats();
        }, 300);
    }

    /**
     * Refresh statistics
     */
    refreshStats() {
        const params = new URLSearchParams(window.location.search);
        fetch(`?api=stats${params.toString() ? '&' + params.toString() : ''}`)
            .then(response => response.json())
            .then(data => {
                const statTotal = document.getElementById('stat-total');
                const statNew = document.getElementById('stat-new');
                const statRead = document.getElementById('stat-read');
                const statSpam = document.getElementById('stat-spam');

                if (statTotal) statTotal.textContent = data.total;
                if (statNew) statNew.textContent = data.new;
                if (statRead) statRead.textContent = data.read;
                if (statSpam) statSpam.textContent = data.spam;
            })
            .catch(error => console.error('Error refreshing stats:', error));
    }
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new FormActionHandler();
    });
} else {
    new FormActionHandler();
}
