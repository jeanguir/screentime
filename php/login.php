<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "login";
$password = "password";
$dbname = "sitemanager";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Используем подготовленный запрос
    $stmt = $conn->prepare("SELECT id, hashed_password FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === FALSE) {
        echo "Ошибка при выполнении запроса: " . $conn->error;
        exit();
    }

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $stored_hashed_password = $row['hashed_password'];
        $user_id = $row['id'];

        if (password_verify($password, $stored_hashed_password)) {
            // Авторизация успешна, устанавливаем сессию
            session_start();
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $user_id;

            // Редирект на страницу пользователя
            header("Location: user_page.php");
            exit();
        } else {
            echo "Invalid username or password";
        }
    } else {
        echo "Invalid username or password";
    }
}

$conn->close();
?>