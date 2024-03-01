<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Get JSON data from the request
$json = file_get_contents('php://input');

// Decode JSON into an associative array
$data = json_decode($json, true);

// Check if the 'user' key is present in the data
if (isset($data['user'])) {
    // Get the value of the 'user' key
    $user = $data['user'];

    // Create an instance of the DatabaseModel class
    $database = new DatabaseModel();

    // SQL query to select data based on the 'curator' column
    $sql = "SELECT u.id, u.name, u.surname, u.email, u.birthdate, u.registration_date, u.role, u.city, u.curator
            FROM users u
            WHERE curator = ?";

    // Prepare and execute the SQL query
    $stmt = $database->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();

    // Get the result set
    $result = $stmt->get_result();

    // Check if there are rows in the result set
    if ($result->num_rows > 0) {
        // Create an array to store all rows
        $rows = array();

        // Process each row in the result set
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        // Return all rows in JSON format
        echo json_encode($rows);
    } else {
        // Return an error message if no rows are found
        echo json_encode(["error" => "No data found"]);
    }

    // Close the database connection
    $database->close();
} else {
    // Return an error message if the 'user' key is not present
    echo json_encode(["error" => "Invalid request data"]);
}
?>
