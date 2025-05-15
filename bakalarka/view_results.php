<?php
session_start();
require_once 'db.php';


// Проверка роли
if ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'supervisor') {
    header("Location: test.php");
    exit();
}


// Запрос для получения результатов тестов
$query = "SELECT tr.id, s.name AS student_name, tr.test_name, tr.score, tr.total_questions, tr.date_taken 
          FROM test_results tr
          JOIN students s ON tr.student_id = s.id
          ORDER BY tr.date_taken DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Výsledky študentov</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
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
    </style>
</head>
<body>
<div class="container">
    <h1>Výsledky študentov</h1>

    <table>
        <thead>
        <tr>
            <th>Študent</th>
            <th>Test</th>
            <th>Skóre</th>
            <th>Počet otázok</th>
            <th>Dátum</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['student_name']) ?></td>
                <td><?= htmlspecialchars($row['test_name']) ?></td>
                <td><?= htmlspecialchars($row['score']) ?></td>
                <td><?= htmlspecialchars($row['total_questions']) ?></td>
                <td><?= htmlspecialchars($row['date_taken']) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <a href="test.php" class="back-button">Späť na hlavnú stránku</a>
</div>
</body>
</html>
