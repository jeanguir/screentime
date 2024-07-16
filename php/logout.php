<?php
session_start();

// Уничтожение сессии и перенаправление на страницу входа
session_destroy();
header("Location: index.php");
exit();
?>
