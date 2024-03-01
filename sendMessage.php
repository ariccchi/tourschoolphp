<?php
require_once "DatabaseModel.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

$senderId = isset($_POST['sender_user_id']) ? $_POST['sender_user_id'] : null;
$receiverId = isset($_POST['receiver_user_id']) ? $_POST['receiver_user_id'] : null;
$message = isset($_POST['message_text']) ? $_POST['message_text'] : null;
$read = isset($_POST['is_read']) ? $_POST['is_read'] : 0;

if (!$senderId || !$receiverId) {
    echo json_encode(['error' => 'Missing sender ID or receiver ID']);
    exit();
}

$db = new DatabaseModel();

// Check if a file is attached
$fileUploaded = false;
$fileName = null;

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Generate a unique name for the file with the "img/" prefix
    $newFileName = 'img/' . md5(time() . $senderId . $receiverId) . '.' . $fileExtension;

    // Move the file to the 'img' folder
    $uploadPath = __DIR__ . '/' . $newFileName; // Изменено путь
    move_uploaded_file($fileTmpPath, $uploadPath);

    $fileUploaded = true;
}

// Insert message into the database
$sql = "INSERT INTO messages (sender_user_id, receiver_user_id, message_text, is_read, file_name) VALUES (?, ?, ?, ?, ?)";
$stmt = $db->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Error preparing statement']);
    exit();
}

$stmt->bind_param("sssss", $senderId, $receiverId, $message, $read, $newFileName);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Error executing statement']);
    exit();
}

$stmt->close();

echo json_encode(['success' => true]);
?>
