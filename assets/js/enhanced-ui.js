/**
 * VetClinic Enhanced UI/UX JavaScript
 * Smooth interactions and user experience enhancements
 */

(function () {
    'use strict';

    // ===========================
    // 1. TOAST NOTIFICATION SYSTEM
    // ===========================
    window.showToast = function (message, type = 'success', duration = 3000) {
        // Create toast container if it doesn't exist
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        // Icon mapping
        const icons = {
            success: '<i class="fas fa-check-circle"></i>',
            error: '<i class="fas fa-times-circle"></i>',
            warning: '<i class="fas fa-exclamation-triangle"></i>',
            info: '<i class="fas fa-info-circle"></i>'
        };

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-icon">${icons[type] || icons.info}</div>
            <div class="toast-message">${message}</div>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;

        container.appendChild(toast);

        // Trigger show animation
        setTimeout(() => toast.classList.add('show'), 10);

        // Auto dismiss
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };

    // ===========================
    // 2. REPLACE ALERT WITH TOAST - DISABLED (Annoying popups)
    // ===========================
    // Auto-toast conversion disabled to prevent annoying popup notifications
    // Users can still use showToast() manually if needed

    // ===========================
    // 3. SMOOTH FORM VALIDATION
    // ===========================
    window.validateForm = function (formElement) {
        let isValid = true;
        const inputs = formElement.querySelectorAll('[required]');

        inputs.forEach(input => {
            const formGroup = input.closest('.form-group');
            let errorElement = formGroup?.querySelector('.form-error');

            // Remove previous errors
            if (errorElement) {
                errorElement.remove();
            }
            input.classList.remove('error');

            // Check validity
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('error');

                if (formGroup) {
                    const error = document.createElement('div');
                    error.className = 'form-error';
                    error.innerHTML = `<i class="fas fa-exclamation-circle"></i> This field is required`;
                    formGroup.appendChild(error);
                }
            }
        });

        return isValid;
    };

    // DISABLED: Auto-attach validation was preventing form submissions
    // Forms now use native HTML5 validation instead
    // Keep validateForm() function available for manual use if needed
    /*
    document.addEventListener('DOMContentLoaded', function () {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function (e) {
                // Skip validation for delete forms or forms with novalidate
                if (this.hasAttribute('novalidate') || this.querySelector('[name="_method"][value="DELETE"]')) {
                    return true;
                }

                if (!validateForm(this)) {
                    e.preventDefault();
                    showToast('Please fill in all required fields', 'error');
                    return false;
                }
            });
        });
    });
    */

    // ===========================
    // 4. LOADING STATES
    // ===========================
    window.showLoading = function (element, text = 'Loading...') {
        const originalContent = element.innerHTML;
        element.dataset.originalContent = originalContent;
        element.disabled = true;
        element.innerHTML = `
            <span class="spinner spinner-sm"></span>
            <span>${text}</span>
        `;
    };

    window.hideLoading = function (element) {
        if (element.dataset.originalContent) {
            element.innerHTML = element.dataset.originalContent;
            element.disabled = false;
            delete element.dataset.originalContent;
        }
    };

    // DISABLED: Auto-attach loading state was interfering with forms
    // Can still manually call showLoading() and hideLoading() if needed
    /*
    document.addEventListener('DOMContentLoaded', function () {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                form.addEventListener('submit', function (e) {
                    // Don't show loading if validation failed (default prevented)
                    if (e.defaultPrevented) return;

                    // Double check validation just in case
                    if (typeof validateForm === 'function' && !validateForm(this)) {
                        return;
                    }

                    showLoading(submitBtn, 'Processing...');
                });
            }
        });
    });
    */

    // ===========================
    // 5. CONFIRM DIALOGS
    // ===========================
    window.confirmAction = function (message, callback) {
        if (confirm(message)) {
            if (typeof callback === 'function') {
                callback();
            }
            return true;
        }
        return false;
    };

    // Enhance delete buttons
    document.addEventListener('DOMContentLoaded', function () {
        const deleteButtons = document.querySelectorAll('[onclick*="confirmDelete"], .btn-danger, [class*="delete"]');
        deleteButtons.forEach(btn => {
            if (btn.onclick && btn.onclick.toString().includes('confirmDelete')) {
                return; // Skip if already has confirmDelete
            }

            const form = btn.closest('form');
            if (form && (form.method === 'POST' || form.querySelector('[name="_method"]'))) {
                btn.addEventListener('click', function (e) {
                    if (!this.hasAttribute('data-confirmed')) {
                        e.preventDefault();
                        if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                            this.setAttribute('data-confirmed', 'true');
                            this.click();
                        }
                    }
                });
            }
        });
    });

    // ===========================
    // 6. SMOOTH SCROLL
    // ===========================
    document.addEventListener('DOMContentLoaded', function () {
        const smoothScrollLinks = document.querySelectorAll('a[href^="#"]');
        smoothScrollLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    });

    // ===========================
    // 7. DARK MODE TOGGLE
    // ===========================
    window.toggleDarkMode = function () {
        const html = document.documentElement;
        const body = document.body;
        const toggle = document.getElementById('darkModeToggle');
        const isDark = html.getAttribute('data-theme') === 'dark';

        if (isDark) {
            // Switch to light mode
            html.removeAttribute('data-theme');
            body.removeAttribute('data-theme');
            localStorage.setItem('theme', 'light');
            if (toggle) {
                toggle.classList.remove('active');
            }
        } else {
            // Switch to dark mode
            html.setAttribute('data-theme', 'dark');
            body.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            if (toggle) {
                toggle.classList.add('active');
            }
        }
    };

    // Initialize theme immediately (before DOMContentLoaded)
    (function () {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            if (document.body) {
                document.body.setAttribute('data-theme', 'dark');
            }
        }
    })();

    // Apply saved theme on load and ensure toggle UI is synced
    document.addEventListener('DOMContentLoaded', function () {
        const savedTheme = localStorage.getItem('theme');
        const toggle = document.getElementById('darkModeToggle');

        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            if (document.body) {
                document.body.setAttribute('data-theme', 'dark');
            }
            if (toggle) {
                toggle.classList.add('active');
            }
        } else {
            // Ensure light mode is properly set
            document.documentElement.removeAttribute('data-theme');
            if (document.body) {
                document.body.removeAttribute('data-theme');
            }
            if (toggle) {
                toggle.classList.remove('active');
            }
        }
    });

    // ===========================
    // 8. TOOLTIP INITIALIZATION
    // ===========================
    document.addEventListener('DOMContentLoaded', function () {
        const elementsWithTitle = document.querySelectorAll('[title]');
        elementsWithTitle.forEach(el => {
            if (!el.classList.contains('tooltip')) {
                const title = el.getAttribute('title');
                el.removeAttribute('title');
                el.classList.add('tooltip');

                const tooltipText = document.createElement('span');
                tooltipText.className = 'tooltip-text';
                tooltipText.textContent = title;
                el.appendChild(tooltipText);
            }
        });
    });

    // ===========================
    // 9. AUTO-HIDE ALERTS
    // ===========================
    document.addEventListener('DOMContentLoaded', function () {
        const alerts = document.querySelectorAll('.alert, [class*="alert-"]');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    });

    // ===========================
    // 10. TABLE ENHANCEMENTS
    // ===========================
    document.addEventListener('DOMContentLoaded', function () {
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            // Add table class if not present
            if (!table.classList.contains('table')) {
                table.classList.add('table');
            }

            // Wrap in container if not wrapped
            if (!table.parentElement.classList.contains('table-container')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-container';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }

            // Add row click handler for detail links
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.style.cursor = 'pointer';
                row.addEventListener('click', function (e) {
                    // Don't trigger if clicking on a button or link
                    if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'A' && !e.target.closest('button') && !e.target.closest('a')) {
                        const detailLink = this.querySelector('a[href*="detail"]');
                        if (detailLink) {
                            detailLink.click();
                        }
                    }
                });
            });
        });
    });

    // ===========================
    // 11. CARD STAGGER ANIMATION
    // ===========================
    document.addEventListener('DOMContentLoaded', function () {
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.05}s`;
        });
    });

    // ===========================
    // 12. SEARCH ENHANCEMENT
    // ===========================
    window.initSearch = function (inputSelector, targetSelector) {
        const searchInput = document.querySelector(inputSelector);
        const searchTargets = document.querySelectorAll(targetSelector);

        if (!searchInput || !searchTargets.length) return;

        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase();

            searchTargets.forEach(target => {
                const text = target.textContent.toLowerCase();
                if (text.includes(query)) {
                    target.style.display = '';
                    target.style.animation = 'fadeIn 0.3s ease';
                } else {
                    target.style.display = 'none';
                }
            });

            // Show empty state if no results
            const visibleTargets = Array.from(searchTargets).filter(t => t.style.display !== 'none');
            if (visibleTargets.length === 0 && query) {
                showEmptyState(searchTargets[0].parentElement, 'No results found');
            } else {
                removeEmptyState(searchTargets[0].parentElement);
            }
        });
    };

    function showEmptyState(container, message) {
        if (container.querySelector('.empty-state')) return;

        const emptyState = document.createElement('div');
        emptyState.className = 'empty-state';
        emptyState.innerHTML = `
            <div class="empty-state-icon">
                <i class="fas fa-search"></i>
            </div>
            <h3>${message}</h3>
            <p>Try adjusting your search criteria</p>
        `;
        container.appendChild(emptyState);
    }

    function removeEmptyState(container) {
        const emptyState = container.querySelector('.empty-state');
        if (emptyState) {
            emptyState.remove();
        }
    }

    // ===========================
    // 13. PROGRESS BAR ANIMATION
    // ===========================
    window.animateProgress = function (element, targetPercent) {
        const progressBar = element.querySelector('.progress-bar');
        if (!progressBar) return;

        let current = 0;
        const increment = targetPercent / 50;

        const interval = setInterval(() => {
            current += increment;
            if (current >= targetPercent) {
                current = targetPercent;
                clearInterval(interval);
            }
            progressBar.style.width = current + '%';
        }, 20);
    };

    // Auto-animate progress bars on page load
    document.addEventListener('DOMContentLoaded', function () {
        const progressBars = document.querySelectorAll('.progress');
        progressBars.forEach(progress => {
            const bar = progress.querySelector('.progress-bar');
            if (bar) {
                const target = parseInt(bar.dataset.progress || bar.style.width) || 0;
                bar.style.width = '0%';
                setTimeout(() => animateProgress(progress, target), 100);
            }
        });
    });

    // ===========================
    // 14. COPY TO CLIPBOARD
    // ===========================
    window.copyToClipboard = function (text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Copied to clipboard!', 'success', 2000);
            });
        } else {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showToast('Copied to clipboard!', 'success', 2000);
        }
    };

    // ===========================
    // 15. KEYBOARD SHORTCUTS
    // ===========================
    document.addEventListener('keydown', function (e) {
        // Ctrl/Cmd + K for search focus
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[type="search"], input[name="search"]');
            if (searchInput) {
                searchInput.focus();
            }
        }

        // Escape to close modals/dropdowns
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal.show, .dropdown.show');
            modals.forEach(modal => modal.classList.remove('show'));
        }
    });

    // ===========================
    // 16. IMAGE LAZY LOADING
    // ===========================
    document.addEventListener('DOMContentLoaded', function () {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            const lazyImages = document.querySelectorAll('img.lazy, img[data-src]');
            lazyImages.forEach(img => imageObserver.observe(img));
        }
    });

    // ===========================
    // 17. PRINT FUNCTIONALITY
    // ===========================
    window.printSection = function (selector) {
        const element = document.querySelector(selector);
        if (!element) return;

        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print</title>');
        printWindow.document.write('<link rel="stylesheet" href="/assets/css/style.css">');
        printWindow.document.write('<link rel="stylesheet" href="/assets/css/enhanced-ui.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write(element.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    };

    // ===========================
    // 18. EXPOSE UTILITIES
    // ===========================
    window.VetClinicUI = {
        showToast,
        showLoading,
        hideLoading,
        confirmAction,
        toggleDarkMode,
        animateProgress,
        copyToClipboard,
        printSection,
        initSearch,
        validateForm
    };

    // Enhanced UI loaded silently
})();
