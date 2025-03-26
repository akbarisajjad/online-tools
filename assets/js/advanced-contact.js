/**
 * مدیریت پیشرفته پیام‌های تماس - اسکریپت‌های فرانت‌اند
 * نسخه: 2.0
 * تاریخ آخرین بروزرسانی: 1402/05/15
 */

class ContactManager {
    constructor() {
        this.initSelectAll();
        this.initSorting();
        this.initBulkActions();
        this.initSingleMessageActions();
        this.initAjaxForms();
        this.initDatePickers();
        this.initTooltips();
    }

    // مقداردهی اولیه انتخاب همه/هیچ
    initSelectAll() {
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', () => {
                const checkboxes = document.querySelectorAll('.message-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = selectAll.checked;
                });
            });
        }
    }

    // مقداردهی اولیه سیستم مرتب‌سازی
    initSorting() {
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            sortSelect.addEventListener('change', () => {
                const url = new URL(window.location.href);
                url.searchParams.set('sort', sortSelect.value);
                window.location.href = url.toString();
            });
        }
    }

    // مقداردهی اولیه عملیات گروهی
    initBulkActions() {
        // ارجاع گروهی به کارشناس
        const assignBulk = document.getElementById('assignToExpertSelected');
        if (assignBulk) {
            assignBulk.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleBulkAction('assign');
            });
        }

        // تغییر اولویت گروهی
        const priorityBulk = document.getElementById('changePrioritySelected');
        if (priorityBulk) {
            priorityBulk.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleBulkAction('priority');
            });
        }

        // علامت‌گذاری به عنوان خوانده شده
        const readBulk = document.getElementById('markAsReadSelected');
        if (readBulk) {
            readBulk.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleBulkAction('read');
            });
        }

        // حذف گروهی
        const deleteBulk = document.getElementById('deleteSelected');
        if (deleteBulk) {
            deleteBulk.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleBulkAction('delete');
            });
        }
    }

    // مقداردهی اولیه عملیات تک پیام
    initSingleMessageActions() {
        // ارجاع تک پیام به کارشناس
        document.querySelectorAll('.assign-expert').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const messageId = btn.getAttribute('data-id');
                document.getElementById('assignMessageIds').value = messageId;
                $('#assignExpertModal').modal('show');
            });
        });

        // تغییر اولویت تک پیام
        document.querySelectorAll('.change-priority').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const messageId = btn.getAttribute('data-id');
                document.getElementById('priorityMessageIds').value = messageId;
                $('#changePriorityModal').modal('show');
            });
        });
    }

    // مقداردهی اولیه فرم‌های AJAX
    initAjaxForms() {
        // فرم ارجاع به کارشناس
        $('#assignExpertForm').on('submit', (e) => {
            e.preventDefault();
            this.submitAjaxForm(
                e.target, 
                'پیام‌ها با موفقیت ارجاع داده شدند',
                'خطا در ارجاع پیام‌ها',
                () => {
                    $('#assignExpertModal').modal('hide');
                    window.location.reload();
                }
            );
        });

        // فرم تغییر اولویت
        $('#changePriorityForm').on('submit', (e) => {
            e.preventDefault();
            this.submitAjaxForm(
                e.target, 
                'اولویت با موفقیت تغییر کرد',
                'خطا در تغییر اولویت',
                () => {
                    $('#changePriorityModal').modal('hide');
                    window.location.reload();
                }
            );
        });
    }

    // مقداردهی اولیه تاریخ‌پیکرها
    initDatePickers() {
        $('.datepicker').persianDatepicker({
            format: 'YYYY/MM/DD',
            observer: true,
            autoClose: true,
            toolbox: {
                enabled: true,
                calendarSwitch: {
                    enabled: true
                }
            }
        });
    }

    // مقداردهی اولیه tooltipها
    initTooltips() {
        $('[data-toggle="tooltip"]').tooltip({
            placement: 'top',
            trigger: 'hover'
        });
    }

    // مدیریت عملیات گروهی
    handleBulkAction(actionType) {
        const selectedIds = this.getSelectedMessageIds();
        
        if (selectedIds.length === 0) {
            this.showAlert('لطفا حداقل یک پیام را انتخاب کنید', 'warning');
            return;
        }

        switch (actionType) {
            case 'assign':
                document.getElementById('assignMessageIds').value = selectedIds.join(',');
                $('#assignExpertModal').modal('show');
                break;
                
            case 'priority':
                document.getElementById('priorityMessageIds').value = selectedIds.join(',');
                $('#changePriorityModal').modal('show');
                break;
                
            case 'read':
                if (confirm('آیا از علامت‌گذاری پیام‌های انتخاب شده به عنوان خوانده شده مطمئن هستید؟')) {
                    this.markAsRead(selectedIds);
                }
                break;
                
            case 'delete':
                if (confirm('آیا از حذف پیام‌های انتخاب شده مطمئن هستید؟ این عمل غیرقابل بازگشت است.')) {
                    this.deleteMessages(selectedIds);
                }
                break;
        }
    }

    // دریافت ID پیام‌های انتخاب شده
    getSelectedMessageIds() {
        const ids = [];
        document.querySelectorAll('.message-checkbox:checked').forEach(checkbox => {
            ids.push(checkbox.value);
        });
        return ids;
    }

    // علامت‌گذاری به عنوان خوانده شده
    markAsRead(ids) {
        fetch('ajax/mark_as_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `message_ids=${ids.join(',')}&csrf_token=${this.getCsrfToken()}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showAlert(data.message || 'پیام‌ها با موفقیت علامت‌گذاری شدند', 'success');
                window.location.reload();
            } else {
                throw new Error(data.message || 'خطا در پردازش درخواست');
            }
        })
        .catch(error => {
            this.showAlert(error.message, 'error');
        });
    }

    // حذف پیام‌ها
    deleteMessages(ids) {
        fetch('ajax/delete_messages.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `message_ids=${ids.join(',')}&csrf_token=${this.getCsrfToken()}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showAlert(data.message || 'پیام‌ها با موفقیت حذف شدند', 'success');
                window.location.reload();
            } else {
                throw new Error(data.message || 'خطا در پردازش درخواست');
            }
        })
        .catch(error => {
            this.showAlert(error.message, 'error');
        });
    }

    // ارسال فرم‌های AJAX
    submitAjaxForm(form, successMessage, errorMessage, callback) {
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showAlert(successMessage, 'success');
                if (typeof callback === 'function') {
                    callback();
                }
            } else {
                throw new Error(data.message || errorMessage);
            }
        })
        .catch(error => {
            this.showAlert(error.message, 'error');
        });
    }

    // نمایش اعلان
    showAlert(message, type = 'info') {
        const alertTypes = {
            success: 'success',
            error: 'danger',
            warning: 'warning',
            info: 'info'
        };

        const alertClass = `alert-${alertTypes[type] || 'info'}`;
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;

        // نمایش اعلان در بالای صفحه
        const alertContainer = document.getElementById('alert-container') || document.querySelector('.content-header');
        if (alertContainer) {
            alertContainer.insertAdjacentHTML('afterend', alertHtml);
            
            // حذف خودکار اعلان پس از 5 ثانیه
            setTimeout(() => {
                const alert = document.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        } else {
            alert(message);
        }
    }

    // دریافت توکن CSRF
    getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }
}

// راه‌اندازی مدیریت پیام‌ها پس از بارگذاری DOM
document.addEventListener('DOMContentLoaded', () => {
    new ContactManager();
    
    // مدیریت وضعیت خوانده شده/نشده در حالت hover
    const rows = document.querySelectorAll('#messagesTable tbody tr');
    rows.forEach(row => {
        if (row.classList.contains('unread')) {
            row.addEventListener('mouseenter', () => {
                row.classList.add('hover-unread');
            });
            
            row.addEventListener('mouseleave', () => {
                row.classList.remove('hover-unread');
            });
        }
    });
    
    // نمایش جزئیات پیام در حالت موبایل
    if (window.innerWidth < 768) {
        document.querySelectorAll('#messagesTable tbody tr').forEach(row => {
            row.addEventListener('click', (e) => {
                if (!e.target.closest('a, button, input')) {
                    const link = row.querySelector('td a');
                    if (link) {
                        window.location.href = link.href;
                    }
                }
            });
        });
    }
});
