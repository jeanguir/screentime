<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['admin'])) {

    header("Location: index.php");
    exit();
}

$_SESSION['isAdmin'] = true;

$host = 'localhost';
$db_user = 'login';
$db_password = 'password';
$db_name = 'sitemanager';

$conn = new mysqli($host, $db_user, $db_password, $db_name);

if($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

$sql_last_window = "SELECT * FROM windows WHERE user_id=7 ORDER BY window_activity_time DESC LIMIT 1";

$last_window = $conn->query($sql_last_window);

$sql_userlist = "SELECT username, id, status FROM users";

$userlist_rows = $conn->query($sql_userlist);

$userlist = array(); 

while ($row1 = $userlist_rows->fetch_assoc()) {

    $user_id = $row1['id'];
    $sql_hours = "SELECT hours FROM daily_activity WHERE user_id = $user_id ORDER BY workday DESC LIMIT 1";
    $result_hours = $conn->query($sql_hours);
    $hours_worked = ($result_hours->num_rows == 1) ? $result_hours->fetch_assoc()['hours'] : "Нет данных";

    $row1['hours'] = $hours_worked;
    $userlist[] = $row1;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            font-size: 16px;
        }
        h2 {
            margin: 50px 30px 0px 30px;
            font-size: 24px;
        }
        table {
            border-collapse: collapse;
            margin: 30px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            font-size: 16px;
        }

        .logout {
            padding: 10px 20px;
            margin: 30px 10px 0px 30px;
            background-color: #007bff;
            text-decoration: none;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%; 
            box-sizing: border-box; 
            display: inline;
        }
        .logout:hover {
            background-color: #0056b3;
        }

        .reg {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td class="heading">Пользователь</td>
            <td class="heading">Статус</td>
            <td class="heading">Время активности (в часах)</td>
        </tr>
        <?php foreach ($userlist as $user): ?>
            <tr>
                <td><a href="user_page.php?user_id=<?php echo $user['id']; ?>"><?php echo $user['username']; ?></a></td>
                <td><?php echo $user['status']; ?></td>
                <td><?php echo $user['hours']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <a class="logout" href="logout.php">Выйти</a>
    <a class="logout reg" href="register.html">Регистрация</a>
</body>
</html>