<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$json = file_get_contents('php://input');

// Преобразовать JSON в ассоциативный массив
$data = json_decode($json, true);

// Получить user_id и course_name из данных
$user_id = $data['user'];
$course_name = $data['course_name'];

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// SQL query to get lesson information and user progress based on user_id and course_name
$sql = "
    SELECT Lessons.id AS lesson_id, Lessons.title AS lesson_title, user_progress.user_id, user_progress.is_completed, user_progress.completed_at, user_progress.available_at, user_progress.result
    FROM Lessons
    LEFT JOIN user_progress ON Lessons.id = user_progress.lesson_id AND user_progress.user_id = ?
    WHERE Lessons.course_id = (SELECT id FROM Courses WHERE course_name = ?)
";

// Подготовка запроса
$stmt = $database->prepare($sql);

// Привязка параметров
$stmt->bind_param("ss", $user_id, $course_name);

// Выполнение запроса
$stmt->execute();

// Получение результата
$result = $stmt->get_result();

// Обработка результата запроса
if ($result) {
    // Обработка результатов запроса, например, преобразование в JSON и отправка клиенту
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($rows);
} else {
    // Если есть ошибка в запросе, можно отправить сообщение об ошибке
    echo json_encode(['error' => $stmt->error]);
}

// Закрытие запроса
$stmt->close();
$database->close();
?>
