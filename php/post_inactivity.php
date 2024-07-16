<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Kod poluchen";

$inputJSON = file_get_contents('php://input');
$inputData = json_decode($inputJSON, true);

if ($inputData === null) {
    http_response_code(400); 
    echo json_encode(array("message" => "Invalid JSON data"));
    exit();
}

$user_id = $inputData['user_id'];
$hashMaps = $inputData['hashMaps'];

$servername = "localhost";
$username = "login";
$password = "password";
$dbname = "sitemanager";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

foreach ($hashMaps as $entry) {
    $duration = $entry['duration'];
    $start = $entry['start'];
    $end = $entry['end'];

    $check_query = "SELECT * FROM inactivities WHERE user_id = ? AND start = ? AND date = CURRENT_DATE";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ss", $user_id, $start);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_stmt->close();

    if ($check_result->num_rows > 0) {
    	echo "Zapis sushestvuet";
        $end = $entry['end'];

        $update_query = "UPDATE inactivities SET end = ?, duration = ? WHERE user_id = ? AND start = ? AND date = CURRENT_DATE";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssss", $end, $duration, $user_id, $start);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
    	echo "novaya zapis";

        $insert_query = "INSERT INTO inactivities (user_id, duration, start, end, date) VALUES (?, ?, ?, ?, CURRENT_DATE)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("ssss", $user_id, $duration, $start, $end);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
}

$conn->close();

?>