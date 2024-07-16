<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$inputJSON = file_get_contents('php://input');
$inputData = json_decode($inputJSON, true);

if ($inputData === null) {
    http_response_code(400); 
    echo json_encode(array("message" => "Invalid JSON data"));
    exit();
}

$user_id = $inputData['user_id'];
$hashMaps = $inputData['hashMaps'];
$hours= $inputData['hours'];

$servername = "localhost";
$username = "login";
$password = "password";
$dbname = "sitemanager";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

foreach ($hashMaps as $entry) {
    $window_name = $entry['name'];
    $window_activity_time = $entry['time'];
    $window_start = $entry['start'];

    $check_query = "SELECT * FROM windows WHERE user_id = ? AND window_name = ? AND date = CURRENT_DATE";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ss", $user_id, $window_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_stmt->close();

    if ($check_result->num_rows > 0) {
        $window_end = $entry['end'];

        $update_query = "UPDATE windows SET window_activity_time = ?, start = ?, w_end = ? WHERE user_id = ? AND window_name = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sssss", $window_activity_time, $window_start, $window_end, $user_id, $window_name);
        $update_stmt->execute();
        $update_stmt->close();
        echo "\nЗапись обновлена\n";
        echo $window_activity_time . " " . $window_name . " " . $window_start . " " . $window_end . "\n";
    } else {

        $insert_query = "INSERT INTO windows (user_id, window_name, window_activity_time, start, date) VALUES (?, ?, ?, ?, CURRENT_DATE)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("ssss", $user_id, $window_name, $window_activity_time, $window_start);
        $insert_stmt->execute();
        $insert_stmt->close();
    }

    $sql = "SELECT id FROM daily_activity WHERE user_id = $user_id AND workday = CURRENT_DATE";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {

        $sql = "UPDATE daily_activity SET hours = $hours WHERE user_id = $user_id AND workday = CURRENT_DATE";
        $conn->query($sql);
        echo "Значение hours обновлено для сегодняшнего дня.";
    } else {

        $sql = "INSERT INTO daily_activity (user_id, hours, workday) VALUES ($user_id, $hours, CURRENT_DATE)";
        $conn->query($sql);
        echo "Создана новая запись для сегодняшнего дня.";
    }
}

$conn->close();

?>