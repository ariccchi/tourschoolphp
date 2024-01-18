<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$json = file_get_contents('php://input');

// Преобразовать JSON в ассоциативный массив
$data = json_decode($json, true);

// Получаем данные из массива
$user_id = $data['user_id'];
$title = $data['lesson_id'];  // Заменили 'lesson_id' на 'title'
$is_completed = $data['is_completed'];
$result = $data['result'];

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

try {
    // Получаем lesson_id из таблицы lessons на основе переданного title
    $sql_get_lesson_id = "SELECT id FROM lessons WHERE title = ?";
    $stmt_get_lesson_id = $database->prepare($sql_get_lesson_id);
    $stmt_get_lesson_id->bind_param("s", $title);
    $stmt_get_lesson_id->execute();
    $stmt_get_lesson_id->bind_result($lesson_id);
    $stmt_get_lesson_id->fetch();
    $stmt_get_lesson_id->close();

    // Проверяем, есть ли уже запись с таким lesson_id в user_progress
    $sql_check_unique = "SELECT COUNT(*) FROM user_progress WHERE lesson_id = ?";
    $stmt_check_unique = $database->prepare($sql_check_unique);
    $stmt_check_unique->bind_param("i", $lesson_id);
    $stmt_check_unique->execute();
    $stmt_check_unique->bind_result($count);
    $stmt_check_unique->fetch();
    $stmt_check_unique->close();

    if ($count > 0) {
        echo json_encode(["error" => "Запись с lesson_id = {$lesson_id} уже существует"]);
    } else {
        // Заменяем 'completed_at' и 'available_at' на текущую дату и время сервера
        $completed_at = date('Y-m-d H:i:s');
        $available_at = date('Y-m-d H:i:s', strtotime($completed_at) + (15 * 60));  // +15 минут от completed_at

        // Выполняем запрос к базе данных для сохранения результатов
        $sql_insert_result = "INSERT INTO user_progress (user_id, lesson_id, is_completed, completed_at, available_at, result) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert_result = $database->prepare($sql_insert_result);
        $stmt_insert_result->bind_param("iiisss", $user_id, $lesson_id, $is_completed, $completed_at, $available_at, $result);
        $stmt_insert_result->execute();
        $stmt_insert_result->close();

        echo json_encode(["success" => "Результаты сохранены успешно!"]);
    }
} catch (Exception $e) {
    echo json_encode(["error" => "Ошибка при сохранении результатов: " . $e->getMessage()]);
}

$database->close();
?>
