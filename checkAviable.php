<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$user_id = $data['user_id'];

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// SQL-запрос для выбора данных
$sql = "SELECT 
            up.available_at, 
            c.course_name, 
            (SELECT l2.title 
             FROM lessons l2 
             WHERE l2.course_id = l1.course_id AND l2.order_number = l1.order_number + 1) as next_lesson_title
        FROM 
            user_progress up
        JOIN 
            lessons l1 ON up.lesson_id = l1.id
        JOIN 
            courses c ON l1.course_id = c.id
        WHERE 
            up.user_id = ?";

// Подготавливаем запрос
$stmt = $database->prepare($sql);
$stmt->bind_param("i", $user_id);
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
   echo json_encode(["error" => "Новость не найдена"]);
}

$database->close();
?>
