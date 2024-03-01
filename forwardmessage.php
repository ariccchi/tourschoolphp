<?php
require_once "DatabaseModel.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

$senderId = isset($_POST['sender_user_id']) ? $_POST['sender_user_id'] : null;
$receiverIds = isset($_POST['receiver_user_ids']) ? json_decode($_POST['receiver_user_ids']) : null;
$message = isset($_POST['message_text']) ? $_POST['message_text'] : null;
$read = isset($_POST['is_read']) ? $_POST['is_read'] : 0;
$fileName = isset($_POST['file']) ? $_POST['file'] : null;

if (!$senderId || !$receiverIds || empty($receiverIds)) {
    echo json_encode(['error' => 'Missing sender ID or receiver IDs']);
    exit();
}

$db = new DatabaseModel();

foreach ($receiverIds as $receiverId) {
    // Insert message into the database
    $sql = "INSERT INTO messages (sender_user_id, receiver_user_id,  is_read, file_name) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($sql);

    if (!$stmt) {
        echo json_encode(['error' => 'Error preparing statement']);
        exit();
    }

    $stmt->bind_param("ssss", $senderId, $receiverId, $read, $fileName);

    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Error executing statement']);
        exit();
    }

    $stmt->close();
}

echo json_encode(['success' => true]);
?>
