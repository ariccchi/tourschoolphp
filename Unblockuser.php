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
if (!isset($data['user']) || empty($data['user']) || !isset($data['curatorid']) || empty($data['curatorid'])) {
    echo json_encode(["error" => "Отсутствуют необходимые данные"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Получить данные из параметров запроса
$user = $data['user'];
$curatorid = $data['curatorid'];

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// Подготовленный запрос на удаление данных из user_blocks
$sqlDeleteUserBlock = "DELETE FROM user_blocks WHERE user_id = ?";

$stmtDeleteUserBlock = $database->prepare($sqlDeleteUserBlock);
$stmtDeleteUserBlock->bind_param("s", $user);

// Выполнение подготовленного запроса
if ($stmtDeleteUserBlock->execute()) {
    echo json_encode(["success" => "Пользователь разблокирован"]);
} else {
    echo json_encode(["error" => "Ошибка при разблокировке пользователя"], JSON_UNESCAPED_UNICODE);
}

$database->close();
?>
