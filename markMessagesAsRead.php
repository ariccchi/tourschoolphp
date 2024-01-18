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

$sql = "UPDATE messages SET is_read = 1 WHERE receiver_user_id = ? AND sender_user_id = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("ii", $receiver_user_id, $sender_user_id);

$stmt->execute();

echo json_encode(['success' => true]);
?>
