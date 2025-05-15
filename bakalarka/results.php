<?php
session_start();

require_once 'db.php';

$score = $_GET['score'] ?? 0;
$totalQuestions = $_GET['total'] ?? 0;
$test_name = $_GET['test_name'] ?? 'Python';
$userResults = $_SESSION['results'] ?? [];

if (!isset($userResults['questions']) || !is_array($userResults['questions'])) {
    die("Invalid session data.");
}

$query = "SELECT question, options, answer FROM tests WHERE test_name = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $test_name);
$stmt->execute();
$result = $stmt->get_result();
$questions = [];

while ($row = $result->fetch_assoc()) {
    $questions[] = [
        'question' => $row['question'],
        'options' => json_decode($row['options'], true),
        'answer' => $row['answer']
    ];
}

$stmt->close();
$conn->close();

$test_file_map = [
    'Python' => 'python/python_test.php',
    'Java' => 'java/java_test.php',
    'HTML' => 'html/html_test.php',
    'C++' => 'cpp/cpp_test.php',
];

$test_file = $test_file_map[$test_name] ?? 'test.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Results</title>
    <!-- Подключаем библиотеки анимаций -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #759cd8;
            --secondary-color: #9cb9d1;
            --dark-color: #4b6e9b;
            --light-color: #eef0f6;
            --correct-color: #28a745;
            --wrong-color: #dc3545;
            --accent-color: #ffcc00;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f5f5 0%, #e0e9f5 100%);
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .results-container {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-width: 800px;
            width: 100%;
            transition: all 0.3s ease;
            transform: translateY(0);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeIn 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        h1 {
            color: var(--dark-color);
            font-size: 32px;
            margin-bottom: 20px;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 3px;
        }
        
        .result-summary {
            font-size: 24px;
            text-align: center;
            margin-bottom: 30px;
            padding: 15px;
            border-radius: 10px;
            background: rgba(117, 156, 216, 0.1);
            animation: bounceIn 0.8s;
        }
        
        @keyframes bounceIn {
            0% { transform: scale(0.8); opacity: 0; }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .score-circle {
            display: inline-block;
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            font-size: 30px;
            font-weight: bold;
            margin: 0 10px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .questions-container {
            margin-top: 20px;
        }
        
        .question-result {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeInUp 0.5s forwards;
            animation-delay: calc(var(--order) * 0.1s);
            transform: translateY(20px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }
        
        .question-result.correct {
            background-color: rgba(40, 167, 69, 0.1);
            border-left: 5px solid var(--correct-color);
        }
        
        .question-result.incorrect {
            background-color: rgba(220, 53, 69, 0.1);
            border-left: 5px solid var(--wrong-color);
        }
        
        .question-text {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .user-answer, .correct-answer {
            margin-top: 8px;
            padding: 8px 12px;
            border-radius: 5px;
            display: inline-block;
        }
        
        .user-answer {
            background-color: rgba(117, 156, 216, 0.2);
        }
        
        .correct-answer {
            background-color: rgba(40, 167, 69, 0.2);
        }
        
        .buttons-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 25px;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-size: 16px;
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
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        .button:hover i {
            transform: scale(1.2);
        }
        
        .button:active {
            transform: translateY(1px);
        }
        
        .back-button {
            background-color: var(--primary-color);
        }
        
        .back-button:hover {
            background-color: var(--dark-color);
        }
        
        .retake-button {
            background-color: var(--correct-color);
        }
        
        .retake-button:hover {
            background-color: #218838;
        }
        
        /* Эффект волны при клике */
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
                font-size: 26px;
            }
            
            .result-summary {
                font-size: 20px;
            }
            
            .buttons-container {
                flex-direction: column;
                gap: 10px;
            }
            
            .button {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="results-container animate__animated">
    <h1><i class="fas fa-poll"></i> Test Results: <?= htmlspecialchars($test_name) ?></h1>
    
    <div class="result-summary">
        You answered <span class="score-circle animate__animated animate__pulse"><?= $score ?></span> 
        out of <?= $totalQuestions ?> questions correctly.
    </div>
    
    <div class="questions-container">
        <?php if (!empty($questions)): ?>
            <?php foreach ($questions as $index => $question): ?>
                <?php
                $userAnswer = trim(strtolower($userResults['questions'][$index]['userAnswer'] ?? ''));
                $correctAnswer = trim(strtolower($question['answer']));
                $isCorrect = ($userAnswer === $correctAnswer);
                ?>
                <div class="question-result <?= $isCorrect ? 'correct' : 'incorrect' ?>" 
                     style="--order: <?= $index ?>;">
                    <div class="question-text"><?= htmlspecialchars($question['question']) ?></div>
                    <div class="user-answer">
                        <i class="fas fa-user"></i> Your answer: 
                        <?= htmlspecialchars($userResults['questions'][$index]['userAnswer'] ?? 'No answer') ?>
                    </div>
                    <?php if (!$isCorrect): ?>
                        <div class="correct-answer">
                            <i class="fas fa-check"></i> Correct answer: 
                            <?= htmlspecialchars($question['answer']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="animate__animated animate__fadeIn">No results found.</p>
        <?php endif; ?>
    </div>
    
    <div class="buttons-container">
        <a href="test.php" class="button back-button animate__animated animate__fadeInLeft">
            <i class="fas fa-arrow-left"></i> Back to Tests
        </a>
        <a href="<?= $test_file ?>" class="button retake-button animate__animated animate__fadeInRight">
            <i class="fas fa-redo"></i> Retake Test
        </a>
    </div>
</div>

<script>
    // Добавляем эффект ripple для всех кнопок
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('button')) {
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
        }
    });
</script>

</body>
</html>

<?php
unset($_SESSION['results']);
?>