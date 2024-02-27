<?php
require_once "DatabaseModel.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

$video = isset($_POST['video']) ? $_POST['video'] : null;

$terminarray = isset($_POST['terminarray']) ? json_decode($_POST['terminarray'], true) : null;

$infoarray = isset($_POST['infoarray']) ? json_decode($_POST['infoarray'], true) : null;

$testarray = isset($_POST['testarray']) ? json_decode($_POST['testarray'], true) : null;

$finarray = isset($_POST['finarray']) ? json_decode($_POST['finarray'], true) : null;


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


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Обрабатываем каждый загруженный файл
    foreach ($_FILES as $key => $file) {
        // Извлекаем индекс из ключа
        $index = str_replace('image_', '', $key);

        // Проверяем, были ли ошибки при загрузке файла
        if ($file['error'] == 0) {
            // Генерируем уникальное имя для файла
            $fileName = $file['name'];
            $newFileName = 'img/' . md5(time() . $fileName) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);

            // Перемещаем файл в папку 'img'
            $uploadPath = __DIR__ . '/' . $newFileName;
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Файл успешно перемещен
                echo "Файл $fileName успешно загружен.\n";

                // Получаем время из POST-запроса
                $timeInSeconds = $_POST['time_' . $index];
                $formattedTime = gmdate("H:i:s", $timeInSeconds);

                // Подготавливаем запрос на вставку данных в БД
                $insertContainerQuery = "INSERT INTO containers (lesson_id, text, time_in) VALUES (?, ?, ?)";
                $insertContainerStmt = $db->prepare($insertContainerQuery);

                // Привязываем параметры и выполняем запрос
                $insertContainerStmt->bind_param("iss", $lessonId, $newFileName, $formattedTime);
                $insertContainerStmt->execute();

                $insertContainerStmt->close();
            } else {
                // Во время перемещения файла произошла ошибка
                echo "Не удалось переместить файл $fileName.\n";
            }
        } else {
            // Во время загрузки файла произошла ошибка
            echo "Ошибка при загрузке файла $fileName.\n";
        }
    }
}
if (!empty($infoarray)) {
    $insertContainerQuery = "INSERT INTO containers (lesson_id, text, time_in) VALUES (?, ?, ?)";
    $insertContainerStmt = $db->prepare($insertContainerQuery);

    foreach ($infoarray as $info) {
        $timeInSeconds = $info['time'];
        $formattedTime = gmdate("H:i:s", $timeInSeconds);

        // Check if there are photos in the array
        // if (!empty($info['photos'])) {
        //     foreach ($info['photos'] as $photo) {
        //         $fileName = $photo->name;
        //         $fileSize = $photo->size;
        //         $fileType = $photo->type;
        //         $fileTmpName = $photo->tmp_name;
        
              
        //         $newFileName = 'img/' . md5(time() . $fileName) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
        //      $uploadPath = __DIR__ . '/' . $newFileName;
        //         if (move_uploaded_file($fileTmpName, $uploadPath)) {
              
        //             $info['text'] .= " $newFileName";
        //         } else {
             
        //             echo "File move failed.";
        //         }

        //         $info['text'] .= " $newFileName";
        //     }
        // }

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

        $insertTerminStmt->bind_param("iss", $lessonId, $termin['text'], $formattedTime);
        if (!$insertTerminStmt->execute()) {
            die("Ошибка выполнения запроса: " . $insertTerminStmt->error);
        }
    }

    $insertTerminStmt->close();
}

if (!empty($testarray)) {
    $insertTestQuery = "INSERT INTO test (question,incorrect_answer1, incorrect_answer2, incorrect_answer3, correct_answer, lesson_id, time_in) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insertTestStmt = $db->prepare($insertTestQuery);

    foreach ($testarray as $test) {
        $timeInSeconds = $test['time'];
        $formattedTime = gmdate("H:i:s", $timeInSeconds);

        $insertTestStmt->bind_param(
            "sssssis",
            $test['question'],
            $test['incorrect1'],
            $test['incorrect2'],
            $test['incorrect3'],
            $test['correctAnswer'],
            $lessonId,
            $formattedTime
        );
        $insertTestStmt->execute();
    }

    $insertTestStmt->close();
}

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

if (!empty($link)) {
    $insertlinkQuery = "INSERT INTO doplinks (lesson_id, text) VALUES (?, ?)";
    $insertlinkStmt = $db->prepare($insertlinkQuery);

    foreach ($link as $link) {
        $insertlinkStmt->bind_param("is",  $lessonId, $link['text']);
        $insertlinkStmt->execute();
    }

    $insertlinkStmt->close();
}
