<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$json = file_get_contents('php://input');

// Преобразовать JSON в ассоциативный массив
$data = json_decode($json, true);

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

$sql = "SELECT u.id, u.name, u.surname, u.email, u.birthdate, u.registration_date, u.role, u.city, u.curator
        FROM users u";

$stmt = $database->prepare($sql);

if ($stmt->execute()) {
    // Получение результата
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Создаем массив для хранения всех строк
        $rows = array();

        // Обрабатываем каждую строку результата запроса
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        // Возвращаем все строки в формате JSON
        echo json_encode($rows);
    } else {
        echo json_encode(["error" => "Нет данных в таблице users"]);
    }
} else {
    echo json_encode(["error" => "Ошибка выполнения запроса: " . $stmt->error]);
}

$database->close();
?>
