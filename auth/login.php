<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';

$auth = new Auth();
$errors = [];

if ($auth->checkAuth()) {
    header('Location: /profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']);

        $user = $auth->login($username, $password, $remember);
        header('Location: /profile.php');
        exit;
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>ورود به ابزارک</h2>
        
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
                <label for="username">نام کاربری یا ایمیل</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">رمز عبور</label>
                <input type="password" id="password" name="password" required>
                <small class="form-text">
                    <a href="forgot-password.php">فراموشی رمز عبور؟</a>
                </small>
            </div>

            <div class="form-group form-check">
                <input type="checkbox" id="remember" name="remember" class="form-check-input">
                <label for="remember" class="form-check-label">مرا به خاطر بسپار</label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">ورود</button>
        </form>

        <div class="auth-links">
            <span>حساب کاربری ندارید؟</span>
            <a href="register.php">ثبت‌نام کنید</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
