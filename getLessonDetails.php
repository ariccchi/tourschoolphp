<?php
require_once "DatabaseModel.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$json = file_get_contents('php://input');

// Преобразовать JSON в ассоциативный массив
$data = json_decode($json, true);

// Получить lesson_id из данных
$lesson_id = $data['lesson_id'];
$user_id = $data['user_id'];

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

$sql = "SELECT id, lesson_id, is_completed, completed_at, available_at, result
        FROM user_progress
        WHERE lesson_id = ? AND user_id =?";
$stmt = $database->prepare($sql);
$stmt->bind_param("ii", $lesson_id, $user_id); // "i" означает, что это integer
$stmt->execute();

$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // Создаем массив для хранения всех строк
    $rows = array();

    // Обрабатываем каждую строку
    while ($row = $result->fetch_assoc()) {
        // Добавляем строку в массив
        $rows[] = $row;
    }

    // Возвращаем все строки в формате JSON
    echo json_encode($rows);
} else {
    echo json_encode(["error" => "Данные урока не найдены"]);
}

$database->close();
?>
