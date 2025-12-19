/**
 * Admin Panel JavaScript
 */

(function () {
    'use strict';

    // Sidebar Toggle
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarClose = document.getElementById('sidebar-close');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.add('active');
        });
    }

    if (sidebarClose) {
        sidebarClose.addEventListener('click', () => {
            sidebar.classList.remove('active');
        });
    }

    // Close sidebar on outside click (mobile)
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 1024) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });

    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Confirm delete actions
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // Form validation
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function (e) {
            let valid = true;

            this.querySelectorAll('[required]').forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('error');
                    field.style.borderColor = '#ef4444';
                } else {
                    field.classList.remove('error');
                    field.style.borderColor = '';
                }
            });

            if (!valid) {
                e.preventDefault();
            }
        });
    });

    // Image preview
    document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
        input.addEventListener('change', function () {
            const preview = document.querySelector(this.dataset.preview);
            if (preview && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // WYSIWYG Editor (if textarea has data-editor attribute)
    document.querySelectorAll('textarea[data-editor]').forEach(textarea => {
        // Simple auto-resize
        textarea.style.minHeight = '300px';
        textarea.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });

    // Select all checkbox
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.item-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }

    // Bulk actions
    const bulkForm = document.getElementById('bulk-form');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function (e) {
            const checked = document.querySelectorAll('.item-checkbox:checked');
            if (checked.length === 0) {
                e.preventDefault();
                alert('Please select at least one item.');
            }
        });
    }

    // Datepicker initialization (if element exists)
    document.querySelectorAll('[data-datepicker]').forEach(input => {
        input.type = 'date';
    });

    // Slug generator
    const titleInput = document.getElementById('title') || document.getElementById('title_en');
    const slugInput = document.getElementById('slug');

    if (titleInput && slugInput && !slugInput.value) {
        titleInput.addEventListener('input', function () {
            slugInput.value = this.value
                .toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        });
    }

    // Tab switching
    document.querySelectorAll('[data-tab]').forEach(tab => {
        tab.addEventListener('click', function () {
            const target = this.dataset.tab;

            // Update tab buttons
            this.closest('.tabs').querySelectorAll('[data-tab]').forEach(t => {
                t.classList.remove('active');
            });
            this.classList.add('active');

            // Update tab panels
            document.querySelectorAll('[data-tab-content]').forEach(panel => {
                panel.style.display = panel.dataset.tabContent === target ? 'block' : 'none';
            });
        });
    });

    // Ajax file upload with progress
    window.uploadFile = function (input, url, onProgress, onComplete) {
        const formData = new FormData();
        formData.append('file', input.files[0]);

        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function (e) {
            if (e.lengthComputable) {
                const percent = (e.loaded / e.total) * 100;
                if (onProgress) onProgress(percent);
            }
        });

        xhr.addEventListener('load', function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (onComplete) onComplete(response);
            }
        });

        xhr.open('POST', url, true);
        xhr.send(formData);
    };

    // Toast notifications
    window.showToast = function (message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type}`;
        toast.style.cssText = 'position:fixed;top:80px;right:20px;z-index:9999;min-width:300px;animation:slideIn 0.3s ease;';
        toast.textContent = message;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    };

    // Add animation keyframes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);

})();
