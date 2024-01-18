<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once "DatabaseModel.php";
$db = new DatabaseModel();

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);

$sender_user_id = $request->sender_user_id;
$receiver_user_id = $request->receiver_user_id;

$sql = "SELECT sender_user_id, receiver_user_id, message_text, created_at, is_read FROM messages 
        WHERE ((receiver_user_id = ? AND sender_user_id = ?) OR (receiver_user_id = ? AND sender_user_id = ?))
        AND message_text <> ''";
$stmt = $db->prepare($sql);
$stmt->bind_param("iiii", $receiver_user_id, $sender_user_id, $sender_user_id, $receiver_user_id);
$stmt->execute();

$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($messages);
?>
