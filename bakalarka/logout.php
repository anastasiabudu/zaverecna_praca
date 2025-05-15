<?php
session_start();
session_unset(); // Удаляет все переменные сессии
session_destroy(); // Уничтожает сессию

// Перенаправляем на страницу входа
header("Location: index.php");
exit();
?>
