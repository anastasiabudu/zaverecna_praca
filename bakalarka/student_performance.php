<?php
session_start();

if ($_SESSION['role'] !== 'supervisor') {
    header("Location: test.php");
    exit();
}

require_once 'db.php';

$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id <= 0) {
    die("Neplatné ID študenta.");
}

$query = "SELECT name, email, role FROM students WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($student_name, $student_email, $student_role);
$stmt->fetch();
$stmt->close();

if (!$student_name) {
    die("Študent nebol nájdený.");
}

$query = "SELECT test_name, score, total_questions, date_taken FROM test_results WHERE student_id = ? ORDER BY date_taken DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($test_name, $score, $total_questions, $date_taken);

$test_results = [];
while ($stmt->fetch()) {
    $test_results[] = [
        'test_name' => $test_name,
        'score' => $score,
        'total_questions' => $total_questions,
        'date_taken' => $date_taken
    ];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Úspešnosť študenta</title>
    <!-- Подключаем библиотеки анимаций -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --success-color: #4bb543;
            --warning-color: #ffc107;
            --error-color: #ff3333;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e0e9f5 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        
        .performance-container {
            background: white;
            width: 100%;
            max-width: 1000px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 20px 0;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .performance-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }
        
        h1 {
            color: var(--dark-color);
            margin-bottom: 20px;
            font-size: 2rem;
            position: relative;
            display: inline-block;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 80px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 3px;
        }
        
        h2 {
            color: var(--dark-color);
            margin: 30px 0 20px;
            font-size: 1.5rem;
        }
        
        .student-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
            background: rgba(67, 97, 238, 0.05);
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid var(--primary-color);
        }
        
        .info-item {
            flex: 1;
            min-width: 200px;
        }
        
        .info-item strong {
            display: block;
            color: var(--primary-color);
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .info-item span {
            font-size: 1.1rem;
        }
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.8s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .results-table th {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 500;
        }
        
        .results-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .results-table tr:last-child td {
            border-bottom: none;
        }
        
        .results-table tr:hover {
            background: rgba(67, 97, 238, 0.05);
        }
        
        .results-table tr {
            transition: all 0.3s ease;
        }
        
        .results-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .progress-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .progress-bar {
            flex: 1;
            height: 10px;
            background: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: var(--primary-color);
            border-radius: 5px;
            transition: width 0.5s ease;
        }
        
        .no-records {
            text-align: center;
            padding: 30px;
            color: #777;
            font-size: 1.1rem;
            animation: fadeIn 0.8s;
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 25px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-size: 1rem;
            font-weight: 500;
            margin-top: 30px;
        }
        
        .btn-back:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        /* Эффект ripple */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.7);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        /* Адаптивность */
        @media (max-width: 768px) {
            .performance-container {
                padding: 20px;
            }
            
            .student-info {
                flex-direction: column;
                gap: 15px;
            }
            
            .info-item {
                min-width: 100%;
            }
            
            .results-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>

<div class="performance-container animate__animated animate__fadeIn">
    <h1><i class="fas fa-user-graduate"></i> Úspešnosť študenta</h1>
    
    <div class="student-info animate__animated animate__fadeIn">
        <div class="info-item">
            <strong><i class="fas fa-user"></i> Meno študenta</strong>
            <span><?= htmlspecialchars($student_name) ?></span>
        </div>
        <div class="info-item">
            <strong><i class="fas fa-envelope"></i> Email</strong>
            <span><?= htmlspecialchars($student_email) ?></span>
        </div>
        <div class="info-item">
            <strong><i class="fas fa-user-tag"></i> Rola</strong>
            <span><?= htmlspecialchars($student_role) ?></span>
        </div>
    </div>
    
    <h2><i class="fas fa-chart-bar"></i> Výsledky testov</h2>
    
    <table class="results-table">
        <thead>
            <tr>
                <th><i class="fas fa-book"></i> Názov testu</th>
                <th><i class="fas fa-star"></i> Úspešnosť</th>
                <th><i class="fas fa-calendar-alt"></i> Dátum testu</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($test_results)): ?>
                <?php foreach ($test_results as $result): 
                    $percentage = round(($result['score'] / $result['total_questions']) * 100);
                    $progress_color = $percentage >= 80 ? 'var(--success-color)' : 
                                    ($percentage >= 50 ? 'var(--warning-color)' : 'var(--error-color)');
                ?>
                    <tr class="animate__animated animate__fadeIn">
                        <td><?= htmlspecialchars($result['test_name']) ?></td>
                        <td>
                            <div class="progress-cell">
                                <span><?= htmlspecialchars($result['score']) ?> / <?= htmlspecialchars($result['total_questions']) ?></span>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $percentage ?>%; background: <?= $progress_color ?>;"></div>
                                </div>
                            </div>
                        </td>
                        <td><i class="fas fa-clock"></i> <?= htmlspecialchars($result['date_taken']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="no-records">
                        <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 15px; display: block;"></i>
                        Žiadne výsledky testov.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="manage_users.php" class="btn-back animate__animated animate__fadeInLeft" id="backButton">
        <i class="fas fa-arrow-left"></i> Späť na zoznam používateľov
    </a>
</div>

<script>
    // Добавляем эффект ripple для кнопки
    document.getElementById('backButton').addEventListener('click', function(e) {
        const btn = e.target;
        const rect = btn.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const ripple = document.createElement('span');
        ripple.classList.add('ripple');
        ripple.style.left = `${x}px`;
        ripple.style.top = `${y}px`;
        
        btn.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    });

    // Анимация строк таблицы с задержкой
    document.querySelectorAll('.results-table tr').forEach((row, index) => {
        row.style.animationDelay = `${index * 0.1}s`;
    });
</script>

</body>
</html>