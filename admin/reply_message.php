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

// دریافت اطلاعات پیام اصلی
try {
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT name, email FROM contact_messages WHERE id = ?");
    $stmt->execute([$message_id]);
    $original_message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$original_message) {
        header('Location: contact_messages.php');
        exit();
    }
} catch (PDOException $e) {
    die("خطا در ارتباط با پایگاه داده: " . $e->getMessage());
}

// پردازش فرم پاسخ
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // اعتبارسنجی
    if (empty($subject)) {
        $errors['subject'] = 'لطفا موضوع پاسخ را وارد کنید';
    }
    
    if (empty($message)) {
        $errors['message'] = 'لطفا متن پاسخ را وارد کنید';
    }
    
    if (!validate_csrf_token($csrf_token)) {
        $errors['csrf'] = 'خطای امنیتی، لطفا دوباره تلاش کنید';
    }
    
    if (empty($errors)) {
        // ارسال ایمیل
        $to = $original_message['email'];
        $headers = "From: contact@example.com\r\n";
        $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
        $email_body = "پیام شما با موضوع: {$original_message['subject']}\n\n";
        $email_body .= "پاسخ ما:\n$message";
        
        // در محیط واقعی این خط را فعال کنید:
        // if (mail($to, $subject, $email_body, $headers)) {
        //     $success = true;
        // } else {
        //     $errors['email'] = 'خطا در ارسال ایمیل پاسخ';
        // }
        
        // برای تست:
        $success = true;
        log_action("پاسخ به پیام", "پاسخ به پیام ID: $message_id ارسال شد");
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پاسخ به پیام</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="content-header">
                <h1>پاسخ به پیام</h1>
                <div class="header-actions">
                    <a href="contact_messages.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> بازگشت
                    </a>
                </div>
            </div>
            
            <div class="card">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        پاسخ با موفقیت ارسال شد.
                    </div>
                    
                    <div class="text-center">
                        <a href="contact_messages.php" class="btn btn-primary">بازگشت به لیست پیام‌ها</a>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <?php if (!empty($errors['csrf'])): ?>
                            <div class="alert alert-error"><?php echo $errors['csrf']; ?></div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>به:</label>
                            <input type="text" value="<?php echo htmlspecialchars($original_message['name']); ?> &lt;<?php echo htmlspecialchars($original_message['email']); ?>&gt;" readonly>
                        </div>
                        
                        <div class="form-group <?php echo !empty($errors['subject']) ? 'has-error' : ''; ?>">
                            <label for="subject">موضوع:</label>
                            <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($_POST['subject'] ?? 'پاسخ به پیام شما'); ?>">
                            <?php if (!empty($errors['subject'])): ?>
                                <span class="error-message"><?php echo $errors['subject']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group <?php echo !empty($errors['message']) ? 'has-error' : ''; ?>">
                            <label for="message">متن پاسخ:</label>
                            <textarea id="message" name="message" rows="8"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            <?php if (!empty($errors['message'])): ?>
                                <span class="error-message"><?php echo $errors['message']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> ارسال پاسخ
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="/assets/js/admin.js"></script>
</body>
</html>
