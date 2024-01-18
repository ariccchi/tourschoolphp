<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$senderId = isset($data['sender_user_id']) ? $data['sender_user_id'] : null;
$receiverId = isset($data['receiver_user_id']) ? $data['receiver_user_id'] : null;
$message = isset($data['message_text']) ? $data['message_text'] : null;
$read = isset($data['is_read']) ? $data['is_read'] : 0;

if (!$senderId || !$receiverId || !$message) {
    echo json_encode(['error' => 'Missing sender ID, receiver ID or message']);
    exit();
}

$db = new DatabaseModel();

$sql = "INSERT INTO messages (sender_user_id, receiver_user_id, message_text, is_read) VALUES (?, ?, ?, ?)";
$stmt = $db->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Error preparing statement']);
    exit();
}

$stmt->bind_param("ssss", $senderId, $receiverId, $message, $read);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Error executing statement']);
    exit();
}

if ($stmt->affected_rows === 1) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Error: No rows affected']);
}

$stmt->close();
?>
