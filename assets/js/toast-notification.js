/**
 * Toast Notification System
 * A modern, lightweight toast notification library with smooth animations
 *
 * Usage:
 *   Toast.show('Your message', 'success'); // success, error, info, warning
 *   Toast.success('Success message');
 *   Toast.error('Error message');
 *   Toast.info('Info message');
 *   Toast.warning('Warning message');
 */

const Toast = (() => {
  // Create or get the container
  let container = null;

  const getContainer = () => {
    if (!container) {
      container = document.createElement("div");
      container.className = "toast-container";
      document.body.appendChild(container);
    }
    return container;
  };

  // Icons for different toast types
  const icons = {
    success: '<i class="fa-solid fa-check-circle"></i>',
    error: '<i class="fa-solid fa-exclamation-circle"></i>',
    info: '<i class="fa-solid fa-info-circle"></i>',
    warning: '<i class="fa-solid fa-exclamation-triangle"></i>',
  };

  /**
   * Show a toast notification
   * @param {string} message - The message to display
   * @param {string} type - The type of toast: 'success', 'error', 'info', 'warning'
   * @param {number} duration - Duration in milliseconds (default: 3500)
   * @param {boolean} closeButton - Show close button (default: true)
   */
  const show = (
    message,
    type = "info",
    duration = 3500,
    closeButton = true,
  ) => {
    const container = getContainer();

    // Create toast element
    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;

    // Build HTML
    let html = `
         <div class="toast-icon">
            ${icons[type] || icons.info}
         </div>
         <div class="toast-message">${message}</div>
      `;

    if (closeButton) {
      html += `
            <button class="toast-close" type="button" aria-label="Close notification">
               <i class="fa-solid fa-times"></i>
            </button>
         `;
    }

    toast.innerHTML = html;

    // Add to container
    container.appendChild(toast);

    // Add close button functionality
    if (closeButton) {
      const closeBtn = toast.querySelector(".toast-close");
      closeBtn.addEventListener("click", () => {
        removeToast(toast);
      });
    }

    // Auto-remove after duration
    const timeoutId = setTimeout(() => {
      removeToast(toast);
    }, duration);

    // Store timeout ID for manual clearing if needed
    toast.dataset.timeoutId = timeoutId;

    return toast;
  };

  /**
   * Remove a toast with fade-out animation
   */
  const removeToast = (toast) => {
    if (toast.dataset.timeoutId) {
      clearTimeout(parseInt(toast.dataset.timeoutId));
    }

    toast.style.animation = "none";
    toast.style.opacity = "0";
    toast.style.transition = "opacity 0.3s ease, transform 0.3s ease";
    toast.style.transform = "translateY(20px)";

    setTimeout(() => {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 300);
  };

  /**
   * Show success toast
   */
  const success = (message, duration = 3500) => {
    return show(message, "success", duration, true);
  };

  /**
   * Show error toast
   */
  const error = (message, duration = 4000) => {
    return show(message, "error", duration, true);
  };

  /**
   * Show info toast
   */
  const info = (message, duration = 3500) => {
    return show(message, "info", duration, true);
  };

  /**
   * Show warning toast
   */
  const warning = (message, duration = 3500) => {
    return show(message, "warning", duration, true);
  };

  /**
   * Clear all toasts
   */
  const clearAll = () => {
    const container = getContainer();
    const toasts = container.querySelectorAll(".toast");
    toasts.forEach((toast) => {
      removeToast(toast);
    });
  };

  // Public API
  return {
    show,
    success,
    error,
    info,
    warning,
    clearAll,
  };
})();
