/**
 * Centralized Tooltip System
 * Provides consistent tooltip initialization and behavior across the entire project
 * 
 * Features:
 * - Global tooltip container (no duplication)
 * - Supports multiple data formats (title, data-tooltip, data-tooltip-json)
 * - Smart positioning (top, bottom, left, right)
 * - Collision detection and adjustment
 * - Smooth animations
 * - Touch-friendly
 * - Works with any element type
 */

(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        arrowSize: 6,
        padding: 10,
        showDelay: 200,
        hideDelay: 100,
        maxWidth: 300,
        position: 'top' // top, bottom, left, right
    };

    // Tooltip state
    let tooltipState = {
        currentElement: null,
        showTimeout: null,
        hideTimeout: null
    };

    /**
     * Initialize tooltip system
     */
    function initializeTooltipSystem() {
        // Create global tooltip container if it doesn't exist
        let container = document.getElementById('global-tooltip-container');
        if (!container) {
            container = createTooltipContainer();
            document.body.appendChild(container);
        }

        // Find all elements with tooltip attributes
        const tooltipElements = document.querySelectorAll('[data-tooltip], [title]:not([title=""])');
        tooltipElements.forEach((element) => {
            attachTooltipToElement(element);
        });

        // Support for dynamically added elements
        observeDOMChanges();
    }

    /**
     * Create the global tooltip container
     */
    function createTooltipContainer() {
        const container = document.createElement('div');
        container.id = 'global-tooltip-container';
        container.style.position = 'fixed';
        container.style.zIndex = '10000';
        container.style.pointerEvents = 'none';

        const tooltip = document.createElement('div');
        tooltip.id = 'global-tooltip';
        tooltip.style.display = 'none';
        tooltip.style.position = 'fixed';

        const arrow = document.createElement('div');
        arrow.id = 'global-arrow';
        arrow.style.display = 'none';
        arrow.style.position = 'fixed';

        container.appendChild(tooltip);
        container.appendChild(arrow);

        // Hover to keep tooltip visible
        container.addEventListener('mouseenter', () => clearTimeouts());
        container.addEventListener('mouseleave', hideTooltip);

        return container;
    }

    /**
     * Attach tooltip to an element
     */
    function attachTooltipToElement(element) {
        // Skip if already has tooltip listener
        if (element.hasAttribute('data-tooltip-attached')) {
            return;
        }

        element.setAttribute('data-tooltip-attached', 'true');

        element.addEventListener('mouseenter', () => {
            clearTimeouts();
            tooltipState.showTimeout = setTimeout(() => {
                showTooltip(element);
            }, CONFIG.showDelay);
        });

        element.addEventListener('mouseleave', () => {
            clearTimeouts();
            tooltipState.hideTimeout = setTimeout(hideTooltip, CONFIG.hideDelay);
        });

        // Support touch devices
        element.addEventListener('touchstart', (e) => {
            clearTimeouts();
            showTooltip(element);
        });

        element.addEventListener('touchend', () => {
            clearTimeouts();
            tooltipState.hideTimeout = setTimeout(hideTooltip, CONFIG.hideDelay);
        });
    }

    /**
     * Get tooltip content from various sources
     */
    function getTooltipContent(element) {
        // Priority 1: data-tooltip-json (for JSON array)
        if (element.hasAttribute('data-tooltip-json')) {
            try {
                const json = JSON.parse(element.getAttribute('data-tooltip-json'));
                return Array.isArray(json) ? json.join('\n') : json;
            } catch (e) {
                console.warn('Failed to parse data-tooltip-json:', e);
            }
        }

        // Priority 2: data-tooltip (direct text)
        if (element.hasAttribute('data-tooltip')) {
            return element.getAttribute('data-tooltip');
        }

        // Priority 3: title attribute (fallback for native tooltips)
        if (element.hasAttribute('title')) {
            const title = element.getAttribute('title');
            // Don't show empty titles
            if (title && title.trim()) {
                return title;
            }
        }

        return null;
    }

    /**
     * Show tooltip for an element
     */
    function showTooltip(element) {
        const content = getTooltipContent(element);
        if (!content) {
            return;
        }

        const tooltip = document.getElementById('global-tooltip');
        const arrow = document.getElementById('global-arrow');

        if (!tooltip || !arrow) {
            console.error('Tooltip elements not found');
            return;
        }

        // Set content
        tooltip.textContent = content;
        tooltip.style.display = 'block';
        tooltip.style.pointerEvents = 'auto';

        // Calculate position after element is rendered
        setTimeout(() => {
            positionTooltip(element, tooltip, arrow);
        }, 0);

        tooltipState.currentElement = element;
    }

    /**
     * Position tooltip intelligently
     */
    function positionTooltip(element, tooltip, arrow) {
        const elementRect = element.getBoundingClientRect();
        const tooltipHeight = tooltip.offsetHeight;
        const tooltipWidth = tooltip.offsetWidth;

        // Default position: top
        let tooltipLeft = elementRect.left + (elementRect.width / 2) - (tooltipWidth / 2);
        let tooltipTop = elementRect.top - tooltipHeight - CONFIG.arrowSize - CONFIG.padding;
        let position = 'top';

        // Check if tooltip goes off-screen and adjust
        if (tooltipTop < CONFIG.padding) {
            // Try bottom
            tooltipTop = elementRect.bottom + CONFIG.arrowSize + CONFIG.padding;
            position = 'bottom';
        }

        // Horizontal boundary checking
        if (tooltipLeft < CONFIG.padding) {
            tooltipLeft = CONFIG.padding;
        } else if (tooltipLeft + tooltipWidth > window.innerWidth - CONFIG.padding) {
            tooltipLeft = window.innerWidth - tooltipWidth - CONFIG.padding;
        }

        // Final bounds check on vertical
        if (tooltipTop < CONFIG.padding) {
            tooltipTop = CONFIG.padding;
        } else if (tooltipTop + tooltipHeight > window.innerHeight - CONFIG.padding) {
            tooltipTop = window.innerHeight - tooltipHeight - CONFIG.padding;
        }

        // Set tooltip position
        tooltip.style.left = tooltipLeft + 'px';
        tooltip.style.top = tooltipTop + 'px';

        // Position arrow
        const arrowLeft = elementRect.left + (elementRect.width / 2) - CONFIG.arrowSize;
        let arrowTop;

        if (position === 'top') {
            arrowTop = tooltipTop + tooltipHeight;
            arrow.style.borderTop = `6px solid #1F2937`;
            arrow.style.borderBottom = 'none';
        } else {
            arrowTop = tooltipTop - CONFIG.arrowSize;
            arrow.style.borderTop = 'none';
            arrow.style.borderBottom = `6px solid #1F2937`;
        }

        arrow.style.left = arrowLeft + 'px';
        arrow.style.top = arrowTop + 'px';
        arrow.style.display = 'block';
    }

    /**
     * Hide tooltip
     */
    function hideTooltip() {
        const tooltip = document.getElementById('global-tooltip');
        const arrow = document.getElementById('global-arrow');

        if (tooltip) {
            tooltip.style.display = 'none';
        }
        if (arrow) {
            arrow.style.display = 'none';
        }

        tooltipState.currentElement = null;
    }

    /**
     * Clear all timeouts
     */
    function clearTimeouts() {
        if (tooltipState.showTimeout) {
            clearTimeout(tooltipState.showTimeout);
        }
        if (tooltipState.hideTimeout) {
            clearTimeout(tooltipState.hideTimeout);
        }
    }

    /**
     * Observe DOM for dynamically added elements with tooltip attributes
     */
    function observeDOMChanges() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length) {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) { // Element node
                            // Check if the added node has tooltip attributes
                            if (node.hasAttribute && (node.hasAttribute('data-tooltip') || node.hasAttribute('title'))) {
                                attachTooltipToElement(node);
                            }

                            // Check children of added node
                            const tooltipElements = node.querySelectorAll?.('[data-tooltip], [title]');
                            if (tooltipElements) {
                                tooltipElements.forEach((element) => {
                                    attachTooltipToElement(element);
                                });
                            }
                        }
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Public API for manual initialization
     */
    window.TooltipSystem = {
        init: initializeTooltipSystem,
        attach: attachTooltipToElement,
        show: showTooltip,
        hide: hideTooltip,
        destroy: () => {
            document.querySelectorAll('[data-tooltip-attached]').forEach((el) => {
                el.removeAttribute('data-tooltip-attached');
            });
        }
    };

    /**
     * Auto-initialize on page load
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeTooltipSystem);
    } else {
        initializeTooltipSystem();
    }
})();
