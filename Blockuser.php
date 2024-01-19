<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$json = file_get_contents('php://input');

// Преобразовать JSON в ассоциативный массив
$data = json_decode($json, true);

// Проверка наличия необходимых данных
if (!isset($data['user']) || empty($data['user']) || !isset($data['curatorid']) || empty($data['curatorid']) || !isset($data['blockReason']) || empty($data['blockReason'])) {
    echo json_encode(["error" => "Отсутствуют необходимые данные"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Получить данные из параметров запроса
$user = $data['user'];
$curatorid = $data['curatorid'];
$blockReason = $data['blockReason'];
$blockTimestamp = date("Y-m-d H:i:s");

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// Проверка, существует ли запись с таким user_id
$sqlCheckUserExists = "SELECT user_id FROM user_blocks WHERE user_id = ?";
$stmtCheckUserExists = $database->prepare($sqlCheckUserExists);
$stmtCheckUserExists->bind_param("s", $user);
$stmtCheckUserExists->execute();
$stmtCheckUserExists->store_result();

if ($stmtCheckUserExists->num_rows > 0) {
    echo json_encode(["error" => "Запись с таким user_id уже существует"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Подготовленный запрос на вставку данных в user_blocks
$sqlInsertUserBlock = "INSERT INTO user_blocks (user_id, block_reason, block_timestamp, admin_id)
                      VALUES (?, ?, ?, ?)";

$stmtInsertUserBlock = $database->prepare($sqlInsertUserBlock);
$stmtInsertUserBlock->bind_param("ssss", $user, $blockReason, $blockTimestamp, $curatorid);

// Выполнение подготовленного запроса
if ($stmtInsertUserBlock->execute()) {
    echo json_encode(["success" => "Пользователь заблокирован"]);
} else {
    echo json_encode(["error" => "Ошибка при блокировке пользователя"], JSON_UNESCAPED_UNICODE);
}

$database->close();
?>
