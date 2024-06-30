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
    $confirm_password = $_POST["confirm_password"];

    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);
    $confirm_password = mysqli_real_escape_string($conn, $confirm_password);

    // Проверка совпадения паролей
    if ($password != $confirm_password) {
        echo "Passwords do not match";
    } else {
        // Проверка уникальности логина
        $check_username_query = "SELECT username FROM users WHERE username='$username'";
        $check_username_result = $conn->query($check_username_query);

        if ($check_username_result->num_rows > 0) {
            echo "Username is already taken. Choose another one.";
        } else {
            // Хеширование пароля перед сохранением в базу данных
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Вставка нового пользователя в базу данных
            $insert_user_query = "INSERT INTO users (username, hashed_password) VALUES ('$username', '$hashed_password')";

            if ($conn->query($insert_user_query) === TRUE) {
                // Получаем ID только что добавленного пользователя
                $user_id = $conn->insert_id;

                // Вставка инструкций для нового пользователя
                $insert_instructions_query = "INSERT INTO instructions (user_id, check_interval_in_sec, make_screenshots, track_windows, track_tabs) VALUES ('$user_id', 60, 1, 1, 1)";

                if ($conn->query($insert_instructions_query) === TRUE) {
                    echo "Registration successful!";
                    // Здесь можно добавить код для перенаправления на страницу входа
                } else {
                    echo "Error: " . $insert_instructions_query . "<br>" . $conn->error;
                }
            } else {
                echo "Error: " . $insert_user_query . "<br>" . $conn->error;
            }
        }
    }
}

$conn->close();

?>