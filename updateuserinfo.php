<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$json = file_get_contents('php://input');

// Преобразовать JSON в ассоциативный массив
$data = json_decode($json, true);

// Получить данные из параметров запроса
$user = $data['user'];
$updatedData = $data['updatedData'];

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// Подготовленный запрос на обновление данных пользователя
// Подготовленный запрос на обновление данных пользователя
$sql = "UPDATE users
        SET name = COALESCE(?, name),
            surname = COALESCE(?, surname),
            email = COALESCE(?, email),
            birthdate = COALESCE(?, birthdate),
            role = COALESCE(?, role),
            city = COALESCE(?, city)
        WHERE id = ?";

$stmt = $database->prepare($sql);
$stmt->bind_param("ssssssi",
    $updatedData['name'],
    $updatedData['surname'],
    $updatedData['email'],
    $updatedData['birthdate'],
    $updatedData['role'],
    $updatedData['city'],
    $user
);

// Выполнение подготовленного запроса
if ($stmt->execute()) {
    echo json_encode(["success" => "Данные успешно обновлены"]);
} else {
    echo json_encode(["error" => "Ошибка при обновлении данных"]);
}

$database->close();
?>
