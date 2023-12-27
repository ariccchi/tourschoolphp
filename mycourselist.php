<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// Получаем данные из тела запроса
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->user_id)) {
    // Получаем ID пользователя из тела запроса
    $user_id = $data->user_id;

    // SQL-запрос для выбора всех данных из таблицы "courses", которые относятся к данному пользователю
    $sql = "SELECT courses.* FROM courses JOIN user_courses ON courses.id = user_courses.course_id WHERE user_courses.user_id = $user_id";
    $result = $database->query($sql);

    // Проверяем, есть ли результат
    if ($result->num_rows > 0) {
        // Создаем массив для хранения результатов
        $output = array();

        // Добавляем каждую строку в массив
        while($row = $result->fetch_assoc()) {
            $output[] = $row;
        }

        // Возвращаем данные в формате JSON
        echo json_encode($output);
    } else {
        echo "0 результатов";
    }
} else {
    echo "Ошибка: не предоставлен user_id.";
}

// Закрываем соединение с базой данных
$database->close();
?>
