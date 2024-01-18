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
$lesson = $data['lesson'];

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// SQL-запрос для выбора новости с заданным title
$sql = "SELECT video FROM lessons WHERE title = ?";
$stmt = $database->prepare($sql);
$stmt->bind_param("s", $lesson);
$stmt->execute();

$result = $stmt->get_result();

// Проверяем, есть ли результат
// Проверяем, есть ли результат
if ($result) {
    $row = $result->fetch_assoc();
    if ($row !== null) {
        $videoPath = $row['video'];
        echo json_encode($videoPath);
    } else {
        echo json_encode(null);
    }
} else {
    echo json_encode(null);
}


// Закрываем соединение с базой данных
$database->close();
?>
