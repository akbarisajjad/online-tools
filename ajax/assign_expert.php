<?php
require_once '../../includes/admin_auth.php';
require_once '../../includes/functions.php';
require_once '../../includes/db_advanced.php';

// بررسی درخواست AJAX
if (!is_ajax_request()) {
    die(json_encode([
        'success' => false,
        'message' => 'درخواست نامعتبر'
    ]));
}

// بررسی سطح دسترسی
if (!hasPermission('contact_manager')) {
    die(json_encode([
        'success' => false,
        'message' => 'دسترسی غیرمجاز'
    ]));
}

// اعتبارسنجی CSRF Token
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    die(json_encode([
        'success' => false,
        'message' => 'خطای امنیتی، لطفا دوباره تلاش کنید'
    ]));
}

// دریافت و اعتبارسنجی داده‌ها
$message_ids = explode(',', $_POST['message_ids'] ?? '');
$expert_id = (int) ($_POST['expert_id'] ?? 0);
$note = sanitize($_POST['note'] ?? '');

if (empty($message_ids) || empty($message_ids[0])) {
    die(json_encode([
        'success' => false,
        'message' => 'هیچ پیامی انتخاب نشده است'
    ]));
}

if ($expert_id <= 0) {
    die(json_encode([
        'success' => false,
        'message' => 'کارشناس معتبر انتخاب نشده است'
    ]));
}

try {
    $db = Database::getInstance();
    
    // بررسی وجود کارشناس
    $expert = $db->query("SELECT id, full_name FROM users WHERE id = ? AND role IN ('admin', 'contact_manager')", [$expert_id])->fetch();
    if (!$expert) {
        die(json_encode([
            'success' => false,
            'message' => 'کارشناس انتخاب شده معتبر نیست'
        ]));
    }
    
    // تبدیل پیام‌ها به اعداد صحیح
    $message_ids = array_map('intval', $message_ids);
    $placeholders = rtrim(str_repeat('?,', count($message_ids)), ',');
    
    // دریافت پیام‌های انتخابی
    $messages = $db->query("SELECT id, subject FROM contact_messages WHERE id IN ($placeholders)", $message_ids)->fetchAll();
    if (count($messages) !== count($message_ids)) {
        die(json_encode([
            'success' => false,
            'message' => 'برخی از پیام‌ها یافت نشدند'
        ]));
    }
    
    // شروع تراکنش
    $db->query("START TRANSACTION");
    
    // به‌روزرسانی پیام‌ها
    $update_stmt = $db->prepare("UPDATE contact_messages SET assigned_to = ?, status = 'pending' WHERE id = ?");
    
    foreach ($message_ids as $message_id) {
        $update_stmt->execute([$expert_id, $message_id]);
        
        // ثبت در تاریخچه
        $db->query(
            "INSERT INTO contact_history 
            (message_id, user_id, action_type, action_details, ip_address) 
            VALUES (?, ?, ?, ?, ?)",
            [
                $message_id,
                $_SESSION['user_id'],
                'assign',
                json_encode([
                    'expert_id' => $expert_id,
                    'expert_name' => $expert['full_name'],
                    'note' => $note
                ]),
                $_SERVER['REMOTE_ADDR']
            ]
        );
    }
    
    // ارسال نوتیفیکیشن
    $notification_content = sprintf(
        "شما %d پیام جدید برای بررسی دارید. موضوعات: %s",
        count($message_ids),
        implode('، ', array_column($messages, 'subject'))
    );
    
    $db->query(
        "INSERT INTO notifications 
        (user_id, title, content, type, is_read) 
        VALUES (?, ?, ?, ?, 0)",
        [
            $expert_id,
            'پیام‌های جدید ارجاع داده شده',
            $notification_content,
            'contact_assigned'
        ]
    );
    
    // ثبت لاگ
    log_action(
        "ارجاع پیام به کارشناس",
        sprintf(
            "کاربر %s %d پیام را به %s ارجاع داد. توضیحات: %s",
            $_SESSION['user_fullname'],
            count($message_ids),
            $expert['full_name'],
            $note
        )
    );
    
    $db->query("COMMIT");
    
    echo json_encode([
        'success' => true,
        'message' => sprintf(
            '%d پیام با موفقیت به %s ارجاع داده شد',
            count($message_ids),
            $expert['full_name']
        )
    ]);
    
} catch (PDOException $e) {
    $db->query("ROLLBACK");
    log_error("خطا در ارجاع پیام به کارشناس: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'خطای پایگاه داده، لطفا بعدا تلاش کنید'
    ]);
}
