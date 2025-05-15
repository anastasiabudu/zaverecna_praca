<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Подготовленный запрос для получения данных пользователя по email
    $stmt = $conn->prepare("SELECT id, name, password FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $name, $hashed_password);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();

        // Проверка пароля
        if (password_verify($password, $hashed_password)) {
            // Если пароль правильный, сохраняем информацию в сессии
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['email'] = $email;

            // Перенаправляем на страницу теста
            header("Location: test.php");
            exit();
        } else {
            echo "Ошибка: Неверный пароль.";
        }
    } else {
        echo "Ошибка: Пользователь с таким email не найден.";
    }

    $stmt->close();
    $conn->close();
}
?>
