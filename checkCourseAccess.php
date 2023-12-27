<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// Получаем данные из тела запроса
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->user_id) && !empty($data->course_name)) {
    // Получаем ID пользователя и ID курса из тела запроса
    $user_id = $data->user_id;
    $course_name = $data->course_name;

    // SQL-запрос для выбора всех данных из таблицы "user_courses", которые относятся к данному пользователю и данному курсу
    $sql = "SELECT * FROM user_courses
    JOIN courses ON user_courses.course_id = courses.id
    WHERE courses.course_name = '$course_name' AND user_courses.user_id = $user_id
    ";
    
    $result = $database->query($sql);

    // Проверяем, есть ли результат
    if ($result) {
        // Создаем массив для хранения результатов
        $output = array();

        // Добавляем каждую строку в массив
        while ($row = $result->fetch_assoc()) {
            $output[] = $row;
        }

        // Возвращаем данные в формате JSON
        echo json_encode($output);
    } else {
        // Если результатов нет, возвращаем пустой массив
        echo json_encode(array());
    }
} else {
    echo "Ошибка: не предоставлены user_id или course_id.";
}

// Закрываем соединение с базой данных
$database->close();
?>