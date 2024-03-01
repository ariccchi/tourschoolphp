<?php

require_once "DatabaseModel.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

$db = new DatabaseModel();

$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;

$file = $_FILES['image'];

if ($file['error'] == 0) {
    // Генерируем уникальное имя для файла
    $fileName = $file['name'];
    $newFileName = 'img/' . md5(time() . $fileName) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);

    // Перемещаем файл в папку 'img'
    $uploadPath = __DIR__ . '/' . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Файл успешно перемещен
        echo "Файл $fileName успешно загружен и сохранен в папке 'img'.\n";

        // Обновляем запись пользователя с указанным user_id
        $updateAvatarQuery = "UPDATE users SET avatar = ? WHERE id = ?";
        $stmtUpdateAvatar = $db->prepare($updateAvatarQuery);
        $stmtUpdateAvatar->bind_param("si", $newFileName, $user_id);
        $stmtUpdateAvatar->execute();
        $stmtUpdateAvatar->close();
    } else {
        // Во время перемещения файла произошла ошибка
        echo "Не удалось переместить файл $fileName в папку 'img'.\n";
    }
} else {
    // Во время загрузки файла произошла ошибка
    echo "Ошибка при загрузке файла $fileName с кодом: " . $file['error'] . "\n";
}
?>
