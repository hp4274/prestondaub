/**
 * Form Modal Handler
 * Opens form details in a sidebar modal when clicking view button
 */

/**
 * UNIFIED BADGE COLOR SYSTEM
 * Provides consistent styling across all form pages
 * Semantic meaning:
 * - Priority badges: indicate form urgency (HIGH/MEDIUM/LOW)
 * - Status badges: indicate form state (NEW/READ/SPAM/ARCHIVED)
 * - Category badges: indicate form type/category
 */
const BADGE_COLORS = {
    priority: {
        'high': { bg: '#FEE2E2', text: '#DC2626', label: '↑ High', description: 'High Priority' },
        'medium': { bg: '#FEF08A', text: '#92400E', label: '- Mid', description: 'Medium Priority' },
        'low': { bg: '#D1FAE5', text: '#065F46', label: '↓ Low', description: 'Low Priority' },
        'not-set': { bg: '#f3f4f6', text: '#9ca3af', label: 'Not Set', description: 'Priority Not Set' }
    },
    status: {
        'new': { bg: '#DBEAFE', text: '#0b0ea8', label: 'New', icon: 'mail' },
        'read': { bg: '#D1FAE5', text: '#065F46', label: 'Read', icon: 'done' },
        'spam': { bg: '#FEE2E2', text: '#DC2626', label: 'Spam', icon: 'close' },
        'archived': { bg: '#E9D5FF', text: '#6B21A8', label: 'Archived', icon: 'archive' }
    },
    category: {
        'default': { bg: '#E0E7FF', text: '#3730A3', label: 'Category' }
    }
};

/**
 * Render a priority badge with consistent styling
 * @param {string} priority - Priority level: 'high', 'medium', 'low', or null
 * @returns {string} HTML for the badge
 */
function renderPriorityBadge(priority) {
    if (!priority) {
        const color = BADGE_COLORS.priority['not-set'];
        return `<span class="badge" style="padding: 6px 12px; border-radius: 6px; font-weight: 600; background: ${color.bg}; color: ${color.text}; title: ${color.description}; cursor: help;">${color.label}</span>`;
    }
    
    const color = BADGE_COLORS.priority[priority];
    if (!color) {
        console.warn(`Unknown priority level: ${priority}`);
        return `<span class="badge" style="padding: 6px 12px; border-radius: 6px; font-weight: 600; background: #f3f4f6; color: #9ca3af;">Unknown</span>`;
    }
    
    return `<span class="badge priority-badge" style="padding: 6px 12px; border-radius: 6px; font-weight: 600; background: ${color.bg}; color: ${color.text}; title: ${color.description}; cursor: help;" title="${color.description}">${color.label}</span>`;
}

/**
 * Render a status badge with consistent styling
 * @param {string} status - Status value: 'new', 'read', 'spam', 'archived'
 * @returns {string} HTML for the badge
 */
function renderStatusBadge(status) {
    const color = BADGE_COLORS.status[status] || BADGE_COLORS.status['read'];
    return `<span class="badge status-badge" style="padding: 6px 12px; border-radius: 6px; font-weight: 600; background: ${color.bg}; color: ${color.text};">${color.label}</span>`;
}

/**
 * Render a category badge with consistent styling
 * @param {string} category - Category name
 * @param {object} overrideColor - Optional color override { bg, text }
 * @returns {string} HTML for the badge
 */
function renderCategoryBadge(category, overrideColor = null) {
    const color = overrideColor || BADGE_COLORS.category['default'];
    return `<span class="badge category-badge" style="padding: 6px 12px; border-radius: 6px; font-weight: 600; background: ${color.bg}; color: ${color.text};">${category}</span>`;
}

/**
 * Apply row styling based on form status
 * @param {HTMLElement} row - Table row element
 * @param {string} status - Form status value
 */
function applyRowStatusStyling(row, status) {
    // Clear existing styles
    row.style.backgroundColor = '';
    row.style.borderLeft = '';
    
    // Apply new styling based on status
    if (status === 'new') {
        row.style.backgroundColor = '#DBEAFE';  // Light blue for new
    } else if (status === 'spam') {
        row.style.backgroundColor = '#FEE2E2';  // Light red for spam
        row.style.borderLeft = '4px solid #EF4444';  // Red left border
    } else if (status === 'read') {
        row.style.backgroundColor = '#D1FAE5';  // Light green for read
    } else if (status === 'archived') {
        row.style.backgroundColor = '#E9D5FF';  // Light purple for archived
    }
}

// Track active modals and listeners
let closeOnEscapeListener = null;
let currentFormId = null;

// Real-time form submission tracking
let lastFormCheckTime = Math.floor(Date.now() / 1000) - 120; // Start from 120 seconds ago to catch any forms
let formPollingInterval = null;
let currentFormType = null;

/**
 * Start polling for new form submissions
 */
function startFormPolling(formType) {
    currentFormType = formType;
    // When starting polling, look back 30 seconds to catch forms submitted just before page load
    lastFormCheckTime = Math.floor(Date.now() / 1000) - 30;
    console.log('[POLLING] Initialized. Looking for forms since:', new Date(lastFormCheckTime * 1000).toISOString());
    
    // Clear any existing interval
    if (formPollingInterval) {
        clearInterval(formPollingInterval);
    }
    
    // Check for new forms immediately (don't wait for the first interval)
    console.log('[POLLING] Performing initial check immediately');
    checkForNewForms(formType);
    
    // Poll every 3 seconds
    formPollingInterval = setInterval(() => {
        checkForNewForms(formType);
    }, 3000);
    
    console.log('[POLLING] Started polling for new ' + formType + ' forms every 3 seconds');
}

/**
 * Stop polling for new form submissions
 */
function stopFormPolling() {
    if (formPollingInterval) {
        clearInterval(formPollingInterval);
        formPollingInterval = null;
        console.log('Stopped form polling');
    }
}

/**
 * Check for new form submissions
 */
function checkForNewForms(formType) {
    const params = new URLSearchParams({
        form_type: formType,
        since: lastFormCheckTime
    });
    const url = `/api/admin/forms/poll?${params.toString()}`;
    console.log(`[POLLING] Checking for new forms: ${url}, lastCheckTime: ${lastFormCheckTime}`);
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch new forms');
            return response.json();
        })
        .then(data => {
            console.log('[POLLING] Response:', data);
            
            if (data.success && data.count > 0) {
                console.log('[POLLING] Found ' + data.count + ' new form(s)');
                // Update the last check time
                lastFormCheckTime = data.current_time;
                console.log('[POLLING] Updated lastFormCheckTime to:', lastFormCheckTime);
                
                // Add new forms to the table
                data.forms.forEach(form => {
                    console.log('[POLLING] Adding form to table:', form);
                    addNewFormToTable(form);
                });
                
                // Show notification
                showToast(`New submission received! (${data.count} new form${data.count > 1 ? 's' : ''})`);
                
                // Update statistics
                updateStatistics();
            } else {
                console.log('[POLLING] No new forms found');
            }
        })
        .catch(error => {
            console.error('[POLLING] Error checking for new forms:', error);
        });
}

/**
 * Add a new form row to the beginning of the table
 */
function addNewFormToTable(form) {
    const tableBody = document.querySelector('tbody');
    if (!tableBody) {
        console.error('[TABLE] tbody not found!');
        return;
    }
    
    console.log('[TABLE] Adding new row for form:', form);
    
    // Create avatar color
    const colors = {
        'A': '#FFB3BA', 'B': '#BAE7E7', 'C': '#A8D8E8', 'D': '#FFD1B3', 'E': '#C8E6DD',
        'F': '#FFED99', 'G': '#E0B8F0', 'H': '#C9E4F5', 'I': '#FFDAB3', 'J': '#A8DCC8',
        'K': '#FFB3D9', 'L': '#B3D9F2', 'M': '#FFD699', 'N': '#A8D99B', 'O': '#E8B3E0',
        'P': '#D1A8F0', 'Q': '#99CCFF', 'R': '#FFB399', 'S': '#FFFF99', 'T': '#99F0FF',
        'U': '#D9B3FF', 'V': '#FF99D1', 'W': '#99D4B8', 'X': '#FF9999', 'Y': '#FFE8B3',
        'Z': '#99CCFF'
    };
    
    const firstName = form.name.split(' ')[0];
    const initials = firstName.charAt(0).toUpperCase();
    const avatarColor = colors[initials] || '#B3D9F2';
    
    // Create priority badge HTML
    let priorityHTML = '';
    if (form.priority) {
        const priorityColors = {
            'high': { bg: '#FEE2E2', text: '#DC2626', label: '↑ High' },
            'medium': { bg: '#FEF08A', text: '#92400E', label: '- Mid' },
            'low': { bg: '#D1FAE5', text: '#065F46', label: '↓ Low' }
        };
        const color = priorityColors[form.priority];
        priorityHTML = `<span class="badge" style="padding: 6px 12px; border-radius: 6px; font-weight: 600; background: ${color.bg}; color: ${color.text};">${color.label}</span>`;
    } else {
        priorityHTML = `<span class="badge" style="padding: 6px 12px; border-radius: 6px; font-weight: 600; background: #f3f4f6; color: #9ca3af;">Not Set</span>`;
    }
    
    // Create status badge HTML
    const statusColors = {
        'new': { bg: '#FEF08A', text: '#92400E' },
        'read': { bg: '#D1FAE5', text: '#065F46' },
        'archived': { bg: '#E9D5FF', text: '#6B21A8' },
        'spam': { bg: '#FEE2E2', text: '#7F1D1D' }
    };
    const statusColor = statusColors[form.status] || statusColors['new'];
    const statusHTML = `<span class="badge" style="padding: 6px 12px; border-radius: 6px; font-weight: 600; background: ${statusColor.bg}; color: ${statusColor.text};">${form.status.charAt(0).toUpperCase() + form.status.slice(1)}</span>`;
    
    // Format created date
    const date = new Date(form.created_at);
    const dateStr = date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    // Create row
    const rowHTML = `
        <tr style="border-bottom: 1px solid var(--border-color); transition: background-color 0.2s; ${form.status === 'spam' ? 'border-left: 4px solid #EF4444; background-color: #FEE2E2;' : (form.status === 'new' ? 'background-color: #DBEAFE;' : '')}" data-form-id="${form.id}" data-status="${form.status}">
            <td style="padding: 16px; text-align: center;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: ${avatarColor}; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; color: #333; margin: 0 auto;">
                    ${initials}
                </div>
            </td>
            <td style="padding: 16px; color: var(--text-primary); font-weight: 600;">${form.name}</td>
            <td style="padding: 16px;">
                <a href="mailto:${form.email}" style="color: #0b0ea8; text-decoration: none;">
                    ${form.email}
                </a>
            </td>
            <td style="padding: 16px;">
                <small class="badge" style="background: var(--light-bg); color: #0b0ea8; padding: 6px 12px; border-radius: 6px; font-weight: 600;">
                    ${form.service || 'N/A'}
                </small>
            </td>
            <td style="padding: 16px;">
                ${priorityHTML}
            </td>
            <td style="padding: 16px;">
                ${statusHTML}
            </td>
            <td style="padding: 16px; color: var(--text-secondary); font-size: 14px;">
                <small>${dateStr}</small>
            </td>
            <td style="padding: 16px;">
                <div class="action-buttons" style="display: flex; gap: 8px;">
                    <button onclick="openFormModal(${form.id})" class="btn btn-sm" style="background: white; border: 1px solid #e5e7eb; color: #0b0ea8; border-radius: 6px; padding: 6px 10px; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s; cursor: pointer;" title="View">
                        <span class="material-symbols-rounded" style="font-size: 18px;">visibility</span>
                    </button>
                    <a href="?action=mark-read&id=${form.id}" class="btn btn-sm" style="background: white; border: 1px solid #e5e7eb; color: #10B981; border-radius: 6px; padding: 6px 10px; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s; cursor: pointer;" title="Mark as Read">
                        <span class="material-symbols-rounded" style="font-size: 18px;">done</span>
                    </a>
                    <a href="?action=mark-spam&id=${form.id}" class="btn btn-sm" style="background: white; border: 1px solid #e5e7eb; color: #EF4444; border-radius: 6px; padding: 6px 10px; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s; cursor: pointer;" title="Mark as Spam/Invalid">
                        <span class="material-symbols-rounded" style="font-size: 18px;">flag</span>
                    </a>
                </div>
            </td>
        </tr>
    `;
    
    // Insert at the beginning of tbody with animation
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = rowHTML;
    const newRow = tempDiv.querySelector('tr');
    
    // Remove empty state row if it exists
    const emptyRow = tableBody.querySelector('tr td[colspan="7"]');
    if (emptyRow) {
        console.log('[TABLE] Removing empty state row');
        emptyRow.parentElement.remove();
    }
    
    // Add animation
    newRow.style.opacity = '0';
    newRow.style.transform = 'translateY(-20px)';
    newRow.style.transition = 'all 0.3s ease';
    
    tableBody.insertBefore(newRow, tableBody.firstChild);
    console.log('[TABLE] Row inserted into DOM');
    
    // Trigger animation
    setTimeout(() => {
        newRow.style.opacity = '1';
        newRow.style.transform = 'translateY(0)';
        console.log('[TABLE] Animation triggered');
    }, 10);
}

/**
 * Update statistics boxes
 */
function updateStatistics() {
    console.log('updateStatistics called');
    // Call the refreshStats function if it exists (defined in the page)
    if (typeof refreshStats === 'function') {
        console.log('Calling refreshStats function');
        refreshStats();
    } else {
        // Fallback: Fetch stats directly via the API endpoint
        console.log('refreshStats not found, fetching stats via API');
        fetch('?api=stats')
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch stats');
                return response.json();
            })
            .then(data => {
                console.log('Stats received:', data);
                const totalEl = document.getElementById('stat-total');
                const newEl = document.getElementById('stat-new');
                const readEl = document.getElementById('stat-read');
                const spamEl = document.getElementById('stat-spam');
                
                if (totalEl) { 
                    console.log('Updating stat-total from', totalEl.textContent, 'to', data.total);
                    totalEl.textContent = data.total; 
                }
                if (newEl) { 
                    console.log('Updating stat-new from', newEl.textContent, 'to', data.new);
                    newEl.textContent = data.new; 
                }
                if (readEl) { 
                    console.log('Updating stat-read from', readEl.textContent, 'to', data.read);
                    readEl.textContent = data.read; 
                }
                if (spamEl) { 
                    console.log('Updating stat-spam from', spamEl.textContent, 'to', data.spam);
                    spamEl.textContent = data.spam; 
                }
            })
            .catch(error => console.error('Error updating statistics:', error));
    }
}

/**
 * Update category grid with latest counts from server
 */
function updateCategoryGrid() {
    console.log('updateCategoryGrid called');
    fetch('get-sports-categories.php')
        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch category data');
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                console.error('API returned error');
                return;
            }
            
            console.log('Category data received:', data.data);
            
            // Update each sport category
            const sports = ['soccer', 'f1', 'nba', 'nfl', 'mlb', 'cricket'];
            sports.forEach(sport => {
                const sportData = data.data[sport];
                if (sportData) {
                    const totalEl = document.getElementById(`cat-${sport}-total`);
                    const newEl = document.getElementById(`cat-${sport}-new`);
                    const readEl = document.getElementById(`cat-${sport}-read`);
                    const archivedEl = document.getElementById(`cat-${sport}-archived`);
                    
                    if (totalEl) {
                        console.log(`Updating cat-${sport}-total from ${totalEl.textContent} to ${sportData.total}`);
                        totalEl.textContent = sportData.total;
                    }
                    if (newEl) {
                        console.log(`Updating cat-${sport}-new from ${newEl.textContent} to ${sportData.new}`);
                        newEl.textContent = sportData.new;
                    }
                    if (readEl) {
                        console.log(`Updating cat-${sport}-read from ${readEl.textContent} to ${sportData.read}`);
                        readEl.textContent = sportData.read;
                    }
                    if (archivedEl) {
                        console.log(`Updating cat-${sport}-archived from ${archivedEl.textContent} to ${sportData.archived}`);
                        archivedEl.textContent = sportData.archived;
                    }
                }
            });
            
            console.log('Category grid updated successfully');
        })
        .catch(error => console.error('Error updating category grid:', error));
}

/**
 * Form Modal Handler
 * Opens form details in a sidebar modal when clicking view button
 */

/**
 * Update table row with latest data from server
 * Handles priority and status updates accurately without corrupting other cells
 */
function updateTableRow(formId) {
    // Fetch the updated form data as JSON
    fetch(`/api/admin/forms/${formId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch form data');
            }
            return response.json();
        })
        .then(data => {
            console.log('[TABLE UPDATE] Received form data:', data);
            
            // Find the table row by data-form-id attribute
            const targetRow = document.querySelector(`tr[data-form-id="${formId}"]`);
            
            if (!targetRow) {
                console.warn('[TABLE UPDATE] Table row not found for form ID:', formId);
                return;
            }
            
            console.log('[TABLE UPDATE] Found row, current cell count:', targetRow.cells.length);
            
            // Log all cells before update
            Array.from(targetRow.cells).forEach((cell, index) => {
                console.log(`[TABLE UPDATE] Cell ${index}:`, cell.textContent.trim().substring(0, 50));
            });
            
            // Standard table column order (consistent across all form pages):
            // 0: Photo, 1: Name, 2: Email, 3: Service, 4: Priority, 5: Date, 6: Action
            const PRIORITY_CELL_INDEX = 4;
            
            // ========== UPDATE PRIORITY CELL ==========
            if (data.priority !== undefined && targetRow.cells[PRIORITY_CELL_INDEX]) {
                console.log('[TABLE UPDATE] Updating priority cell from', 
                    targetRow.cells[PRIORITY_CELL_INDEX].textContent, 
                    'to', data.priority);
                
                const priorityCell = targetRow.cells[PRIORITY_CELL_INDEX];
                let priorityHTML = '';
                
                if (data.priority) {
                    const colors = {
                        'high': { bg: '#FEE2E2', text: '#DC2626', label: '↑ High' },
                        'medium': { bg: '#FEF08A', text: '#92400E', label: '- Mid' },
                        'low': { bg: '#D1FAE5', text: '#065F46', label: '↓ Low' }
                    };
                    const color = colors[data.priority] || colors['low'];
                    priorityHTML = `<span class="badge" style="padding: 6px 12px; border-radius: 6px; font-weight: 600; background: ${color.bg}; color: ${color.text}; white-space: nowrap;">${color.label}</span>`;
                } else {
                    priorityHTML = `<span class="badge" style="padding: 6px 12px; border-radius: 6px; font-weight: 600; background: #f3f4f6; color: #9ca3af; white-space: nowrap;">Not Set</span>`;
                }
                
                // Update ONLY the innerHTML of this cell, preserve cell element itself
                priorityCell.innerHTML = priorityHTML;
                console.log('[TABLE UPDATE] Priority cell updated');
            } else {
                console.warn('[TABLE UPDATE] Priority data missing or cell not found. Cells:', targetRow.cells.length, 'Priority index:', PRIORITY_CELL_INDEX);
            }
            
            // ========== UPDATE ROW STYLING BASED ON STATUS ==========
            const oldStatus = targetRow.getAttribute('data-status');
            const newStatus = data.status || 'read';
            
            console.log('[TABLE UPDATE] Status check - Old:', oldStatus, 'New:', newStatus);
            
            if (newStatus !== oldStatus) {
                console.log('[TABLE UPDATE] Status changed, updating row styling');
                
                // Reset all row styles
                targetRow.style.backgroundColor = '';
                targetRow.style.borderLeft = '';
                
                // Apply new status styling
                if (newStatus === 'new') {
                    targetRow.style.backgroundColor = '#DBEAFE';
                    targetRow.style.borderLeft = 'none';
                } else if (newStatus === 'spam') {
                    targetRow.style.backgroundColor = '#FEE2E2';
                    targetRow.style.borderLeft = '4px solid #EF4444';
                } else if (newStatus === 'read') {
                    targetRow.style.backgroundColor = '';
                    targetRow.style.borderLeft = '';
                } else if (newStatus === 'archived') {
                    targetRow.style.backgroundColor = '#E9D5FF';
                    targetRow.style.borderLeft = 'none';
                }
                
                // Update data-status attribute
                targetRow.setAttribute('data-status', newStatus);
                console.log('[TABLE UPDATE] Row styling updated for status:', newStatus);
            } else {
                console.log('[TABLE UPDATE] Status unchanged, no row styling update needed');
            }
            
            console.log('[TABLE UPDATE] Row update complete');
        })
        .catch(error => {
            console.error('[TABLE UPDATE] Error updating table row:', error);
        });
}

function openFormModal(formId) {
    currentFormId = formId;
    
    // Create overlay if it doesn't exist
    let overlay = document.getElementById('formModalOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'formModalOverlay';
        overlay.className = 'modal-overlay';
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeFormModal();
            }
        });
        document.body.appendChild(overlay);
    }

    // Create sidebar if it doesn't exist
    let sidebar = document.getElementById('formModalSidebar');
    if (!sidebar) {
        sidebar = document.createElement('div');
        sidebar.id = 'formModalSidebar';
        sidebar.className = 'modal-sidebar';
        document.body.appendChild(sidebar);
    }

    // Show overlay and sidebar
    overlay.style.display = 'block';
    overlay.classList.add('active');
    sidebar.classList.add('active');

    sidebar.innerHTML = `
        <div class="modal-header">
            <h2 class="modal-title">Loading...</h2>
            <button type="button" class="modal-close">×</button>
        </div>
        <div class="modal-content" style="padding: 20px 24px; color: #6b7280;">Fetching form details...</div>
    `;
    attachModalEventListeners();

    fetch(`/api/admin/forms/${formId}/detail`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Failed to load form details (${response.status})`);
            }
            return response.json();
        })
        .then(data => {
            const form = data.form || data;
            sidebar.innerHTML = renderFormModal(form);
            attachModalEventListeners();
            updateTableRow(formId);
        })
        .catch(error => {
            console.error('Error loading form:', error);
            showToast('Error loading form details', 'error');
        });
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function formatDateTime(value) {
    if (!value) return 'N/A';
    const date = new Date(value);
    return Number.isNaN(date.getTime())
        ? 'N/A'
        : date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
}

function getInitialColor(initial) {
    const colors = {
        A: '#FFB3BA', B: '#BAE7E7', C: '#A8D8E8', D: '#FFD1B3', E: '#C8E6DD',
        F: '#FFED99', G: '#E0B8F0', H: '#C9E4F5', I: '#FFDAB3', J: '#A8DCC8',
        K: '#FFB3D9', L: '#B3D9F2', M: '#FFD699', N: '#A8D99B', O: '#E8B3E0',
        P: '#D1A8F0', Q: '#99CCFF', R: '#FFB399', S: '#FFFF99', T: '#99F0FF',
        U: '#D9B3FF', V: '#FF99D1', W: '#99D4B8', X: '#FF9999', Y: '#FFE8B3',
        Z: '#99CCFF'
    };

    return colors[initial] || '#B3D9F2';
}

function renderField(label, value) {
    if (!value) return '';
    return `
        <div style="margin-bottom: 12px;">
            <span style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; display: block; margin-bottom: 6px; letter-spacing: 0.5px;">${escapeHtml(label)}</span>
            <div style="padding: 12px 14px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #1f2937; font-weight: 500; word-break: break-word;">${escapeHtml(value)}</div>
        </div>
    `;
}

function renderFormModal(form) {
    const firstName = (form.name || 'N').trim().split(' ')[0];
    const initial = (firstName.charAt(0) || 'N').toUpperCase();
    const avatarColor = getInitialColor(initial);
    const formData = form.form_data || {};
    const detailFields = [];

    detailFields.push(renderField('Name', form.name || 'N/A'));
    detailFields.push(renderField('Email', form.email || 'N/A'));
    detailFields.push(renderField('Phone', form.phone || 'N/A'));
    detailFields.push(renderField('Company', form.company || form.organization || formData.legal_business_name || ''));
    detailFields.push(renderField('Service', form.service || form.form_type || 'N/A'));
    detailFields.push(renderField('Form Type', form.form_type || 'N/A'));

    if (form.organization_type) detailFields.push(renderField('Organization Type', form.organization_type));
    if (form.job_title) detailFields.push(renderField('Job Title', form.job_title));
    if (formData.financing_solution_type) detailFields.push(renderField('Financing Solution', formData.financing_solution_type));
    if (formData.legal_business_name) detailFields.push(renderField('Legal Business Name', formData.legal_business_name));
    if (formData.business_type) detailFields.push(renderField('Business Type', formData.business_type));

    const interests = Array.isArray(form.interests)
        ? form.interests
        : Array.isArray(formData.interest)
            ? formData.interest
            : [];

    const notes = form.notes || '';
    const currentPriority = form.priority || '';
    const spamButtonText = form.status === 'spam' ? 'Mark as Normal' : 'Mark as Spam';
    const spamButtonStatus = form.status === 'spam' ? 'read' : 'spam';
    const message = form.message || form.goals_challenges || formData.message || formData.challenges || '';

    return `
        <div class="modal-header">
            <h2 class="modal-title">Form Submission</h2>
            <button type="button" class="modal-close">×</button>
        </div>
        <div class="modal-content">
            <div class="modal-profile" style="display: flex; align-items: center; gap: 16px; padding: 20px 24px; border-bottom: 1px solid #f3f4f6; background: #fafbfc;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: ${avatarColor}; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; color: #333; flex-shrink: 0;">${escapeHtml(initial)}</div>
                <div style="flex: 1;">
                    <div style="font-size: 18px; font-weight: 700; color: #1f2937; margin-bottom: 4px;">${escapeHtml(form.name || 'N/A')}</div>
                    <div style="font-size: 13px; color: #6b7280;">${escapeHtml(form.email || 'N/A')}</div>
                </div>
            </div>

            <div style="padding: 20px 24px; border-bottom: 1px solid #f3f4f6; background: white;">
                <h3 style="font-size: 14px; font-weight: 700; color: #1f2937; margin: 0 0 16px 0;">Details</h3>
                ${detailFields.join('')}
                ${renderField('Submitted', formatDateTime(form.created_at))}
            </div>

            ${interests.length ? `
            <div style="padding: 20px 24px; border-bottom: 1px solid #f3f4f6; background: white;">
                <h3 style="font-size: 14px; font-weight: 700; color: #1f2937; margin: 0 0 12px 0;">Interests</h3>
                <div style="padding: 12px; background: #f9fafb; border-radius: 8px; font-size: 13px; color: #4b5563; line-height: 1.6; border: 1px solid #e5e7eb;">
                    <ul style="margin: 0; padding-left: 18px;">${interests.map(item => `<li>${escapeHtml(item)}</li>`).join('')}</ul>
                </div>
            </div>` : ''}

            <div style="padding: 20px 24px; border-bottom: 1px solid #f3f4f6; background: white;">
                <h3 style="font-size: 14px; font-weight: 700; color: #1f2937; margin: 0 0 12px 0;">Message</h3>
                <div style="padding: 12px; background: #f9fafb; border-radius: 8px; font-size: 13px; color: #4b5563; line-height: 1.6; max-height: 200px; overflow-y: auto; border: 1px solid #e5e7eb;">${escapeHtml(message || 'N/A')}</div>
            </div>

            <div style="padding: 20px 24px; border-bottom: 1px solid #f3f4f6; background: white;">
                <h3 style="font-size: 14px; font-weight: 700; color: #1f2937; margin: 0 0 12px 0;">Notes</h3>
                <form method="POST" data-action="update_notes" style="display: flex; flex-direction: column; gap: 12px;">
                    <input type="hidden" name="action" value="update_notes">
                    <textarea name="notes" placeholder="Add your notes here..." style="width: 100%; min-height: 100px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; font-family: inherit; resize: vertical;">${escapeHtml(notes)}</textarea>
                    <button type="submit" style="padding: 12px 20px; background: linear-gradient(135deg, #0b0ea8 0%, #1e40af 100%); color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer;">Save Notes</button>
                </form>
            </div>

            <div style="padding: 20px 24px; border-bottom: 1px solid #f3f4f6; background: white;">
                <div style="font-size: 11px; font-weight: 700; color: #0b0ea8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">Priority</div>
                <div style="display: flex; gap: 8px;">
                    <form method="POST" data-action="update_priority" style="display: inline; flex: 1;">
                        <input type="hidden" name="action" value="update_priority">
                        <input type="hidden" name="priority" value="low">
                        <button type="submit" style="width: 100%; padding: 10px 12px; border-radius: 6px; background: ${currentPriority === 'low' ? '#f0fdf4' : 'white'}; border: 2px solid ${currentPriority === 'low' ? '#22c55e' : '#e5e7eb'}; color: #22c55e; font-size: 12px; font-weight: 600; cursor: pointer;">Low</button>
                    </form>
                    <form method="POST" data-action="update_priority" style="display: inline; flex: 1;">
                        <input type="hidden" name="action" value="update_priority">
                        <input type="hidden" name="priority" value="medium">
                        <button type="submit" style="width: 100%; padding: 10px 12px; border-radius: 6px; background: ${currentPriority === 'medium' ? '#fffbeb' : 'white'}; border: 2px solid ${currentPriority === 'medium' ? '#f59e0b' : '#e5e7eb'}; color: #f59e0b; font-size: 12px; font-weight: 600; cursor: pointer;">Mid</button>
                    </form>
                    <form method="POST" data-action="update_priority" style="display: inline; flex: 1;">
                        <input type="hidden" name="action" value="update_priority">
                        <input type="hidden" name="priority" value="high">
                        <button type="submit" style="width: 100%; padding: 10px 12px; border-radius: 6px; background: ${currentPriority === 'high' ? '#fef2f2' : 'white'}; border: 2px solid ${currentPriority === 'high' ? '#ef4444' : '#e5e7eb'}; color: #ef4444; font-size: 12px; font-weight: 600; cursor: pointer;">High</button>
                    </form>
                </div>
            </div>

            <div style="padding: 20px 24px; background: white; display: flex; flex-direction: row; gap: 10px; flex-wrap: wrap;">
                <a href="mailto:${escapeHtml(form.email || '')}" style="flex: 1; min-width: 120px; padding: 12px 20px; background: white; border: 1px solid #e5e7eb; border-radius: 8px; color: #0b0ea8; font-weight: 600; font-size: 14px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">Reply via Email</a>
                <form method="POST" data-action="update_status" style="flex: 1; min-width: 120px; display: flex;">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="status" value="${escapeHtml(spamButtonStatus)}">
                    <button type="submit" style="flex: 1; padding: 12px 20px; background: white; border: 1px solid #fef08a; border-radius: 8px; color: #b45309; font-weight: 600; font-size: 14px; cursor: pointer;">${escapeHtml(spamButtonText)}</button>
                </form>
                <form method="POST" data-action="delete" style="flex: 1; min-width: 120px; display: flex;">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" style="flex: 1; padding: 12px 20px; background: white; border: 1px solid #fee2e2; border-radius: 8px; color: #dc2626; font-weight: 600; font-size: 14px; cursor: pointer;">Delete</button>
                </form>
            </div>
        </div>
    `;
}

function closeFormModal() {
    const overlay = document.getElementById('formModalOverlay');
    const sidebar = document.getElementById('formModalSidebar');
    
    // Remove active classes for animation
    if (overlay) overlay.classList.remove('active');
    if (sidebar) sidebar.classList.remove('active');
    
    // Remove Escape key listener
    if (closeOnEscapeListener) {
        document.removeEventListener('keydown', closeOnEscapeListener);
        closeOnEscapeListener = null;
    }
    
    // Wait for animation to complete, then hide
    setTimeout(() => {
        if (overlay) {
            overlay.style.display = 'none';
        }
        if (sidebar) {
            sidebar.innerHTML = '';
        }
        
        // IMPORTANT: Don't call updateTableRow() here - it has bugs and causes incorrect re-rendering
        // Instead, just refresh the statistics numbers which update via API
        // The table row styling and buttons will be preserved as-is
        updateStatistics();
        
        // Do NOT reload page - this breaks scroll position
        // The stats are already being updated via refreshStats() API call
        // No page reload needed
        
        currentFormId = null;
    }, 300);
}

function attachModalEventListeners() {
    // Format dates with time
    document.querySelectorAll('[data-date]').forEach(el => {
        const dateStr = el.getAttribute('data-date');
        if (dateStr) {
            const date = new Date(dateStr);
            if (!isNaN(date)) {
                el.textContent = date.toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        }
    });

    // Close button
    const closeBtn = document.querySelector('.modal-close');
    if (closeBtn) {
        closeBtn.onclick = closeFormModal;
    }

    // Priority form submissions
    const priorityForms = document.querySelectorAll('form[data-action="update_priority"]');
    console.log('Found priority forms:', priorityForms.length);
    priorityForms.forEach((form, idx) => {
        console.log('Attaching priority handler to form', idx);
        form.onsubmit = function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            console.log('[PRIORITY UPDATE] Submitting form with action:', formData.get('action'), 'priority:', formData.get('priority'), 'formId:', currentFormId);
            
            fetch(`/api/admin/forms/${currentFormId}/actions`, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('[PRIORITY UPDATE] Response status:', response.status, 'Content-Type:', response.headers.get('Content-Type'));
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('[PRIORITY UPDATE] Error response:', text);
                        throw new Error(`HTTP error! status: ${response.status}. Response: ${text.substring(0, 200)}`);
                    });
                }
                return response.text();
            })
            .then(text => {
                console.log('[PRIORITY UPDATE] Response text:', text.substring(0, 200));
                try {
                    const data = JSON.parse(text);
                    console.log('[PRIORITY UPDATE] Parsed JSON:', data);
                    if (data.success) {
                        showToast('Priority updated', 'success');
                        
                        // Update table row immediately with slight delay to ensure DB is updated
                        console.log('[PRIORITY UPDATE] Scheduling table row update');
                        setTimeout(() => {
                            console.log('[PRIORITY UPDATE] Calling updateTableRow');
                            updateTableRow(currentFormId);
                        }, 100);
                        
                        // Update statistics to reflect any priority changes
                        console.log('[PRIORITY UPDATE] Scheduling statistics update');
                        setTimeout(() => {
                            console.log('[PRIORITY UPDATE] Calling updateStatistics');
                            updateStatistics();
                        }, 150);
                        
                        // Reload modal content to show updated priority styling
                        console.log('[PRIORITY UPDATE] Scheduling modal reload');
                        setTimeout(() => {
                            console.log('[PRIORITY UPDATE] Calling openFormModal');
                            openFormModal(currentFormId);
                        }, 200);
                    } else {
                        showToast(data.message || 'Error updating priority', 'error');
                    }
                } catch(e) {
                    console.error('[PRIORITY UPDATE] JSON parse error:', e, 'Text was:', text);
                    showToast('Error: ' + (e.message || 'Invalid server response'), 'error');
                }
            })
            .catch(error => {
                console.error('[PRIORITY UPDATE] Fetch error:', error);
                showToast('Error: ' + error.message, 'error');
            });
        };
    });

    // Notes form submission
    const notesForm = document.querySelector('form[data-action="update_notes"]');
    if (notesForm) {
        notesForm.onsubmit = function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            fetch(`/api/admin/forms/${currentFormId}/actions`, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Notes response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        showToast(data.message || 'Notes updated', 'success');
                    } else {
                        showToast(data.message || 'Error updating notes', 'error');
                    }
                } catch(e) {
                    console.error('Notes JSON parse error:', e);
                    showToast('Error updating notes', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating notes', 'error');
            });
        };
    }

    // Status update form submission (Spam button)
    const statusForm = document.querySelector('form[data-action="update_status"]');
    if (statusForm) {
        statusForm.onsubmit = function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            fetch(`/api/admin/forms/${currentFormId}/actions`, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Status response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        showToast('Form marked as spam', 'success');
                        // Update table row immediately with slight delay to ensure DB is updated
                        setTimeout(() => {
                            updateTableRow(currentFormId);
                        }, 100);
                        // Reload modal content to show updated status
                        setTimeout(() => {
                            openFormModal(currentFormId);
                        }, 200);
                    } else {
                        showToast(data.message || 'Error marking as spam', 'error');
                    }
                } catch(e) {
                    console.error('Status JSON parse error:', e);
                    showToast('Error marking as spam', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error marking as spam', 'error');
            });
        };
    }

    // Delete form submission - Using custom confirmation modal
    const deleteForm = document.querySelector('form[data-action="delete"]');
    if (deleteForm) {
        deleteForm.onsubmit = async function(e) {
            e.preventDefault();
            console.log('[DELETE] Delete form submitted for form ID:', currentFormId);
            
            // Show custom confirmation modal instead of browser confirm()
            // IMPORTANT: Only proceed if user explicitly confirms in the modal
            const confirmed = await confirmDelete('this form');
            
            if (!confirmed) {
                console.log('[DELETE] User cancelled delete confirmation for form ID:', currentFormId);
                return; // Stop here - do NOT delete
            }
            
            console.log('[DELETE] User confirmed delete for form ID:', currentFormId);
            
            const formData = new FormData(this);
            fetch(`/api/admin/forms/${currentFormId}/actions`, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('[DELETE] Delete response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        console.log('[DELETE] Delete successful, showing success toast');
                        showToast(data.message, 'success');
                        setTimeout(() => {
                            console.log('[DELETE] Closing modal and reloading page');
                            closeFormModal();
                            location.reload();
                        }, 500);
                    } else {
                        console.error('[DELETE] Delete failed:', data.message);
                        showToast(data.message || 'Error deleting form', 'error');
                    }
                } catch(e) {
                    console.error('[DELETE] JSON parse error:', e);
                    showToast('Error deleting form', 'error');
                }
            })
            .catch(error => {
                console.error('[DELETE] Fetch error:', error);
                showToast('Error: ' + error.message, 'error');
            });
        };
    }

    // Remove old Escape listener if it exists
    if (closeOnEscapeListener) {
        document.removeEventListener('keydown', closeOnEscapeListener);
    }

    // Add new Escape key listener
    closeOnEscapeListener = (e) => {
        if (e.key === 'Escape') {
            closeFormModal();
        }
    };
    document.addEventListener('keydown', closeOnEscapeListener);
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: ${type === 'success' ? '#22c55e' : '#ef4444'};
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        animation: slideUp 0.3s ease;
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideDown 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}

// Add CSS for modal and toast animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from {
            transform: translateY(100px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes slideDown {
        from {
            transform: translateY(0);
            opacity: 1;
        }
        to {
            transform: translateY(100px);
            opacity: 0;
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        display: none;
        animation: fadeIn 0.3s ease;
        backdrop-filter: blur(4px);
    }

    .modal-overlay.active {
        display: block;
    }

    .modal-sidebar {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: 420px;
        background: white;
        box-shadow: -4px 0 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        display: flex;
        flex-direction: column;
        transform: translateX(420px);
        transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
        overflow-y: auto;
    }

    .modal-sidebar.active {
        transform: translateX(0);
    }

    @media (max-width: 768px) {
        .modal-sidebar {
            width: 100%;
            transform: translateX(100%);
        }

        .modal-sidebar.active {
            transform: translateX(0);
        }
    }

    /* Delete Confirmation Modal Styles */
    #deleteConfirmModal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }

    .delete-confirm-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0);
        cursor: pointer;
        transition: background 0.3s ease;
    }

    #deleteConfirmModal.active .delete-confirm-overlay {
        background: rgba(0, 0, 0, 0.5);
    }

    .delete-confirm-dialog {
        position: relative;
        background: white;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        width: 90%;
        max-width: 420px;
        transform: scale(0.95) translateY(20px);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    #deleteConfirmModal.active .delete-confirm-dialog {
        transform: scale(1) translateY(0);
        opacity: 1;
    }

    .delete-confirm-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 24px;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(135deg, #fef2f2 0%, #fff5f5 100%);
    }

    .delete-confirm-icon {
        font-size: 28px;
        color: #ef4444;
        font-weight: 500;
    }

    .delete-confirm-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
    }

    .delete-confirm-body {
        padding: 24px;
    }

    .delete-confirm-text {
        margin: 0 0 12px 0;
        font-size: 14px;
        line-height: 1.6;
        color: #374151;
    }

    .delete-confirm-text strong {
        color: #1f2937;
        font-weight: 600;
    }

    .delete-confirm-warning {
        margin: 12px 0 0 0;
        padding: 12px;
        background: #fef2f2;
        border-left: 3px solid #ef4444;
        border-radius: 4px;
        font-size: 13px;
        line-height: 1.5;
        color: #7f1d1d;
    }

    .delete-confirm-footer {
        display: flex;
        gap: 12px;
        padding: 16px 24px 24px 24px;
        justify-content: flex-end;
    }

    .delete-confirm-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        min-width: 100px;
    }

    .cancel-btn {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }

    .cancel-btn:hover {
        background: #e5e7eb;
        border-color: #d1d5db;
    }

    .cancel-btn:active {
        background: #d1d5db;
        transform: scale(0.98);
    }

    .delete-btn {
        background: #ef4444;
        color: white;
    }

    .delete-btn:hover {
        background: #dc2626;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .delete-btn:active {
        background: #b91c1c;
        transform: scale(0.98);
    }

    @media (max-width: 480px) {
        .delete-confirm-dialog {
            width: 95%;
            max-width: none;
        }

        .delete-confirm-header {
            padding: 18px;
        }

        .delete-confirm-body {
            padding: 18px;
        }

        .delete-confirm-footer {
            padding: 12px 18px 18px 18px;
            flex-wrap: wrap;
        }

        .delete-confirm-btn {
            flex: 1;
            min-width: 80px;
        }
    }
`;
document.head.appendChild(style);

/**
 * Delete confirmation dialog - Custom themed modal
 * Shows a beautiful confirmation dialog that matches the app theme
 * @param {string} itemType - Description of the item being deleted
 * @returns {boolean} - true if user confirms, false if user cancels
 */
function confirmDelete(itemType = 'this item', form = null) {
    return new Promise((resolve) => {
        // Create modal HTML
        const modal = document.createElement('div');
        modal.id = 'deleteConfirmModal';
        modal.innerHTML = `
            <div class="delete-confirm-overlay"></div>
            <div class="delete-confirm-dialog">
                <div class="delete-confirm-header">
                    <span class="material-symbols-rounded delete-confirm-icon">warning</span>
                    <h2>Delete Confirmation</h2>
                </div>
                <div class="delete-confirm-body">
                    <p class="delete-confirm-text">Are you sure you want to permanently delete <strong>${escapeHtml(itemType)}</strong>?</p>
                    <p class="delete-confirm-warning">⚠️ This action cannot be undone. The record will be permanently removed.</p>
                </div>
                <div class="delete-confirm-footer">
                    <button class="delete-confirm-btn cancel-btn">Cancel</button>
                    <button class="delete-confirm-btn delete-btn">Delete Permanently</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Trigger animation
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
        
        // Handle button clicks
        const cancelBtn = modal.querySelector('.cancel-btn');
        const deleteBtn = modal.querySelector('.delete-btn');
        const overlay = modal.querySelector('.delete-confirm-overlay');
        
        const cleanup = () => {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.remove();
            }, 300);
        };
        
        cancelBtn.addEventListener('click', () => {
            cleanup();
            resolve(false);
        });
        
        deleteBtn.addEventListener('click', () => {
            cleanup();
            resolve(true);
        });
        
        overlay.addEventListener('click', () => {
            cleanup();
            resolve(false);
        });
        
        // Handle Escape key
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                document.removeEventListener('keydown', handleEscape);
                cleanup();
                resolve(false);
            }
        };
        document.addEventListener('keydown', handleEscape);
    });
}

/**
 * Escape HTML special characters
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Initialize delete form handlers
 * Attaches click event listeners to all delete forms to show confirmation modal
 */
function initializeDeleteForms() {
    const deleteForms = document.querySelectorAll('.delete-form');
    
    deleteForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault(); // Prevent immediate submission
            
            // Get the item type from data attribute
            const itemType = form.getAttribute('data-item-type') || 'this item';
            
            // Show confirmation modal
            const confirmed = await confirmDelete(itemType, form);
            
            // Only submit if user confirmed
            if (confirmed) {
                // Remove the event listener to allow actual submission
                form.onsubmit = null;
                form.submit();
            }
        });
    });
}

/**
 * Initialize date formatting and other DOM listeners
 * Call this after the page loads to format all dates
 */
/**
 * Initialize action link handlers (mark-read, mark-spam, etc.)
 * Provides immediate visual feedback before page reload
 */
function initializeActionLinkHandlers() {
    console.log('[ACTION LINKS] Initializing action link handlers');
    
    // Handle all action links (mark-read, mark-spam, mark-new)
    document.addEventListener('click', function(event) {
        const link = event.target.closest('a[href*="action=mark-"]');
        if (!link) return;
        
        // Extract form ID and action from the href
        const href = link.getAttribute('href');
        const formIdMatch = href.match(/id=(\d+)/);
        const actionMatch = href.match(/action=(mark-[a-z]+)/);
        
        if (!formIdMatch || !actionMatch) return;
        
        const formId = formIdMatch[1];
        const action = actionMatch[1];
        
        console.log(`[ACTION LINKS] Action link clicked: ${action} for form ID ${formId}`);
        
        // Get the table row
        const row = document.querySelector(`tr[data-form-id="${formId}"]`);
        if (!row) {
            console.warn(`[ACTION LINKS] Row not found for form ID ${formId}`);
            return;
        }
        
        // Immediately update row styling based on action
        if (action === 'mark-read') {
            console.log(`[ACTION LINKS] Updating row ${formId} for mark-read action`);
            row.style.backgroundColor = '#D1FAE5';  // Green background for read
            row.style.borderLeft = '';  // Remove spam border if present
            row.setAttribute('data-status', 'read');
        } else if (action === 'mark-spam') {
            console.log(`[ACTION LINKS] Updating row ${formId} for mark-spam action`);
            row.style.backgroundColor = '#FEE2E2';  // Red background for spam
            row.style.borderLeft = '4px solid #EF4444';  // Red left border
            row.setAttribute('data-status', 'spam');
        } else if (action === 'mark-new') {
            console.log(`[ACTION LINKS] Updating row ${formId} for mark-new action`);
            row.style.backgroundColor = '#DBEAFE';  // Blue background for new
            row.style.borderLeft = '';  // Remove spam border if present
            row.setAttribute('data-status', 'new');
        }
        
        // Allow the navigation to proceed (page will reload)
        console.log(`[ACTION LINKS] Allowing navigation to ${href}`);
    }, false);
    
    console.log('[ACTION LINKS] Action link handlers initialized');
}

function initializePage() {
    // Format dates in the table
    attachModalEventListeners();
    // Initialize delete form handlers
    initializeDeleteForms();
    // Initialize action link handlers for immediate visual feedback
    initializeActionLinkHandlers();
    console.log('Page initialized: date formatting, delete handlers, and action links applied');
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePage);
} else {
    initializePage();
}
