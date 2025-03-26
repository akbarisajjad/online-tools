<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';

$auth = new Auth();
$errors = [];
$success = false;

if ($auth->checkAuth()) {
    header('Location: /profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];
        $full_name = trim($_POST['full_name'] ?? '');

        if ($password !== $password_confirm) {
            throw new Exception("رمز عبور و تکرار آن مطابقت ندارند");
        }

        $auth->register($username, $email, $password, $full_name);
        $success = true;
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>ثبت‌نام در ابزارک</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                ثبت‌نام با موفقیت انجام شد. <a href="login.php">ورود به حساب کاربری</a>
            </div>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">نام کاربری</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">ایمیل</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="full_name">نام کامل (اختیاری)</label>
                    <input type="text" id="full_name" name="full_name"
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">رمز عبور</label>
                    <input type="password" id="password" name="password" required minlength="8">
                    <small class="form-text">حداقل ۸ کاراکتر</small>
                </div>

                <div class="form-group">
                    <label for="password_confirm">تکرار رمز عبور</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">ثبت‌نام</button>
            </form>

            <div class="auth-links">
                <span>قبلاً ثبت‌نام کرده‌اید؟</span>
                <a href="login.php">ورود به حساب کاربری</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
