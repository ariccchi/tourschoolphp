<?php
require_once "DatabaseModel.php";

// Разрешить запросы с любого источника (CORS)
header("Access-Control-Allow-Origin: *");

// Разрешить только POST и OPTIONS запросы
header("Access-Control-Allow-Methods: POST, OPTIONS");

// Разрешить заголовок Content-Type
header("Access-Control-Allow-Headers: Content-Type");

// Установить тип контента как JSON
header("Content-Type: application/json");

// Получить JSON из тела запроса
$json = file_get_contents('php://input');

// Преобразовать JSON в ассоциативный массив
$data = json_decode($json, true);

// Проверить, есть ли обязательные данные в запросе
if (!isset($data['user'])) {
    echo json_encode(["error" => "Отсутствует обязательный параметр 'user'"]);
    exit;
}

// Получить параметр 'user' из данных
$user = $data['user'];

// Создать экземпляр класса DatabaseModel
$database = new DatabaseModel();

// Подготовить запрос с использованием подготовленных выражений
$sql = "SELECT avatar FROM users WHERE id = ?";

$stmt = $database->prepare($sql);

// Привязать параметры
$stmt->bind_param("s", $user);

// Выполнить запрос
$stmt->execute();

// Получить результат запроса
$result = $stmt->get_result();

// Проверить, есть ли хотя бы одна строка
if ($result->num_rows > 0) {
    // Получить данные из результата
    $row = $result->fetch_assoc();

    // Отправить данные в формате JSON
    echo json_encode($row);
} else {
    // В случае отсутствия данных
    echo json_encode(["error" => "Пользователь не найден"]);
}

// Закрыть соединение с базой данных
$database->close();
?>
