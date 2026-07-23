/**
 * KVN Construction - Main JavaScript
 * Includes: navigation, toast notifications, loading states, responsive tables
 */

// =============================================
// TOAST NOTIFICATION SYSTEM
// =============================================
const Toast = {
    _container: null,

    _getContainer() {
        if (!this._container) {
            this._container = document.createElement('div');
            this._container.id = 'toast-container';
            this._container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 999999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                max-width: 400px;
            `;
            document.body.appendChild(this._container);
        }
        return this._container;
    },

    show(message, type = 'info', duration = 5000) {
        const container = this._getContainer();
        const toast = document.createElement('div');
        
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };

        const colors = {
            success: '#16a34a',
            error: '#dc2626',
            warning: '#f59e0b',
            info: '#2563eb'
        };

        toast.style.cssText = `
            background: white;
            border-left: 4px solid ${colors[type] || colors.info};
            border-radius: 8px;
            padding: 14px 18px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: #1e293b;
            transform: translateX(120%);
            transition: transform 0.3s ease, opacity 0.3s ease;
            opacity: 0;
            cursor: pointer;
        `;

        toast.innerHTML = `
            <span style="
                width: 28px;
                height: 28px;
                border-radius: 50%;
                background: ${colors[type] || colors.info}15;
                color: ${colors[type] || colors.info};
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 14px;
                flex-shrink: 0;
            ">${icons[type] || icons.info}</span>
            <span style="flex: 1;">${this._escapeHtml(message)}</span>
            <span style="color: #94a3b8; cursor: pointer; font-size: 18px; line-height: 1;">&times;</span>
        `;

        toast.addEventListener('click', () => this._dismiss(toast));
        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        });

        // Auto dismiss
        if (duration > 0) {
            setTimeout(() => this._dismiss(toast), duration);
        }

        return toast;
    },

    _dismiss(toast) {
        toast.style.transform = 'translateX(120%)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    },

    success(msg, duration) { return this.show(msg, 'success', duration); },
    error(msg, duration) { return this.show(msg, 'error', duration); },
    warning(msg, duration) { return this.show(msg, 'warning', duration); },
    info(msg, duration) { return this.show(msg, 'info', duration); },

    _escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
};

// =============================================
// LOADING STATES
// =============================================
const Loader = {
    show(container) {
        const target = container || document.body;
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner-ring"></div>
                <span class="loading-text">Loading...</span>
            </div>
        `;
        target.appendChild(overlay);
        return overlay;
    },

    hide(overlay) {
        if (overlay) {
            overlay.style.opacity = '0';
            setTimeout(() => overlay.remove(), 200);
        }
    },

    // Button loading state
    button(btn, isLoading = true, originalText = '') {
        if (isLoading) {
            btn._originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border-sm"></span> Processing...`;
            btn.style.opacity = '0.8';
        } else {
            btn.disabled = false;
            btn.innerHTML = btn._originalText || originalText;
            btn.style.opacity = '1';
        }
    }
};

// =============================================
// RESPONSIVE TABLE WRAPPER
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    // Subtle sticky-header state and a predictable mobile menu close action.
    const header = document.querySelector('.header');
    const updateHeaderState = () => {
        if (header) header.classList.toggle('is-scrolled', window.scrollY > 12);
    };
    updateHeaderState();
    window.addEventListener('scroll', updateHeaderState, { passive: true });

    document.querySelectorAll('#mainNavbar .nav-link').forEach(link => {
        link.addEventListener('click', () => {
            const menu = document.getElementById('mainNavbar');
            if (menu && menu.classList.contains('show') && window.bootstrap) {
                window.bootstrap.Collapse.getOrCreateInstance(menu).hide();
            }
        });
    });

    // Wrap all tables in responsive containers
    document.querySelectorAll('table:not(.no-responsive)').forEach(table => {
        if (!table.closest('.table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            wrapper.style.cssText = `
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin: 1rem 0;
            `;
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });

    // Initialize tooltips
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(el => {
        // Simple tooltip implementation
        el.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'custom-tooltip';
            tooltip.textContent = this.dataset.title || this.title;
            tooltip.style.cssText = `
                position: fixed;
                background: #1e293b;
                color: white;
                padding: 6px 12px;
                border-radius: 6px;
                font-size: 13px;
                z-index: 99999;
                pointer-events: none;
                max-width: 250px;
                white-space: nowrap;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            `;
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + rect.width/2 - tooltip.offsetWidth/2 + 'px';
            tooltip.style.top = rect.bottom + 8 + 'px';
            
            el._tooltip = tooltip;
        });
        
        el.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                this._tooltip = null;
            }
        });
    });
});

// =============================================
// FORM AUTO-SAVE INDICATOR
// =============================================
function initFormDirtyTracking(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    let isDirty = false;
    const warnMessage = 'You have unsaved changes.';
    
    form.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('change', () => { isDirty = true; });
        el.addEventListener('input', () => { isDirty = true; });
    });
    
    window.addEventListener('beforeunload', (e) => {
        if (isDirty) {
            e.preventDefault();
            e.returnValue = warnMessage;
            return warnMessage;
        }
    });
    
    form.addEventListener('submit', () => { isDirty = false; });
}

// =============================================
// EXPORT GLOBALLY
// =============================================
window.Toast = Toast;
window.Loader = Loader;
window.initFormDirtyTracking = initFormDirtyTracking;
