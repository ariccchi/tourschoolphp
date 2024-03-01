<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$json = file_get_contents('php://input');

// Преобразовать JSON в ассоциативный массив
$data = json_decode($json, true);

// Проверка наличия ключа 'id' в массиве
if (isset($data['id'])) {
    // Получить id из данных
    $id = $data['id'];

    // Создаем экземпляр класса DatabaseModel
    $database = new DatabaseModel();

    // SQL query to check user role
    $roleQuery = "SELECT role FROM users WHERE id = ?";
    $stmt = $database->prepare($roleQuery);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $role = $row['role'];

        echo json_encode(["status" => "active", "role" => $role]);
    } else {
        echo json_encode(["error" => "User not found"]);
    }

    $database->close();
} else {
    echo json_encode(["error" => "Отсутствует ключ 'id' в данных"]);
}
?>
