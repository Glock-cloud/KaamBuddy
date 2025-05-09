/**
 * Show a notification message
 * @param {Object} options - Notification options
 * @param {string} options.type - Notification type: 'success', 'error', 'info', 'warning'
 * @param {string} options.title - Notification title
 * @param {string} options.message - Notification message
 * @param {boolean} options.persistent - Whether the notification should persist (not auto-hide)
 * @param {string} options.container - ID of the container element (default: 'notification-container')
 */
function showNotification(options) {
    // Default options
    const defaults = {
        type: 'info',
        title: '',
        message: '',
        persistent: false,
        container: 'notification-area'
    };
    
    // Merge options with defaults
    const settings = {...defaults, ...options};
    
    // Icons for different notification types
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        info: 'fa-info-circle',
        warning: 'fa-exclamation-triangle'
    };
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${settings.type}`;
    if (settings.persistent) {
        notification.classList.add('persistent');
    }
    
    // Add content to notification
    notification.innerHTML = `
        <div class="notification-icon">
            <i class="fas ${icons[settings.type]}"></i>
        </div>
        <div class="notification-content">
            ${settings.title ? `<h4 class="notification-title">${settings.title}</h4>` : ''}
            ${settings.message ? `<p class="notification-message">${settings.message}</p>` : ''}
        </div>
        <button class="notification-close">
            <i class="fas fa-times"></i>
        </button>
        ${!settings.persistent ? '<div class="notification-progress"></div>' : ''}
    `;
    
    // Get or create container element
    let container = document.getElementById(settings.container);
    if (!container) {
        container = document.createElement('div');
        container.id = settings.container;
        container.className = 'notification-container';
        document.body.appendChild(container);
    }
    
    // Add notification to container
    container.appendChild(notification);
    
    // Add event listener to close button
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.classList.add('remove');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
            
            // Remove container if it's empty
            if (container.children.length === 0) {
                container.parentNode.removeChild(container);
            }
        }, 500);
    });
    
    // Auto-remove after animation if not persistent
    if (!settings.persistent) {
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
            
            // Remove container if it's empty
            if (container.children.length === 0 && container.parentNode) {
                container.parentNode.removeChild(container);
            }
        }, 5000);
    }
    
    return notification;
}

// Initialize notifications from existing success/error boxes when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Convert success boxes
    const successBoxes = document.querySelectorAll('.success-box');
    successBoxes.forEach(box => {
        showNotification({
            type: 'success',
            message: box.textContent.trim(),
            title: 'Success'
        });
        box.style.display = 'none';  // Hide the original box
    });
    
    // Convert error boxes
    const errorBoxes = document.querySelectorAll('.error-box');
    errorBoxes.forEach(box => {
        showNotification({
            type: 'error',
            message: box.textContent.trim(),
            title: 'Error'
        });
        box.style.display = 'none';  // Hide the original box
    });
}); 