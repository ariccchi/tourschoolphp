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
$testid = isset($data['testid']) ? $data['testid'] : null;
$tries = isset($data['tries']) ? $data['tries'] : null;
$timestamp = isset($data['timestamp']) ? $data['timestamp'] : null;

// Check if testid or user is NULL
if ($testid === null || $user === null) {
    $response = array('success' => false, 'message' => 'testid or user is NULL');
    echo json_encode($response);
    exit();  // Stop execution if testid or user is NULL
}

$sqlCheckUser = "SELECT * FROM lessonresult WHERE test_id = ? AND user_id = ?";
$stmtCheckUser = $db->prepare($sqlCheckUser);
$stmtCheckUser->bind_param("ss", $testid, $user);
$stmtCheckUser->execute();
$result = $stmtCheckUser->get_result();

if ($result->num_rows == 0) {
    $sqlInsertUser = "INSERT INTO lessonresult (test_id, user_id, tries, date_time) VALUES (?, ?, ?, ?)";
    $stmtInsertUser = $db->prepare($sqlInsertUser);
    $stmtInsertUser->bind_param("ssss", $testid, $user, $tries, $timestamp);
    $stmtInsertUser->execute();

    $response = array('success' => true, 'message' => 'User inserted successfully');
    echo json_encode($response);
} else {
    $response = array('success' => false, 'message' => 'User already exists');
    echo json_encode($response);
}
?>
