<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");

// Создаем экземпляр класса DatabaseModel
$database = new DatabaseModel();

// SQL-запрос для подсчета количества строк в таблице "Application"
$sql = "SELECT COUNT(*) as count FROM Application";

$result = $database->query($sql);

// Получаем результат
$row = $result->fetch_assoc();

// Выводим количество строк
echo $row['count'];

// Закрываем соединение с базой данных
$database->close();