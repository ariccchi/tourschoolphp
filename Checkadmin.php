<?php
require_once "DatabaseModel.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$json = file_get_contents('php://input');

// Преобразовать JSON в ассоциативный массив
$data = json_decode($json, true);

// Проверка наличия ключа 'user' в массиве
if (isset($data['user'])) {
    // Получить user из данных
    $user = $data['user'];

    // Создаем экземпляр класса DatabaseModel
    $database = new DatabaseModel();

    // SQL query to get user information
    $sql = "SELECT role
            FROM users 
            WHERE id = ?";

    $stmt = $database->prepare($sql);
    $stmt->bind_param("i", $user);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Возвращаем роль пользователя в формате JSON
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Пользователь не найден"]);
    }

    $database->close();
} else {
    echo json_encode(["error" => "Отсутствует ключ 'user' в данных"]);
}
?>
