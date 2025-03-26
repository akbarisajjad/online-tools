<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';

$auth = new Auth();

if (!$auth->checkAuth()) {
    header('Location: /auth/login.php');
    exit;
}

$user = $auth->getUser();
$errors = [];
$success = false;

// پردازش فرم به‌روزرسانی پروفایل
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    try {
        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => trim($_POST['email'] ?? '')
        ];

        $auth->updateProfile($user['id'], $data);
        $success = true;
        $user = $auth->getUser(); // دریافت اطلاعات به‌روز شده
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

// پردازش فرم تغییر رمز عبور
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    try {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $new_password_confirm = $_POST['new_password_confirm'];

        if ($new_password !== $new_password_confirm) {
            throw new Exception("رمز عبور جدید و تکرار آن مطابقت ندارند");
        }

        $auth->changePassword($user['id'], $current_password, $new_password);
        $success = true;
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}
?>

<div class="profile-container">
    <div class="profile-sidebar">
        <div class="profile-avatar">
            <img src="<?php echo $user['avatar'] ?: 'assets/images/default-avatar.png'; ?>" alt="آواتار">
            <button class="btn btn-sm btn-outline-primary mt-2" id="change-avatar-btn">تغییر آواتار</button>
        </div>
        <h3><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></h3>
        <p class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></p>
        
        <ul class="profile-menu">
            <li class="active"><a href="#profile-info">اطلاعات پروفایل</a></li>
            <li><a href="#change-password">تغییر رمز عبور</a></li>
            <li><a href="#favorites">ابزارهای مورد علاقه</a></li>
            <li><a href="auth/logout.php">خروج</a></li>
        </ul>
    </div>

    <div class="profile-content">
        <?php if ($success): ?>
            <div class="alert alert-success">
                تغییرات با موفقیت ذخیره شد.
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section id="profile-info">
            <h4>اطلاعات پروفایل</h4>
            <form method="POST">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="form-group">
                    <label for="full_name">نام کامل</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="username">نام کاربری</label>
                    <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    <small class="form-text text-muted">نام کاربری قابل تغییر نیست</small>
                </div>

                <div class="form-group">
                    <label for="email">ایمیل</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>

                <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
            </form>
        </section>

        <section id="change-password" class="mt-5">
            <h4>تغییر رمز عبور</h4>
            <form method="POST">
                <input type="hidden" name="change_password" value="1">
                
                <div class="form-group">
                    <label for="current_password">رمز عبور فعلی</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">رمز عبور جدید</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8">
                </div>

                <div class="form-group">
                    <label for="new_password_confirm">تکرار رمز عبور جدید</label>
                    <input type="password" id="new_password_confirm" name="new_password_confirm" required>
                </div>

                <button type="submit" class="btn btn-primary">تغییر رمز عبور</button>
            </form>
        </section>

        <section id="favorites" class="mt-5">
            <h4>ابزارهای مورد علاقه</h4>
            <div class="favorites-list">
                <?php
                $stmt = $auth->getDb()->prepare("SELECT tool_path FROM user_favorites WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $favorites = $stmt->fetchAll();

                if (empty($favorites)): ?>
                    <p class="text-muted">هنوز ابزاری به مورد علاقه‌ها اضافه نکرده‌اید.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($favorites as $fav): 
                            $tool_name = basename($fav['tool_path'], '.php');
                            $tool_name = str_replace('-', ' ', $tool_name);
                            $tool_name = ucwords($tool_name);
                        ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="<?php echo htmlspecialchars($fav['tool_path']); ?>">
                                    <?php echo htmlspecialchars($tool_name); ?>
                                </a>
                                <button class="btn btn-sm btn-outline-danger remove-favorite" 
                                        data-path="<?php echo htmlspecialchars($fav['tool_path']); ?>">
                                    حذف
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<script src="assets/js/profile.js"></script>
<?php require_once 'includes/footer.php'; ?>
