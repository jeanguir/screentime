<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Подключение к базе данных
$servername = "localhost";
$username = "login";
$password = "password";
$dbname = "sitemanager";

$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Получение данных из формы
$user_id = $_SESSION['user_id'];
$check_interval_in_sec = $_POST['check_interval_in_sec'];
$inactivity_period_in_min = $_POST['inactivity_period_in_min'];
$make_screenshots = isset($_POST['make_screenshots']) ? 1 : 0;
$track_windows = isset($_POST['track_windows']) ? 1 : 0;
$track_tabs = isset($_POST['track_tabs']) ? 1 : 0;

// Подготовка и выполнение SQL-запроса для обновления данных
$sql = "UPDATE instructions SET user_id=$user_id, check_interval_in_sec=$check_interval_in_sec, inactivity_period_in_min=$inactivity_period_in_min, make_screenshots=$make_screenshots, track_windows=$track_windows, track_tabs=$track_tabs WHERE user_id=$user_id";

if ($conn->query($sql) === TRUE) {
    // Если обновление прошло успешно, перенаправляем обратно на форму с сообщением об успешном обновлении
    header("Location: index.php?success=true");
} else {
    echo "Ошибка при обновлении данных: " . $conn->error;
}

$conn->close();
?>
