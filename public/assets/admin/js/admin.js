/**
 * KVN Construction - Admin Panel JavaScript
 * Includes: dashboard widgets, table interactions, form validation
 */

(function() {
    'use strict';

    // =============================================
    // SIDEBAR TOGGLE
    // =============================================
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            if (window.matchMedia('(max-width: 991px)').matches) {
                sidebar.classList.toggle('active');
            } else {
                sidebar.classList.toggle('collapsed');
                document.body.classList.toggle('sidebar-collapsed');
            }
        });
    }

    // =============================================
    // TABLE CHECKBOX SELECTALL
    // =============================================
    document.querySelectorAll('.table-select-all').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const table = this.closest('table');
            if (table) {
                const checkboxes = table.querySelectorAll('.table-row-select');
                checkboxes.forEach(cb => cb.checked = this.checked);
            }
        });
    });

    // =============================================
    // CONFIRM DIALOGS
    // =============================================
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });

    // =============================================
    // AUTO-HIDE ALERTS
    // =============================================
    document.querySelectorAll('.alert-auto-hide').forEach(alert => {
        const duration = parseInt(alert.dataset.duration) || 5000;
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, duration);
    });

    // =============================================
    // SEARCH TABLE FILTER
    // =============================================
    document.querySelectorAll('[data-table-search]').forEach(input => {
        input.addEventListener('keyup', function() {
            const tableId = this.dataset.tableSearch;
            const table = document.getElementById(tableId);
            if (!table) return;
            
            const filter = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    });

    // =============================================
    // PASSWORD STRENGTH INDICATOR
    // =============================================
    document.querySelectorAll('[data-password-strength]').forEach(input => {
        const meter = document.getElementById(input.dataset.passwordStrength);
        if (!meter) return;
        
        input.addEventListener('keyup', function() {
            const val = this.value;
            let strength = 0;
            
            if (val.length >= 8) strength += 25;
            if (val.length >= 12) strength += 15;
            if (/[A-Z]/.test(val)) strength += 20;
            if (/[a-z]/.test(val)) strength += 10;
            if (/[0-9]/.test(val)) strength += 15;
            if (/[^A-Za-z0-9]/.test(val)) strength += 15;
            
            meter.value = Math.min(strength, 100);
            
            const label = meter.nextElementSibling;
            if (label && label.classList.contains('strength-label')) {
                if (strength < 40) label.textContent = 'Weak';
                else if (strength < 70) label.textContent = 'Medium';
                else label.textContent = 'Strong';
                
                label.style.color = strength < 40 ? '#dc2626' : strength < 70 ? '#f59e0b' : '#16a34a';
            }
        });
    });

    // =============================================
    // FORM VALIDATION HELPERS
    // =============================================
    document.querySelectorAll('form.needs-validation').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    });

    // =============================================
    // CHART.JS FALLBACK (simple stats display)
    // =============================================
    function initSimpleStats() {
        document.querySelectorAll('.stat-card').forEach(card => {
            const value = card.querySelector('.stat-value');
            if (value && value.dataset.count) {
                animateCount(value, parseInt(value.dataset.count));
            }
        });
    }

    function animateCount(el, target) {
        let current = 0;
        const increment = Math.ceil(target / 30);
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = current.toLocaleString('en-IN');
        }, 50);
    }

    if (document.readyState === 'complete') {
        initSimpleStats();
    } else {
        window.addEventListener('load', initSimpleStats);
    }

})();
