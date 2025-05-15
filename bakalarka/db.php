<?php
// Показывать все ошибки для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$hostname = "localhost";
$database = "students_db";
$username = "xbudu";
$password = "23122002";

// Функция для подключения к базе данных с использованием MySQLi
function connectMysql($hostname, $database, $username, $password) {
    $conn = new mysqli($hostname, $username, $password, $database);

    // Проверка подключения
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        return null;
    }

    return $conn;
}

// Создаем глобальное соединение с базой данных
$conn = connectMysql($hostname, $database, $username, $password);

if ($conn === null) {
    die("Database connection is not established.");
}
?>