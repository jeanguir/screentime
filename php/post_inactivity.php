<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Kod poluchen";

// Получаем данные из POST-запроса
$inputJSON = file_get_contents('php://input');
$inputData = json_decode($inputJSON, true);

// Проверяем, удалось ли декодировать JSON
if ($inputData === null) {
    http_response_code(400); // Ошибка неверного запроса
    echo json_encode(array("message" => "Invalid JSON data"));
    exit();
}

// Доступ к значениям в JSON
$user_id = $inputData['user_id'];
$hashMaps = $inputData['hashMaps'];

$servername = "localhost";
$username = "login";
$password = "password";
$dbname = "sitemanager";

$conn = new mysqli($servername, $username, $password, $dbname);

// Проверяем соединение с базой данных
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Цикл для вставки данных из массива hashMaps
foreach ($hashMaps as $entry) {
    $duration = $entry['duration'];
    $start = $entry['start'];
    $end = $entry['end'];

    // Проверяем наличие записи в базе данных
    $check_query = "SELECT * FROM inactivities WHERE user_id = ? AND start = ? AND date = CURRENT_DATE";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ss", $user_id, $start);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_stmt->close();

    if ($check_result->num_rows > 0) {
    	echo "Zapis sushestvuet";
        $end = $entry['end'];
        // Если запись существует, обновляем window_activity_time
        $update_query = "UPDATE inactivities SET end = ?, duration = ? WHERE user_id = ? AND start = ? AND date = CURRENT_DATE";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssss", $end, $duration, $user_id, $start);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
    	echo "novaya zapis";
        // Если запись не существует, создаем новую
        $insert_query = "INSERT INTO inactivities (user_id, duration, start, end, date) VALUES (?, ?, ?, ?, CURRENT_DATE)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("ssss", $user_id, $duration, $start, $end);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
}

$conn->close();

?>