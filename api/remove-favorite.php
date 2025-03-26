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

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['tool_path'])) {
    echo json_encode(['success' => false, 'message' => 'مسیر ابزار مشخص نشده است']);
    exit;
}

$db = (new Database())->connect();
$stmt = $db->prepare("DELETE FROM user_favorites WHERE user_id = ? AND tool_path = ?");
$result = $stmt->execute([$auth->getUser()['id'], $data['tool_path']]);

echo json_encode(['success' => $result, 'message' => $result ? 'با موفقیت حذف شد' : 'خطا در حذف']);
?>
