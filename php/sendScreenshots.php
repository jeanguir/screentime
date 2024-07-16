<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "HEELLO";

session_start();

if (isset($_GET['imagelink'], $_GET['user_id'])) {
    $url = $_GET['imagelink'];
    $user_id = $_GET['user_id'];

    $servername = "localhost";
    $username = "login";
    $password = "password";
    $dbname = "sitemanager";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO screenshots (user_id, screenshot_url) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $url);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Запись успешно добавлена в базу данных.";
    } else {
        echo "Ошибка при добавлении записи: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    session_write_close();
} else {
    echo "Отсутствуют необходимые параметры в URL.";
}

?>