<?php
require_once "DatabaseModel.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

$video = isset($_POST['video']) ? $_POST['video'] : null;
$terminarray = isset($_POST['terminarray']) ? json_decode($_POST['terminarray']) : null;
$infoarray = isset($_POST['infoarray']) ? json_decode($_POST['infoarray'], true) : null;

$testarray = isset($_POST['testarray']) ? json_decode($_POST['testarray'], true) : null;

$link = isset($_POST['link']) ? json_decode($_POST['link'], true) : null;

$name = isset($_POST['name']) ? $_POST['name'] : null;
$course = isset($_POST['course']) ? $_POST['course'] : null;
$lessonType = isset($_POST['lessontype']) ? $_POST['lessontype'] : null;

$db = new DatabaseModel();

// Получаем course_id по course_name
$query = "SELECT id FROM courses WHERE course_name = ?";
$stmtquerry = $db->prepare($query);
$stmtquerry->bind_param("s", $course);
$stmtquerry->execute();
$stmtquerry->bind_result($courseId);
$stmtquerry->fetch();
$stmtquerry->close();

// if (!$courseId) {
//     // Обработка случая, если не удалось найти course_id
//     die(json_encode(['error' => 'Course not found']));
// }

// Определяем lesson_type
$lessonType2 = ($lessonType === 'video') ? 'videolesson' : 'textlesson';

// Получаем максимальный order_number по course_id и вставляем новый урок
$query = "
    INSERT INTO lessons (course_id, title, video, order_number, lesson_type)
    SELECT ?, ?, ?, COALESCE(MAX(order_number), 0) + 1, ?
    FROM lessons
    WHERE course_id = ?
";
$stmt = $db->prepare($query);
$stmt->bind_param("ssssi", $courseId, $name, $video, $lessonType2, $courseId);
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


// Вставляем данные из infoarray в таблицу containers
if (!empty($infoarray)) {
    $insertContainerQuery = "INSERT INTO containers (lesson_id, text, time_in) VALUES (?, ?, ?)";
    $insertContainerStmt = $db->prepare($insertContainerQuery);

    foreach ($infoarray as $info) {
        $timeInSeconds = $info['time'];
        $formattedTime = gmdate("H:i:s", $timeInSeconds);

        $insertContainerStmt->bind_param("iss", $lessonId, $info['text'], $formattedTime);
        $insertContainerStmt->execute();
    }

    $insertContainerStmt->close();
}

if (!empty($terminarray)) {
    $insertTerminQuery = "INSERT INTO lessontermins (lesson_id, text, time_in) VALUES (?, ?, ?)";
    $insertTerminStmt = $db->prepare($insertTerminQuery);

    foreach ($terminarray as $termin) {
        $timeInSeconds = $termin['time'];
        $formattedTime = gmdate("H:i:s", $timeInSeconds);

        $insertTerminStmt->bind_param("iss", $lessonId, $termin['text'], $formattedTime); // Используйте insertTerminStmt здесь
        $insertTerminStmt->execute(); // Используйте insertTerminStmt здесь
    }

    $insertTerminStmt->close(); // Используйте insertTerminStmt здесь
}

if (!empty($testarray)) {
    $insertTestQuery = "INSERT INTO test (question,incorrect_answer1, incorrect_answer2, incorrect_answer3, correct_answer, lesson_id, time_in) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insertTestStmt = $db->prepare($insertTestQuery);

    foreach ($testarray as $test) {
        $timeInSeconds = $test['time'];
        $formattedTime = gmdate("H:i:s", $timeInSeconds);

        $insertTestStmt->bind_param("sssssis", $test['question'], $test['incorrect1'], $test['incorrect2'], $test['incorrect3'],
        $test['correctAnswer'], $lessonId, $formattedTime);
        $insertTestStmt->execute();
    }

    $insertTestStmt->close();
}

if (!empty($link)) {
    $insertlinkQuery = "INSERT INTO doplinks (lesson_id, text) VALUES (?, ?)";
    $insertlinkStmt = $db->prepare($insertlinkQuery);

    foreach ($linkarray as $link) {
        $insertlinkStmt->bind_param("is",  $lessonId, $link['text']);
        $insertlinkStmt->execute();
    }

    $insertlinkStmt->close();
}

echo json_encode(['success' => true]);
?>
