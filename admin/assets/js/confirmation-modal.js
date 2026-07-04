/**
 * Unified Confirmation Modal System
 * Replaces all browser confirm() popups with a beautiful, theme-based modal
 * Works for: Delete, Status Change, Spam Toggle, and any other confirmations
 */

(function() {
    'use strict';

    // Create modal container if it doesn't exist
    function ensureModalContainer() {
        let container = document.getElementById('confirmation-modal-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'confirmation-modal-container';
            document.body.appendChild(container);
        }
        return container;
    }

    // Show confirmation modal
    window.showConfirmation = function(options = {}) {
        return new Promise((resolve) => {
            const {
                title = 'Confirm Action',
                message = 'Are you sure?',
                confirmText = 'Confirm',
                cancelText = 'Cancel',
                type = 'danger' // 'danger', 'warning', 'info'
            } = options;

            const container = ensureModalContainer();

            // Create modal structure
            const modalHTML = `
                <div class="confirmation-modal" role="alertdialog" aria-modal="true">
                    <div class="confirmation-overlay"></div>
                    <div class="confirmation-content">
                        <div class="confirmation-header confirmation-header-${type}">
                            <div class="confirmation-icon-wrapper">
                                <span class="material-symbols-rounded confirmation-icon">
                                    ${type === 'danger' ? 'error' : (type === 'warning' ? 'warning' : 'help')}
                                </span>
                            </div>
                            <h2 class="confirmation-title">${escapeHtml(title)}</h2>
                            <button class="confirmation-close-btn" aria-label="Close">
                                <span class="material-symbols-rounded">close</span>
                            </button>
                        </div>
                        
                        <div class="confirmation-body">
                            <p class="confirmation-message">${escapeHtml(message)}</p>
                        </div>
                        
                        <div class="confirmation-footer">
                            <button class="confirmation-btn confirmation-btn-cancel">
                                ${escapeHtml(cancelText)}
                            </button>
                            <button class="confirmation-btn confirmation-btn-confirm confirmation-btn-${type}">
                                ${escapeHtml(confirmText)}
                            </button>
                        </div>
                    </div>
                </div>
            `;

            const modalElement = document.createElement('div');
            modalElement.innerHTML = modalHTML;
            const modal = modalElement.firstElementChild;
            
            container.appendChild(modal);

            // Trigger animation
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);

            // Get button references
            const overlay = modal.querySelector('.confirmation-overlay');
            const cancelBtn = modal.querySelector('.confirmation-btn-cancel');
            const confirmBtn = modal.querySelector('.confirmation-btn-confirm');
            const closeBtn = modal.querySelector('.confirmation-close-btn');

            // Cleanup function
            const cleanup = () => {
                modal.classList.remove('active');
                setTimeout(() => {
                    modal.remove();
                }, 300);
            };

            // Event handlers
            const handleCancel = () => {
                cleanup();
                resolve(false);
            };

            const handleConfirm = () => {
                cleanup();
                resolve(true);
            };

            const handleEscape = (e) => {
                if (e.key === 'Escape') {
                    document.removeEventListener('keydown', handleEscape);
                    handleCancel();
                }
            };

            // Attach listeners
            cancelBtn.addEventListener('click', handleCancel);
            confirmBtn.addEventListener('click', handleConfirm);
            closeBtn.addEventListener('click', handleCancel);
            overlay.addEventListener('click', handleCancel);
            document.addEventListener('keydown', handleEscape);

            // Set focus on confirm button
            confirmBtn.focus();
        });
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

    // Add CSS styles
    function injectStyles() {
        const styleId = 'confirmation-modal-styles';
        if (document.getElementById(styleId)) return;

        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            /* Confirmation Modal Container */
            #confirmation-modal-container {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 10000;
                pointer-events: none;
            }

            /* Modal Wrapper */
            .confirmation-modal {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                pointer-events: auto;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            }

            /* Overlay */
            .confirmation-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0);
                transition: background 0.3s ease;
                cursor: pointer;
            }

            .confirmation-modal.active .confirmation-overlay {
                background: rgba(0, 0, 0, 0.5);
            }

            /* Modal Content */
            .confirmation-content {
                position: relative;
                background: white;
                border-radius: 16px;
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
                width: 90%;
                max-width: 420px;
                overflow: hidden;
                transform: scale(0.9) translateY(30px);
                opacity: 0;
                transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            }

            .confirmation-modal.active .confirmation-content {
                transform: scale(1) translateY(0);
                opacity: 1;
            }

            /* Header */
            .confirmation-header {
                display: flex;
                align-items: flex-start;
                gap: 16px;
                padding: 24px;
                position: relative;
            }

            .confirmation-header-danger {
                background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
                border-bottom: 1px solid #fecaca;
            }

            .confirmation-header-warning {
                background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
                border-bottom: 1px solid #fde68a;
            }

            .confirmation-header-info {
                background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
                border-bottom: 1px solid #bfdbfe;
            }

            /* Icon */
            .confirmation-icon-wrapper {
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .confirmation-icon {
                font-size: 32px;
                font-weight: 500;
            }

            .confirmation-header-danger .confirmation-icon {
                color: #dc2626;
            }

            .confirmation-header-warning .confirmation-icon {
                color: #d97706;
            }

            .confirmation-header-info .confirmation-icon {
                color: #2563eb;
            }

            /* Title */
            .confirmation-title {
                margin: 0;
                font-size: 20px;
                font-weight: 700;
                color: #1f2937;
                flex: 1;
            }

            /* Close Button */
            .confirmation-close-btn {
                background: none;
                border: none;
                padding: 4px;
                cursor: pointer;
                color: #6b7280;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 6px;
                transition: all 0.2s;
                font-size: 20px;
            }

            .confirmation-close-btn:hover {
                background: rgba(0, 0, 0, 0.1);
                color: #1f2937;
            }

            /* Body */
            .confirmation-body {
                padding: 24px;
            }

            .confirmation-message {
                margin: 0;
                font-size: 15px;
                line-height: 1.6;
                color: #374151;
            }

            /* Footer */
            .confirmation-footer {
                display: flex;
                gap: 12px;
                padding: 16px 24px 24px 24px;
                justify-content: flex-end;
            }

            /* Buttons */
            .confirmation-btn {
                padding: 10px 20px;
                border: none;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
                outline: none;
            }

            .confirmation-btn:focus {
                outline: 2px solid #3b82f6;
                outline-offset: 2px;
            }

            /* Cancel Button */
            .confirmation-btn-cancel {
                background: #f3f4f6;
                color: #374151;
                border: 1px solid #e5e7eb;
            }

            .confirmation-btn-cancel:hover {
                background: #e5e7eb;
                border-color: #d1d5db;
            }

            .confirmation-btn-cancel:active {
                transform: scale(0.98);
            }

            /* Confirm Buttons - Different colors by type */
            .confirmation-btn-confirm {
                color: white;
                font-weight: 600;
            }

            .confirmation-btn-danger {
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            }

            .confirmation-btn-danger:hover {
                background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
                box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            }

            .confirmation-btn-danger:active {
                transform: scale(0.98);
            }

            .confirmation-btn-warning {
                background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            }

            .confirmation-btn-warning:hover {
                background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
                box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            }

            .confirmation-btn-info {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            }

            .confirmation-btn-info:hover {
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            }

            /* Responsive */
            @media (max-width: 640px) {
                .confirmation-content {
                    width: 85%;
                    max-width: 90%;
                }

                .confirmation-header {
                    padding: 20px;
                    gap: 12px;
                }

                .confirmation-title {
                    font-size: 18px;
                }

                .confirmation-body {
                    padding: 16px 20px;
                }

                .confirmation-footer {
                    flex-direction: column-reverse;
                    padding: 12px 20px 20px 20px;
                }

                .confirmation-btn {
                    width: 100%;
                    padding: 12px;
                }
            }
        `;

        document.head.appendChild(style);
    }

    // Inject styles when script loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', injectStyles);
    } else {
        injectStyles();
    }

})();
