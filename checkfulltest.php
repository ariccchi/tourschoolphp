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
$title = isset($data['title']) ? $data['title'] : null;
$user_id = isset($data['user_id']) ? $data['user_id'] : null;
$course = isset($data['course']) ? $data['course'] : null;

// Проверка наличия необходимых данных
if ($title === null || $user_id === null || $course === null) {
    echo json_encode(["error" => "Отсутствуют необходимые данные"]);
    exit;
}

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// SQL-запрос для выбора новости с заданным title
$sql = "SELECT finaltest.* 
FROM finaltest 
JOIN lessons ON finaltest.lesson_id = lessons.id 
JOIN courses ON lessons.course_id = courses.id
WHERE lessons.title = ? AND courses.course_name = ?";


$stmt = $database->prepare($sql);
$stmt->bind_param("ss", $title, $course);
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
