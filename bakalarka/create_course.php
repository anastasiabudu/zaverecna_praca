<?php
session_start();
require_once 'db.php';

// Проверка роли
if ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'supervisor') {
    header("Location: test.php");
    exit();
}

// Обработка формы создания курса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $created_by = $_SESSION['user_id'];  // ID пользователя, который создает курс

    if (empty($name)) {
        echo "<p class='error'>Názov kurzu je povinný.</p>";
    } else {
        $query = "INSERT INTO courses (name, description, created_by) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $name, $description, $created_by);

        if ($stmt->execute()) {
            header("Location: courses.php");
            exit();
        } else {
            echo "<p class='error'>Chyba pri vytváraní kurzu: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vytvoriť kurz</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Vytvoriť kurz</h1>

    <form action="" method="POST">
        <label for="name">Názov kurzu:</label>
        <input type="text" name="name" id="name" required>

        <label for="description">Popis kurzu:</label>
        <textarea name="description" id="description" rows="5"></textarea>

        <button type="submit">Vytvoriť kurz</button>
    </form>

    <a href="courses.php" class="back-button">Späť na kurzy</a>
</div>
</body>
</html>
