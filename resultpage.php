<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$user_id = $data['user_id'];
$title = $data['title']; 

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// SQL-запрос для выбора данных
$sql = "
    SELECT up.id, up.user_id, up.lesson_id, up.is_completed, up.completed_at, up.available_at, up.result, l.title
    FROM user_progress up
    INNER JOIN lessons l ON up.lesson_id = l.id
    INNER JOIN courses c ON l.course_id = c.id
    WHERE up.user_id = ? AND c.course_name = ?
";

// Подготавливаем запрос
$stmt = $database->prepare($sql);
$stmt->bind_param("is", $user_id, $title);
$stmt->execute();

// Выполняем запрос
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // Создаем массив для хранения всех строк
    $rows = array();

    // Обрабатываем каждую строку
    while($row = $result->fetch_assoc()) {
        // Добавляем строку в массив
        $rows[] = $row;
    }

    // Возвращаем все строки в формате JSON
    echo json_encode($rows);
} else {
    echo json_encode(["error" => "Записей не найдено"]);
}

$database->close();
?>
