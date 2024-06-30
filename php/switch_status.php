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

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if(isset($_GET["user_id"])) {
        $user_id = $_GET["user_id"];

        $change_status = "UPDATE users SET status = 'offline' WHERE id =" . $user_id;

        if ($conn->query($change_status) === TRUE) {
            echo "Status change successful!";
        } else {
            echo "Error: " . $change_status . "<br>" . mysqli_error($conn);
        }
    } else {
        echo "Error: User ID not provided.";
    }
}

$conn->close();
?>
