<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

// پردازش فرم تماس
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // اعتبارسنجی
    if (empty($name)) {
        $errors['name'] = 'لطفا نام خود را وارد کنید';
    }
    
    if (empty($email)) {
        $errors['email'] = 'لطفا ایمیل خود را وارد کنید';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'ایمیل وارد شده معتبر نیست';
    }
    
    if (empty($subject)) {
        $errors['subject'] = 'لطفا موضوع پیام را انتخاب کنید';
    }
    
    if (empty($message)) {
        $errors['message'] = 'لطفا متن پیام را وارد کنید';
    } elseif (strlen($message) < 20) {
        $errors['message'] = 'پیام شما باید حداقل ۲۰ کاراکتر داشته باشد';
    }
    
    if (!validate_csrf_token($csrf_token)) {
        $errors['csrf'] = 'خطای امنیتی، لطفا دوباره تلاش کنید';
    }
    
    // اگر خطایی نبود
    if (empty($errors)) {
        // ذخیره در پایگاه داده
        try {
            $pdo = db_connect();
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, ip_address) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message, $_SERVER['REMOTE_ADDR']]);
            
            // ارسال ایمیل (شبیه‌سازی)
            $to = "contact@example.com";
            $headers = "From: $email\r\n";
            $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
            $email_body = "نام: $name\nایمیل: $email\nموضوع: $subject\n\nپیام:\n$message";
            
            // در محیط واقعی این خط را فعال کنید:
            // mail($to, "پیام جدید از فرم تماس: $subject", $email_body, $headers);
            
            $success = true;
        } catch (PDOException $e) {
            $errors['database'] = 'خطا در ارسال پیام، لطفا بعدا تلاش کنید';
            log_error("Contact Form Error: " . $e->getMessage());
        }
    }
}
?>

<div class="contact-page">
    <div class="contact-header">
        <div class="container">
            <h1>تماس با ما</h1>
            <p>برای پیگیری مشکلات، پیشنهادات و انتقادات با ما در ارتباط باشید</p>
        </div>
    </div>

    <div class="container">
        <div class="contact-wrapper">
            <div class="contact-form">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        پیام شما با موفقیت ارسال شد. در اسرع وقت پاسخ داده خواهد شد.
                    </div>
                <?php else: ?>
                    <form method="POST" id="contactForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <?php if (!empty($errors['csrf'])): ?>
                            <div class="alert alert-error"><?php echo $errors['csrf']; ?></div>
                        <?php endif; ?>
                        
                        <div class="form-group <?php echo !empty($errors['name']) ? 'has-error' : ''; ?>">
                            <label for="name">نام کامل</label>
                            <input type="text" id="name" name="name" value="<?php echo $_POST['name'] ?? ''; ?>">
                            <?php if (!empty($errors['name'])): ?>
                                <span class="error-message"><?php echo $errors['name']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group <?php echo !empty($errors['email']) ? 'has-error' : ''; ?>">
                            <label for="email">آدرس ایمیل</label>
                            <input type="email" id="email" name="email" value="<?php echo $_POST['email'] ?? ''; ?>">
                            <?php if (!empty($errors['email'])): ?>
                                <span class="error-message"><?php echo $errors['email']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group <?php echo !empty($errors['subject']) ? 'has-error' : ''; ?>">
                            <label for="subject">موضوع</label>
                            <select id="subject" name="subject">
                                <option value="">-- انتخاب کنید --</option>
                                <option value="پیشنهاد" <?php echo ($_POST['subject'] ?? '') === 'پیشنهاد' ? 'selected' : ''; ?>>پیشنهاد</option>
                                <option value="انتقاد" <?php echo ($_POST['subject'] ?? '') === 'انتقاد' ? 'selected' : ''; ?>>انتقاد</option>
                                <option value="پشتیبانی فنی" <?php echo ($_POST['subject'] ?? '') === 'پشتیبانی فنی' ? 'selected' : ''; ?>>پشتیبانی فنی</option>
                                <option value="همکاری" <?php echo ($_POST['subject'] ?? '') === 'همکاری' ? 'selected' : ''; ?>>همکاری</option>
                                <option value="سایر" <?php echo ($_POST['subject'] ?? '') === 'سایر' ? 'selected' : ''; ?>>سایر</option>
                            </select>
                            <?php if (!empty($errors['subject'])): ?>
                                <span class="error-message"><?php echo $errors['subject']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group <?php echo !empty($errors['message']) ? 'has-error' : ''; ?>">
                            <label for="message">متن پیام</label>
                            <textarea id="message" name="message" rows="5"><?php echo $_POST['message'] ?? ''; ?></textarea>
                            <?php if (!empty($errors['message'])): ?>
                                <span class="error-message"><?php echo $errors['message']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> ارسال پیام
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            
            <div class="contact-info">
                <div class="info-card">
                    <h3>اطلاعات تماس</h3>
                    
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>آدرس</h4>
                            <p>تهران، خیابان آزادی، دانشگاه تهران</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>ایمیل</h4>
                            <p>contact@example.com</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>تلفن</h4>
                            <p>+98 21 1234 5678</p>
                        </div>
                    </div>
                    
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-telegram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                
                <div class="map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3239.811057797602!2d51.38990131527062!3d35.70296698018798!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzXCsDQyJzEwLjciTiA1McKwMjMnMjguNiJF!5e0!3m2!1sen!2s!4v1620000000000!5m2!1sen!2s" 
                        width="100%" 
                        height="300" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/contact-form.js"></script>
<?php require_once 'includes/footer.php'; ?>
