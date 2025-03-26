<?php
/**
 * سیستم مدیریت پیام‌های فلش (Flash Messages)
 * نسخه: 2.1
 * 
 * این فایل برای نمایش پیام‌های موقت به کاربران استفاده می‌شود
 * مانند پیام‌های موفقیت، خطا، هشدار و اطلاعات
 */

// بررسی وجود پیام‌های فلش در session
$flash_messages = [];
if (isset($_SESSION['flash_messages']) {
    $flash_messages = $_SESSION['flash_messages'];
    unset($_SESSION['flash_messages']);
}

// بررسی پیام‌های فلش قدیمی (برای جلوگیری از نمایش دوباره)
if (isset($_SESSION['old_flash_messages'])) {
    unset($_SESSION['old_flash_messages']);
}

// ذخیره پیام‌های فعلی برای بررسی در درخواست بعدی
if (!empty($flash_messages)) {
    $_SESSION['old_flash_messages'] = $flash_messages;
}

// تابع نمایش پیام‌های فلش
function display_flash_messages() {
    global $flash_messages;
    
    if (empty($flash_messages)) {
        return;
    }
    
    $output = '';
    $icons = [
        'success' => 'check-circle',
        'error'   => 'times-circle',
        'warning' => 'exclamation-triangle',
        'info'    => 'info-circle',
        'primary' => 'info-circle',
        'secondary' => 'info-circle',
        'danger'  => 'times-circle'
    ];
    
    foreach ($flash_messages as $message) {
        $type = $message['type'] ?? 'info';
        $title = $message['title'] ?? '';
        $text = $message['message'] ?? '';
        $dismissible = isset($message['dismissible']) ? $message['dismissible'] : true;
        $icon = $icons[$type] ?? 'info-circle';
        
        $output .= '
        <div class="alert alert-' . htmlspecialchars($type) . ' alert-dismissible fade show flash-message" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-' . $icon . ' me-2"></i>
                <div>
                    ' . ($title ? '<strong>' . htmlspecialchars($title) . '</strong><br>' : '') . '
                    ' . htmlspecialchars($text) . '
                </div>
            </div>
            ' . ($dismissible ? '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>' : '') . '
        </div>';
    }
    
    echo $output;
}

// تابع تنظیم پیام فلش جدید
function set_flash_message($message, $type = 'info', $title = '', $dismissible = true) {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    
    $_SESSION['flash_messages'][] = [
        'message' => $message,
        'type' => $type,
        'title' => $title,
        'dismissible' => $dismissible
    ];
}

// توابع میانبر برای انواع پیام‌ها
function set_flash_success($message, $title = 'موفقیت', $dismissible = true) {
    set_flash_message($message, 'success', $title, $dismissible);
}

function set_flash_error($message, $title = 'خطا', $dismissible = true) {
    set_flash_message($message, 'error', $title, $dismissible);
}

function set_flash_warning($message, $title = 'هشدار', $dismissible = true) {
    set_flash_message($message, 'warning', $title, $dismissible);
}

function set_flash_info($message, $title = 'اطلاعات', $dismissible = true) {
    set_flash_message($message, 'info', $title, $dismissible);
}

// تابع بررسی وجود پیام فلش
function has_flash_message($type = null) {
    global $flash_messages;
    
    if (empty($flash_messages)) {
        return false;
    }
    
    if ($type === null) {
        return true;
    }
    
    foreach ($flash_messages as $message) {
        if ($message['type'] === $type) {
            return true;
        }
    }
    
    return false;
}

// تابع دریافت اولین پیام فلش از یک نوع خاص
function get_flash_message($type = null) {
    global $flash_messages;
    
    if (empty($flash_messages)) {
        return null;
    }
    
    if ($type === null) {
        return $flash_messages[0];
    }
    
    foreach ($flash_messages as $message) {
        if ($message['type'] === $type) {
            return $message;
        }
    }
    
    return null;
}

// نمایش خودکار پیام‌ها در صورت وجود
if (!defined('NO_AUTO_DISPLAY_FLASH') && !empty($flash_messages)) {
    // تعیین محل نمایش پیام‌ها
    $flash_container = defined('FLASH_CONTAINER') ? FLASH_CONTAINER : '.content-header';
    
    // اسکریپت نمایش پیام‌ها
    echo '
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const container = document.querySelector("' . $flash_container . '");
        if (container) {
            const flashHtml = `' . addslashes(str_replace(["\r", "\n"], '', display_flash_messages())) . '`;
            container.insertAdjacentHTML("afterend", flashHtml);
            
            // حذف خودکار پیام‌ها پس از 5 ثانیه
            setTimeout(() => {
                document.querySelectorAll(".flash-message").forEach(alert => {
                    alert.remove();
                });
            }, 5000);
        }
    });
    </script>';
}
