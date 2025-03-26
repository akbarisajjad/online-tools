<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->checkAuth()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'لطفاً وارد شوید']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'متد غیرمجاز']);
    exit;
}

if (empty($_FILES['avatar'])) {
    echo json_encode(['success' => false, 'message' => 'هیچ فایلی آپلود نشده است']);
    exit;
}

$file = $_FILES['avatar'];

// بررسی نوع فایل
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'فقط تصاویر JPEG, PNG و GIF مجاز هستند']);
    exit;
}

// بررسی حجم فایل (حداکثر ۲ مگابایت)
if ($file['size'] > 2 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'حجم فایل باید کمتر از ۲ مگابایت باشد']);
    exit;
}

// ایجاد نام منحصر به فرد برای فایل
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'avatar_' . $auth->getUser()['id'] . '_' . time() . '.' . $extension;
$upload_path = '../uploads/avatars/' . $filename;

// ایجاد دایرکتوری اگر وجود نداشت
if (!file_exists('../uploads/avatars')) {
    mkdir('../uploads/avatars', 0755, true);
}

// آپلود فایل
if (move_uploaded_file($file['tmp_name'], $upload_path)) {
    // آدرس نسبی برای ذخیره در دیتابیس
    $avatar_url = '/uploads/avatars/' . $filename;
    
    // به‌روزرسانی در دیتابیس
    $db = (new Database())->connect();
    $stmt = $db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
    $stmt->execute([$avatar_url, $auth->getUser()['id']]);
    
    echo json_encode(['success' => true, 'avatar_url' => $avatar_url]);
} else {
    echo json_encode(['success' => false, 'message' => 'خطا در آپلود تصویر']);
}
?>
