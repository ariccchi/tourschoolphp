<?php 
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
$json = file_get_contents('php://input');

// Преобразовать JSON в ассоциативный массив
$data = json_decode($json, true);

// Проверяем, установлен ли ключ 'title' в данных
if (!isset($data['title'])) {
    echo json_encode(["error" => "Не удалось получить 'title' из данных"]);
    exit;
}

// Получить title из данных
$title = $data['title'];

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel(); 
$sql = "SELECT id, name, surname, avatar
FROM users;
";

$stmt = $database->prepare($sql);
$stmt->execute();

$result = $stmt->get_result();

// Проверяем, есть ли результат
if ($result->num_rows > 0) {
    // Инициализируем пустой массив для хранения всех строк
    $rows = [];

    // Извлекаем все строки
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    // Возвращаем данные в формате JSON
    echo json_encode($rows);
} else {
    echo json_encode(["error" => "Ученики не найдены"]);
}

// Закрываем соединение с базой данных
$database->close();
?>
