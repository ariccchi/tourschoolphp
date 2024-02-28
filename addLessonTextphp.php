<?php
require_once "DatabaseModel.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

$finarray = isset($_POST['finarray']) ? json_decode($_POST['finarray'], true) : null;
$name = isset($_POST['name']) ? $_POST['name'] : null;
$course = isset($_POST['course']) ? $_POST['course'] : null;
$lessonType = isset($_POST['lessontype']) ? $_POST['lessontype'] : null;

$db = new DatabaseModel();
$file = $_FILES['file'];
if ($file['error'] == 0) {
    // Генерируем уникальное имя для файла
    $fileName = $file['name'];
    $newFileName = 'img/' . md5(time() . $fileName) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);

    // Перемещаем файл в папку 'img'
    $uploadPath = __DIR__ . '/' . $newFileName;
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Файл успешно перемещен
        echo "Файл $fileName успешно загружен и сохранен в папке 'img'.\n";

        // Получаем время из POST-запроса
    
    } else {
        // Во время перемещения файла произошла ошибка
        echo "Не удалось переместить файл $fileName в папку 'img'.\n";
    }
} else {
    // Во время загрузки файла произошла ошибка
    echo "Ошибка при загрузке файла $fileName с кодом: " . $file['error'] . "\n";
}
// Получаем course_id по course_name
$query = "SELECT id FROM courses WHERE course_name = ?";
$stmtquerry = $db->prepare($query);
$stmtquerry->bind_param("s", $course);
$stmtquerry->execute();
$stmtquerry->bind_result($courseId);
$stmtquerry->fetch();
$stmtquerry->close();
// Определяем lesson_type
$lessonType2 = ($lessonType === 'video') ? 'videolesson' : 'textlesson';

$query = "
INSERT INTO lessons (course_id, title, video, order_number, lesson_type)
SELECT ?, ?, ?, COALESCE(MAX(order_number), 0) + 1, ?
FROM lessons
WHERE course_id = ?
";
$stmt = $db->prepare($query);
$stmt->bind_param("ssssi", $courseId, $name, $newFileName, $lessonType2, $courseId);
$stmt->execute();

$stmt->close();

$lessonIdQuery = "
    SELECT id
    FROM lessons
    WHERE course_id = ? AND title = ?
    LIMIT 1
";

$stmtLessonId = $db->prepare($lessonIdQuery);
$stmtLessonId->bind_param("ss", $courseId, $name);
$stmtLessonId->execute();
$stmtLessonId->bind_result($lessonId);
$stmtLessonId->fetch();
$stmtLessonId->close();


if (!empty($finarray)) {
    $insertFinQuery = "INSERT INTO finaltest (question,incorrect_answer1, incorrect_answer2, incorrect_answer3, correct_answer, lesson_id) VALUES (?, ?, ?, ?, ?, ?)";
    $insertFinStmt = $db->prepare($insertFinQuery);

    foreach ($finarray as $fin) {

        $insertFinStmt->bind_param(
            "sssssi",
            $fin['finQuestion'],
            $fin['finIncorrect1'],
            $fin['finIncorrect2'],
            $fin['finIncorrect3'],
            $fin['finCorrectAnswer'],
            $lessonId
        );
        $insertFinStmt->execute();
    }

    $insertFinStmt->close();
}


