<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

$db = new DatabaseModel();
$json = file_get_contents('php://input');
$data = json_decode(file_get_contents('php://input'), true);

$user = isset($data['user']) ? $data['user'] : null;
$lesson = isset($data['lesson']) ? $data['lesson'] : null;
$date = isset($data['date']) ? $data['date'] : null;

if ($user === null || $lesson === null) {
    $response = array('success' => false, 'message' => 'testid or user is NULL');
    echo json_encode($response);
    exit();  // Stop execution if testid or user is NULL
}

// Получение lesson_id из таблицы lessons на основе title
$sqlGetLessonId = "SELECT id FROM lessons WHERE title = ?";
$stmtGetLessonId = $db->prepare($sqlGetLessonId);
$stmtGetLessonId->bind_param("s", $lesson);
$stmtGetLessonId->execute();
$resultLessonId = $stmtGetLessonId->get_result();

if ($resultLessonId->num_rows > 0) {
    $row = $resultLessonId->fetch_assoc();
    $lesson_id = $row['id'];

    // Проверка наличия записи в lesson_finished
    $sqlCheckUser = "SELECT * FROM lesson_finished WHERE lesson_id = ? AND user_id = ?";
    $stmtCheckUser = $db->prepare($sqlCheckUser);
    $stmtCheckUser->bind_param("ss", $lesson_id, $user);
    $stmtCheckUser->execute();
    $result = $stmtCheckUser->get_result();

    if ($result->num_rows == 0) {
        // Вставка новой записи
        $sqlInsertUser = "INSERT INTO lesson_finished (user_id, lesson_id, fin_date) VALUES (?, ?, ?)";
        $stmtInsertUser = $db->prepare($sqlInsertUser);
        $stmtInsertUser->bind_param("sss", $user, $lesson_id, $date);
        $stmtInsertUser->execute();

        $response = array('success' => true, 'message' => 'User inserted successfully');
        echo json_encode($response);
    } else {
        $response = array('success' => false, 'message' => 'User already exists');
        echo json_encode($response);
    }
} else {
    $response = array('success' => false, 'message' => 'Lesson not found');
    echo json_encode($response);
}
?>
