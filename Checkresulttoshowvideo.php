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
$course = $data['course'];
$user_id = $data['user_id'];

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// SQL-запрос для выбора новости с заданным title
$stmt = $database->prepare("SELECT id FROM courses WHERE course_name = ?");
$stmt->bind_param("s", $course);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$course_id = $row['id'];

// Получаем order_number, уменьшаем его на 1 и выбираем соответствующий урок
$stmt = $database->prepare("SELECT id FROM lessons WHERE course_id = ? AND order_number = (SELECT MAX(order_number) - 1 FROM lessons WHERE title = ?)");
$stmt->bind_param("is", $course_id, $title);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$lesson_id = $row['id'];

// Получаем данные из таблицы user_progress
$stmt = $database->prepare("SELECT user_id, lesson_id, is_completed, completed_at, available_at, result FROM user_progress WHERE user_id = ? AND lesson_id = ?");
$stmt->bind_param("ii", $user_id, $lesson_id);
$stmt->execute();
$result = $stmt->get_result();

$responseData = array();

if ($result->num_rows > 0) {
    // Добавляем каждую строку в массив
    while($row = $result->fetch_assoc()) {
        $responseData[] = $row;
    }
} else {
    // Если результатов нет, добавляем сообщение в массив
    $responseData[] = array("message" => "0 results");
}

// Отправляем массив в формате JSON
echo json_encode($responseData);

// Закрываем соединение с базой данных
$database->close();
?>
