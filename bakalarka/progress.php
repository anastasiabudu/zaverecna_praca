<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<p>Prosím, <a href='login.php'>prihláste sa</a>, aby ste si mohli pozrieť svoje výsledky.</p>";
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT test_name, score, total_questions, date_taken FROM test_results WHERE student_id = ? ORDER BY date_taken DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moja úspešnosť</title>
    <!-- Подключаем библиотеки анимаций -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --success-color: #4bb543;
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
        
        .results-container {
            background: white;
            width: 100%;
            max-width: 800px;
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
        
        .results-container::before {
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
            margin-bottom: 30px;
            font-size: 2rem;
            position: relative;
            display: inline-block;
            width: 100%;
            text-align: center;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 3px;
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
        
        .no-results {
            text-align: center;
            padding: 30px;
            color: #777;
            font-size: 1.1rem;
            animation: fadeIn 0.8s;
        }
        
        .progress-circle {
            display: inline-block;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            text-align: center;
            line-height: 40px;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .button-container {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }
        
        .button {
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
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .button i {
            margin-right: 8px;
            transition: transform 0.3s;
        }
        
        .button:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        .button:hover i {
            transform: scale(1.2);
        }
        
        .button:active {
            transform: translateY(1px);
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
            .results-container {
                padding: 20px;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .results-table th, 
            .results-table td {
                padding: 12px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<div class="results-container animate__animated animate__fadeIn">
    <h1><i class="fas fa-chart-line"></i> Moja úspešnosť</h1>

    <?php
    if ($result->num_rows > 0) {
        echo '<table class="results-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-book"></i> Test</th>
                        <th><i class="fas fa-star"></i> Výsledok</th>
                        <th><i class="fas fa-calendar-alt"></i> Dátum</th>
                    </tr>
                </thead>
                <tbody>';

        while ($row = $result->fetch_assoc()) {
            $test_name = htmlspecialchars($row['test_name']);
            $score = htmlspecialchars($row['score']);
            $total_questions = htmlspecialchars($row['total_questions']);
            $date_taken = htmlspecialchars($row['date_taken']);
            $percentage = round(($score / $total_questions) * 100);

            echo "<tr class='animate__animated animate__fadeIn'>
                    <td><i class='fas fa-file-alt'></i> {$test_name}</td>
                    <td>
                        <div class='progress-circle'>{$score}</div>
                        {$score} z {$total_questions} ({$percentage}%)
                    </td>
                    <td><i class='fas fa-clock'></i> {$date_taken}</td>
                  </tr>";
        }

        echo '</tbody></table>';
    } else {
        echo "<div class='no-results animate__animated animate__fadeIn'>
                <i class='fas fa-info-circle' style='font-size: 2rem; margin-bottom: 15px; display: block;'></i>
                Zatiaľ nemáte žiadne výsledky testov.
              </div>";
    }

    $stmt->close();
    $conn->close();
    ?>

    <div class="button-container">
        <a href="test.php" class="button" id="backButton">
            <i class="fas fa-arrow-left"></i> Vrátiť sa k testom
        </a>
    </div>
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