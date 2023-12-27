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

if (isset($data['title'])) {
    $title = $data['title'];
} else {
    // Handle the error appropriately
    echo json_encode(["error" => "Title not provided"]);
    exit();
}
error_log(print_r($data, true));

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// SQL-запрос для выбора новости с заданным title
$sql = "SELECT * FROM courses WHERE course_name = ?;
";
$stmt = $database->prepare($sql);
$stmt->bind_param("s", $title);
$stmt->execute();

$result = $stmt->get_result();

// Проверяем, есть ли результат
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
    echo json_encode(["error" => "Курс не найдена"]);
}


// Закрываем соединение с базой данных
$database->close();
?>
