<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json"); 

$json = file_get_contents('php://input');

// Преобразовать JSON в ассоциативный массив
$data = json_decode($json, true);

// Получить title из данных
$lesson = $data['lesson'];

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

        $sql = "SELECT Containers.* FROM Containers
                JOIN lessons ON Containers.lesson_id = lessons.id
                WHERE lessons.title = ? "; // Используйте метод escape, чтобы избежать SQL инъекций
     $stmt = $database->prepare($sql);
     $stmt->bind_param("s", $lesson);
     $stmt->execute();
     
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
    