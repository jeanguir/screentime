<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$uploadPath = 'screenshots/';

if (!file_exists($uploadPath)) {
    mkdir($uploadPath, 0777, true);
}

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['screenshot'], $_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $create_day = date('Y-m-d'); 

    date_default_timezone_set('Asia/Novosibirsk'); 
    $time = date('H:i:s');

    $uniqueString = bin2hex(random_bytes(4));
    $targetFile = $uploadPath . $uniqueString . '.' . strtolower(pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION));

    $check = getimagesize($_FILES['screenshot']['tmp_name']);

    if ($check !== false) {
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        if ($imageFileType === 'png') {
            if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $targetFile)) {
                echo 'Изображение успешно загружено.';

                $servername = "localhost";
                $username = "login";
                $password = "password";
                $dbname = "sitemanager";

                $conn = new mysqli($servername, $username, $password, $dbname);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $stmt = $conn->prepare("INSERT INTO screenshots (user_id, screenshot_url, created_at, time) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $user_id, $targetFile, $create_day, $time); 
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    echo "Запись успешно добавлена в базу данных.";
                } else {
                    echo "Ошибка при добавлении записи в таблицу screenshots: " . $stmt->error;
                }

                $stmt->close();
                $conn->close();
                session_write_close();

            } else {
                echo 'Ошибка при сохранении изображения: ' . error_get_last()['message'];
            }
        } else {
            echo 'Допускаются только изображения в формате PNG.';
        }
    } else {
        echo 'Файл не является изображением.';
    }
} 
else {
    echo 'Неверный запрос.';
}

?>