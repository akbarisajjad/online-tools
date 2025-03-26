<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->checkAuth()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to save JWT tokens']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['jwt_token'])) {
    echo json_encode(['success' => false, 'message' => 'No JWT token provided']);
    exit;
}

try {
    $db = (new Database())->connect();
    $user_id = $auth->getUser()['id'];
    $title = $data['title'] ?? 'JWT Token';
    $jwt_token = $data['jwt_token'];
    
    $stmt = $db->prepare("INSERT INTO user_jwt_tokens (user_id, title, jwt_token) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $title, $jwt_token]);
    
    echo json_encode(['success' => true, 'message' => 'JWT token saved successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
