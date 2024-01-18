<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
date_default_timezone_set('Asia/Almaty'); 

// Создаем экземпляр класса DatabaseModel
$db = new DatabaseModel();
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$token = isset($data['token']) ? $data['token'] : null;

if ($token) {
    $sql = "UPDATE users SET refresh_token = NULL, access_token = NULL WHERE refresh_token = ?"; // Используйте метод escape, чтобы избежать SQL инъекций
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();

    $sqlCheckUser = "UPDATE users SET refresh_token = NULL, access_token = NULL WHERE refresh_token = ?";
    $stmtCheckUser = $db->prepare($sqlCheckUser);
    $stmtCheckUser->bind_param("s", $token);
    $stmtCheckUser->execute();
    $result = $stmtCheckUser->get_result();

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Token not provided']);
}
?>
