<?php
require_once 'config.php';

// تابع اتصال به پایگاه داده
function db_connect() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("خطا در اتصال به پایگاه داده: " . $e->getMessage());
    }
}

// تابع تغییر مسیر
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

// تابع جلوگیری از حملات XSS
function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// تابع تولید توکن CSRF
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// تابع اعتبارسنجی توکن CSRF
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// تابع لاگ خطاها
function log_error($error) {
    if (DEBUG_MODE) {
        error_log(date('[Y-m-d H:i:s] ') . $error . "\n", 3, 'logs/errors.log');
    }
}
?>
