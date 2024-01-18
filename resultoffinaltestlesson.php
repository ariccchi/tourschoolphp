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
$user_id = $data['user_id'];

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// SQL-запрос для выбора новости с заданным title
$sql = "SELECT r.id, r.test_id, r.user_id, r.tries, r.date_time,
t.id AS question_id, t.question, t.incorrect_answer1, t.incorrect_answer2,
t.incorrect_answer3, t.correct_answer, t.lesson_id 
FROM finaltestresult AS r
JOIN finaltest AS t ON r.test_id = t.id
JOIN lessons AS l ON l.id = t.lesson_id
WHERE r.user_id = ? AND l.title = ?";


$stmt = $database->prepare($sql);
$stmt->bind_param("ss", $user_id, $title);
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
