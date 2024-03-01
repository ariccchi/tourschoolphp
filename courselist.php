<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// SQL-запрос для выбора данных из таблицы "courses" и "lessons" с использованием JOIN
$sql = "SELECT courses.*, COUNT(lessons.id) AS lesson_count
        FROM courses
        LEFT JOIN lessons ON courses.id = lessons.course_id
        GROUP BY courses.id";

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

// Закрываем соединение с базой данных
$database->close();
?>
