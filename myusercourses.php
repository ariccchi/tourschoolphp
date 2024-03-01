<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$json = file_get_contents('php://input');

// Преобразовать JSON в ассоциативный массив
$data = json_decode($json, true);

// Проверяем, установлен ли ключ 'user' в данных
if (!isset($data['user'])) {
    echo json_encode(["error" => "Не удалось получить 'user' из данных"]);
    exit;
}

// Получить user_id из данных
$user_id = $data['user'];

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// SQL query to get course information, count of lessons, and count of completed lessons based on user_id
$sql = "SELECT c.*, 
               COUNT(l.id) AS lesson_count,
               COUNT(ul.lesson_id) AS completed_lesson_count
        FROM courses c
        JOIN user_courses uc ON c.id = uc.course_id
        LEFT JOIN lessons l ON c.id = l.course_id
        LEFT JOIN user_progress ul ON l.id = ul.lesson_id AND uc.user_id = ul.user_id
        WHERE uc.user_id = ?
        GROUP BY c.id;
";

$stmt = $database->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();

$result = $stmt->get_result();

// Обработка результата запроса
if ($result->num_rows > 0) {
    // Обработка результатов запроса, например, преобразование в JSON и отправка клиенту
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($rows);
} else {
    // Если нет результатов, можно отправить пустой ответ или сообщение об ошибке
    echo json_encode(['error' => 'No courses found for the user']);
}

$stmt->close();
$database->close();
?>
