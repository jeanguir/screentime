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

    $username = $conn->real_escape_string($username);  
    $password = $conn->real_escape_string($password);  

    $stmt = $conn->prepare("SELECT id, hashed_password FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $stored_hashed_password = $row['hashed_password'];
        $user_id = $row['id'];

        if (password_verify($password, $stored_hashed_password)) {

            $stmt = $conn->prepare("SELECT * FROM instructions WHERE user_id=?");
            $stmt->bind_param("i", $user_id); 
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                var_dump($row);
            } else {
                echo "No instructions found for the user.";
            }

            $change_status = "UPDATE users SET status = 'online' WHERE id =" . $user_id;

            if ($conn->query($change_status) === TRUE) {
                echo "Registration successful!";

            } else {
                echo "Error: " . $change_status . "<br>" . $conn->error;
            }

        } else {
            echo "Invalid username or password";
        }
    } else {
        echo "Invalid username or password";
    }

    $stmt->close(); 
}

$conn->close(); 
?>