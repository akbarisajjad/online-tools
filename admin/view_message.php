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
    
    // دریافت پیام
    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$message_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$message) {
        header('Location: contact_messages.php');
        exit();
    }
    
    // علامت‌گذاری به عنوان خوانده شده
    if (!$message['is_read']) {
        $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$message_id]);
    }
} catch (PDOException $e) {
    die("خطا در ارتباط با پایگاه داده: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مشاهده پیام</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="content-header">
                <h1>مشاهده پیام</h1>
                <div class="header-actions">
                    <a href="contact_messages.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> بازگشت
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="message-header">
                    <div class="sender-info">
                        <h2><?php echo htmlspecialchars($message['subject']); ?></h2>
                        <div class="meta">
                            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($message['name']); ?></span>
                            <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($message['email']); ?></span>
                            <span><i class="fas fa-clock"></i> <?php echo jdate('Y/m/d H:i', strtotime($message['created_at'])); ?></span>
                            <span><i class="fas fa-globe"></i> <?php echo htmlspecialchars($message['ip_address']); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="message-content">
                    <div class="message-text">
                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                    </div>
                </div>
                
                <div class="message-actions">
                    <a href="reply_message.php?id=<?php echo $message['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-reply"></i> پاسخ
                    </a>
                    <a href="delete_message.php?id=<?php echo $message['id']; ?>" class="btn btn-danger" onclick="return confirm('آیا از حذف این پیام مطمئن هستید؟')">
                        <i class="fas fa-trash"></i> حذف
                    </a>
                </div>
            </div>
        </main>
    </div>
    
    <script src="/assets/js/admin.js"></script>
</body>
</html>
