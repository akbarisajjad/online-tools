<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->checkAuth()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to save JSON']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['json_data'])) {
    echo json_encode(['success' => false, 'message' => 'No JSON data provided']);
    exit;
}

try {
    // Validate the JSON again
    json_decode($data['json_data']);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }
    
    $db = (new Database())->connect();
    $user_id = $auth->getUser()['id'];
    $title = $data['title'] ?? 'Saved JSON';
    $json_data = $data['json_data'];
    
    $stmt = $db->prepare("INSERT INTO user_json_data (user_id, title, json_data) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $title, $json_data]);
    
    echo json_encode(['success' => true, 'message' => 'JSON saved successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
