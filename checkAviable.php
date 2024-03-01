<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Check if 'user_id' is set in the decoded JSON data
if (!isset($data['user_id'])) {
    echo json_encode(["error" => "User ID not provided"]);
    exit;
}

$user_id = $data['user_id'];

// Create an instance of the DatabaseModel class
$database = new DatabaseModel();

// SQL query to select data
$sql = "SELECT 
            up.available_at, 
            c.course_name, 
            (SELECT l2.title 
             FROM lessons l2 
             WHERE l2.course_id = l1.course_id AND l2.order_number = l1.order_number + 1) as next_lesson_title
        FROM 
            user_progress up
        JOIN 
            lessons l1 ON up.lesson_id = l1.id
        JOIN 
            courses c ON l1.course_id = c.id
        WHERE 
            up.user_id = ?";

// Prepare the query
$stmt = $database->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Execute the query
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // Create an array to store all rows
    $rows = array();

    // Process each row
    while ($row = $result->fetch_assoc()) {
        // Add the row to the array
        $rows[] = $row;
    }

    // Return all rows in JSON format
    echo json_encode($rows);
} else {
    echo json_encode(["error" => "No data found"]);
}

// Close the database connection
$database->close();
