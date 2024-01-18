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

// Получить title из данных
$title = $data['title'];

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// SQL-запрос для выбора новости с заданным title
$sql = "SELECT lessons.id AS lesson_id, lessons.title AS lesson_title, order_number, user_progress.id, user_progress.is_completed, user_progress.completed_at, user_progress.available_at, user_progress.result FROM lessons JOIN courses ON lessons.course_id = courses.id LEFT JOIN user_progress ON lessons.id = user_progress.lesson_id AND user_progress.user_id = 26 WHERE courses.course_name = ? ";

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
