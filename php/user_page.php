<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['isAdmin'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_GET['user_id'];

$_SESSION['user_id'] = $user_id;

$servername = "localhost";
$login = "login";
$password = "password";
$dbname = "sitemanager";

$conn = new mysqli($servername, $login, $password, $dbname);

if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$selected_date = date('Y-m-d'); // По умолчанию сегодняшняя дата

if (isset($_GET['date'])) {
    $selected_date = $_GET['date'];
}

$sql = "SELECT * FROM windows WHERE user_id = $user_id AND date = '$selected_date'";
$result = $conn->query($sql);
$windows = array();

while ($row = $result->fetch_assoc()) {
    $windows[] = array(
        'user_activity_id' => $row['id'],
        'window_name' => $row['window_name'],
        'window_activity_time' => $row['window_activity_time'],
        'start' => $row['start'],
        'end' => $row['w_end']
    );
}

$sqlScreenshots = "SELECT * FROM screenshots WHERE user_id = $user_id AND created_at = '$selected_date'";
$resultScreenshots = $conn->query($sqlScreenshots);
$screenshots = array();

while ($rowScreenshots = $resultScreenshots->fetch_assoc()) {
    $screenshots[] = array(
        'screenshot_id' => $rowScreenshots['id'],
        'screenshot_url' => $rowScreenshots['screenshot_url'],
        'created_at' => $rowScreenshots['created_at'],
        'time' => $rowScreenshots['time']
    );
}

$sqlDays = "SELECT * FROM daily_activity WHERE user_id=" . $user_id;
$resultDays = $conn->query($sqlDays);
$workdays = array();

while ($rowDays = $resultDays->fetch_assoc()) {
    $workdays[] = array(
        'workday' => $rowDays['workday'],
        'hours' => $rowDays['hours']
    );
}

$sqlSettings = "SELECT check_interval_in_sec, inactivity_period_in_min, make_screenshots, track_windows, track_tabs FROM instructions WHERE user_id = " . $user_id;
$resultSettings = $conn->query($sqlSettings);

$settings = array();
if ($rowSettings = $resultSettings->fetch_assoc()) {
    $settings = $rowSettings;
}

$sqlInac = "SELECT * FROM inactivities WHERE user_id=$user_id AND date = '$selected_date'";
$resultInac = $conn->query($sqlInac);
$inactivities = array();

while ($rowInac = $resultInac->fetch_assoc()) {
    $inactivities[] = array(
        'start' => $rowInac['start'],
        'end' => $rowInac['end'],
        'duration' => $rowInac['duration']
    );
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            font-size: 16px;
        }
        form {
            width: 400px; /* Ширина формы */
            margin: 30px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            font-size: 16px;
        }
        label {
            display: inline;
            margin-bottom: 8px;
        }
        input[type="text"] {
            margin-bottom: 8px; /* Отступ между текстовыми input */
            padding: 8px;
            width: calc(100% - 16px); /* Ширина с учетом padding */
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box; /* Учитываем padding внутри ширины */
        }
        input[type="checkbox"] {
            margin-bottom: 16px; /* Отступ между checkbox и следующим элементом */
        }
        input[type="submit"] {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%; /* Ширина кнопки равна ширине формы */
            box-sizing: border-box; /* Учитываем padding внутри ширины */
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        h2 {
            margin: 50px 30px 0px 30px;
            font-size: 24px;
        }
        table {
            border-collapse: collapse;
            margin: 30px;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            font-size: 16px;
        }
        .screenshot-link {
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <form action="update_instructions.php" method="post">
        <label for="check_interval_in_sec">Интервал создания скриншотов (сек):</label>
        <input type="text" name="check_interval_in_sec" value="<?php echo isset($settings['check_interval_in_sec']) ? $settings['check_interval_in_sec'] : ''; ?>" required><br>

        <label for="inactivity_period_in_min">Период неактивности (мин):</label>
        <input style="margin-bottom: 30px;" type="text" name="inactivity_period_in_min" value="<?php echo isset($settings['inactivity_period_in_min']) ? $settings['inactivity_period_in_min'] : ''; ?>" required><br>

        <label for="make_screenshots">Создание скриншотов:</label>
        <input type="checkbox" name="make_screenshots" <?php echo (isset($settings['make_screenshots']) && $settings['make_screenshots'] == '1') ? 'checked' : ''; ?>><br>

        <label for="track_windows">Отслеживание окон:</label>
        <input type="checkbox" name="track_windows" <?php echo (isset($settings['track_windows']) && $settings['track_windows'] == '1') ? 'checked' : ''; ?>><br>

        <label for="track_tabs">Отслеживание вкладок:</label>
        <input type="checkbox" name="track_tabs" <?php echo (isset($settings['track_tabs']) && $settings['track_tabs'] == '1') ? 'checked' : ''; ?>><br>

        <input type="submit" value="Обновить параметры">
    </form>

    <form action="" method="get">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <label for="date">Выберите дату:</label>
        <input type="date" name="date" id="date" value="<?php echo date('Y-m-d'); ?>">
        <input type="submit" value="Отобразить данные">
    </form>


    <h2>Данные окон:</h2>
    <table>
        <tr>
            <th>Название окна</th>
            <th>Время активности</th>
            <th>Старт</th>
            <th>Финиш</th>
        </tr>
        <?php foreach ($windows as $window) : ?>
            <tr>
                <td><?php echo $window['window_name']; ?></td>
                <td><?php echo $window['window_activity_time']; ?> минут</td>
                <td><?php echo $window['start']; ?></td>
                <td><?php echo $window['end']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- <h2>Данные вкладок:</h2>
    <table>
        <tr>
            <th>Название вкладки</th>
            <th>Время активности</th>
        </tr>
        <?php foreach ($tabs as $tab) : ?>
            <tr>
                <td><?php echo $tab['tab_name']; ?></td>
                <td><?php echo $tab['tab_activity_time']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table> -->

    <h2>Дневная неактивность</h2>
    <table>
        <tr>
            <th>Старт</th>
            <th>Финиш</th>
            <th>Длительность</th>
        </tr>
        
        <?php foreach ($inactivities as $inactivity) : ?>
            <tr>
                <td><?php echo $inactivity['start']; ?></td>
                <td><?php echo $inactivity['end']; ?></td>
                <td><?php echo $inactivity['duration']; ?> минут</td>
            </tr>
        <?php endforeach; ?>
        <!--<tr>
            <th style="border-right-color: #f4f4f4;">Итог</th>
            <td style="border-right-color: #f4f4f4;"></td>
            <td style="border-left-color: #f4f4f4;">12 минут</td>
        </tr>-->
    </table>

    <h2>Скриншоты:</h2>
    <table>
        <tr>
            <th>Ссылка</th>
            <th>Время</th>
        </tr>

        <?php foreach ($screenshots as $screenshot) : ?>
            <tr>
                <td>
                    <a class="screenshot-link" href="<?php echo $screenshot['screenshot_url']; ?>">
                        <?php echo $screenshot['screenshot_url']; ?>
                    </a>
                </td>
                <td>
                    <?php echo $screenshot['time']; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Дни активности за месяц</h2>
    <table>
        <tr>
            <th>Дата</th>
            <th>Время активности</th>
        </tr>
        <?php foreach ($workdays as $day) : ?>
            <tr>
                <td><?php echo $day['workday']; ?> </td>
                <td><?php echo $day['hours'] . " часов"; ?>   </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>