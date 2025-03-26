<?php
require_once 'db.php';

class Auth {
    private $db;
    private $session_lifetime = 30 * 24 * 60 * 60; // 30 روز

    public function __construct() {
        $this->db = (new Database())->connect();
        session_start();
    }

    public function register($username, $email, $password, $full_name = '') {
        // اعتبارسنجی ورودی‌ها
        if (empty($username) || empty($email) || empty($password)) {
            throw new Exception("تمام فیلدهای ضروری باید پر شوند");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("فرمت ایمیل نامعتبر است");
        }

        if (strlen($password) < 8) {
            throw new Exception("رمز عبور باید حداقل ۸ کاراکتر باشد");
        }

        // بررسی تکراری نبودن نام کاربری و ایمیل
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            throw new Exception("نام کاربری یا ایمیل قبلاً استفاده شده است");
        }

        // هش کردن رمز عبور
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // ایجاد کاربر جدید
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashed_password, $full_name]);

        return $this->db->lastInsertId();
    }

    public function login($username, $password, $remember = false) {
        // یافتن کاربر
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception("نام کاربری یا رمز عبور اشتباه است");
        }

        // ایجاد session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['logged_in'] = true;

        // اگر remember me زده شده بود، کوکی ایجاد کن
        if ($remember) {
            $this->createRememberToken($user['id']);
        }

        return $user;
    }

    private function createRememberToken($user_id) {
        $token = bin2hex(random_bytes(64));
        $expires = date('Y-m-d H:i:s', time() + $this->session_lifetime);

        // ذخیره توکن در دیتابیس
        $stmt = $this->db->prepare("INSERT INTO user_sessions (user_id, token, ip_address, user_agent, expires_at) 
                                   VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $token,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'],
            $expires
        ]);

        // تنظیم کوکی
        setcookie('remember_token', $token, time() + $this->session_lifetime, '/', '', true, true);
    }

    public function checkAuth() {
        // بررسی session
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
            return true;
        }

        // بررسی remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            return $this->validateRememberToken($_COOKIE['remember_token']);
        }

        return false;
    }

    private function validateRememberToken($token) {
        $stmt = $this->db->prepare("SELECT user_id FROM user_sessions 
                                   WHERE token = ? AND ip_address = ? AND user_agent = ? AND expires_at > NOW()");
        $stmt->execute([$token, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
        $session = $stmt->fetch();

        if ($session) {
            // کاربر را لاگین کن
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$session['user_id']]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['logged_in'] = true;
                return true;
            }
        }

        return false;
    }

    public function logout() {
        // حذف session
        session_unset();
        session_destroy();

        // حذف remember token
        if (isset($_COOKIE['remember_token'])) {
            $this->deleteRememberToken($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }

    private function deleteRememberToken($token) {
        $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE token = ?");
        $stmt->execute([$token]);
    }

    public function getUser() {
        if (!$this->checkAuth()) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }

    public function updateProfile($user_id, $data) {
        $allowed_fields = ['full_name', 'email', 'avatar'];
        $updates = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
                $updates[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($updates)) {
            throw new Exception("هیچ فیلدی برای به‌روزرسانی مشخص نشده است");
        }

        $params[] = $user_id;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function changePassword($user_id, $current_password, $new_password) {
        // دریافت رمز فعلی
        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current_password, $user['password'])) {
            throw new Exception("رمز عبور فعلی اشتباه است");
        }

        // به‌روزرسانی رمز جدید
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashed_password, $user_id]);
    }
}
?>
