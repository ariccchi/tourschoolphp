<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Получить JSON из тела запроса
$json = file_get_contents('php://input');

// Преобразовать JSON в ассоциативный массив
$data = json_decode($json, true);

// Проверить наличие необходимых ключей в массиве $data
if (!isset($data['user_id'], $data['lesson_id'])) {
    echo json_encode(["error" => "Отсутствуют необходимые данные"]);
    exit;
}

// Получить user_id и lesson_id из данных
$user_id = $data['user_id'];
$lesson_id = $data['lesson_id'];

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// SQL-запрос для проверки наличия записи с заданным user_id и lesson_id
$sql = "SELECT * FROM user_progress WHERE user_id = ? AND lesson_id = ?";
$stmt = $database->prepare($sql);
$stmt->bind_param("ii", $user_id, $lesson_id);
$stmt->execute();

$result = $stmt->get_result();

// Проверяем, есть ли результат
if ($result->num_rows > 0) {
    // Если запись существует, возвращаем {"exists": true}
    echo json_encode(["exists" => true]);
} else {
    // Если записи нет, возвращаем {"exists": false}
    echo json_encode(["exists" => false]);
}

// Закрываем соединение с базой данных
$database->close();
?>
