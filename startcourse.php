<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$json = file_get_contents('php://input');

// Преобразовать JSON в ассоциативный массив
$data = json_decode($json, true);

$user_id = $data['user_id'] ?? null;
$course_id = $data['course_id'] ?? null;
$progress = 0;

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

if (isset($user_id) && isset($course_id)) {
    // Проверить, существует ли уже такая запись
    $sqlCheckExistence = "SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?";
    $stmtCheckExistence = $database->prepare($sqlCheckExistence);
    $stmtCheckExistence->bind_param("ss", $user_id, $course_id);
    $stmtCheckExistence->execute();
    $result = $stmtCheckExistence->get_result();

    if ($result->num_rows > 0) {
        // Запись уже существует
        echo json_encode([
            'success' => false,
            'message' => 'Запись уже существует',
        ]);
    } else {
        // Запись не существует, вставить новую запись
        $sqlInsertUser = "INSERT INTO user_courses (user_id, course_id, progress) VALUES (?, ?, ?)";
        $stmtInsertUser = $database->prepare($sqlInsertUser);
        $bindResult = $stmtInsertUser->bind_param("sss", $user_id, $course_id, $progress);

        if ($bindResult === false) {
            die('Ошибка при привязке параметров: ' . $stmtInsertUser->error);
        }

        $executeResult = $stmtInsertUser->execute();

        if ($executeResult === false) {
            die('Ошибка при выполнении запроса: ' . $stmtInsertUser->error);
        }
    }
} else {
    // Не отправлять запрос
    echo json_encode([
        'success' => false,
        'message' => 'Данные не заполнены',
    ]);
}
?>
