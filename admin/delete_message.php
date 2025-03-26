<?php
require_once '../includes/admin_auth.php';
require_once '../includes/functions.php';

if (!hasPermission('admin')) {
    header('Location: /admin/');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: contact_messages.php');
    exit();
}

$message_id = (int)$_GET['id'];

try {
    $pdo = db_connect();
    
    // بررسی وجود پیام
    $stmt = $pdo->prepare("SELECT id FROM contact_messages WHERE id = ?");
    $stmt->execute([$message_id]);
    
    if ($stmt->fetch()) {
        // حذف پیام
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$message_id]);
        
        log_action("حذف پیام", "پیام با شناسه $message_id حذف شد");
    }
} catch (PDOException $e) {
    die("خطا در ارتباط با پایگاه داده: " . $e->getMessage());
}

header('Location: contact_messages.php');
exit();
?>
