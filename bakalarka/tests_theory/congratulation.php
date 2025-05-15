<?php
session_start();
if (!isset($_SESSION['module_completed'])) {
    header('Location: ../index.php');
    exit;
}

$completedModule = $_SESSION['module_completed']['module_name'];
unset($_SESSION['module_completed']);

// Подключаемся к базе данных для получения рекомендаций
include "../db.php";

// Получаем рекомендуемые курсы
$stmt = $conn->prepare("
    SELECT id, title, short_description 
    FROM courses 
    WHERE id != ? 
    ORDER BY RAND() 
    LIMIT 3
");
$stmt->bind_param("i", $_SESSION['module_completed']['course_id'] ?? 0);
$stmt->execute();
$recommended_courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gratulujeme!</title>
    <link rel="stylesheet" href="../css/congratulation.css">
</head>
<body>
    <div class="congratulation-container">
        <h1>Gratulujeme! 🎉</h1>
        <p>Úspešne ste dokončili modul <strong><?= htmlspecialchars($completedModule) ?></strong>!</p>
        
        <div class="recommendations">
            <h2>Odporúčané ďalšie kurzy:</h2>
            <div class="courses">
                <?php foreach ($recommended_courses as $course): ?>
                    <div class="course-card">
                        <h3><?= htmlspecialchars($course['title']) ?></h3>
                        <p><?= htmlspecialchars($course['short_description']) ?></p>
                        <a href="course.php?id=<?= $course['id'] ?>">Začať študovať</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <a href="../index.php" class="back-button">Naspäť na hlavnú stránku</a>
    </div>
</body>
</html>