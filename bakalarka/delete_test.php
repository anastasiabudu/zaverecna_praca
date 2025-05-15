<?php
session_start();

require_once 'db.php';

if ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'supervisor') {
    header("Location: test.php");
    exit();
}

$test_id = $_GET['id'];

$test = [];
$query = "SELECT id, test_name, question, options, answer FROM tests WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $test_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $test = $result->fetch_assoc();
} else {
    $_SESSION['error'] = "Test nebol nájdený.";
    header("Location: courses.php");
    exit();
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_test'])) {
    $query = "DELETE FROM course_tests WHERE test_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $test_id);
    $stmt->execute();
    $stmt->close();

    $query = "DELETE FROM tests WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $test_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Test bol úspešne vymazaný.";
        header("Location: courses.php");
        exit();
    } else {
        $_SESSION['error'] = "Chyba pri vymazávaní testu: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vymazať test</title>
    <!-- Подключаем библиотеки анимаций -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --success-color: #4bb543;
            --error-color: #dc3545;
            --warning-color: #ffc107;
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
        
        .delete-container {
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
        
        .delete-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--error-color), #ff6b6b);
        }
        
        h1 {
            color: var(--dark-color);
            margin-bottom: 20px;
            font-size: 2rem;
            position: relative;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 80px;
            height: 4px;
            background: var(--error-color);
            border-radius: 3px;
        }
        
        .test-detail {
            margin: 30px 0;
            padding: 20px;
            border-radius: 10px;
            background: rgba(220, 53, 69, 0.05);
            border-left: 4px solid var(--error-color);
            animation: fadeIn 0.8s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .test-detail p {
            margin: 10px 0;
            font-size: 1.1rem;
        }
        
        .test-detail strong {
            color: var(--dark-color);
            font-weight: 500;
        }
        
        .options-list {
            margin: 15px 0;
            padding-left: 25px;
        }
        
        .options-list li {
            margin-bottom: 8px;
            position: relative;
            list-style-type: none;
        }
        
        .options-list li::before {
            content: '•';
            color: var(--primary-color);
            font-weight: bold;
            font-size: 1.2rem;
            position: absolute;
            left: -20px;
            top: -2px;
        }
        
        .correct-answer {
            color: var(--success-color);
            font-weight: 500;
        }
        
        .btn-delete {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 25px;
            background: var(--error-color);
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
        
        .btn-delete i {
            margin-right: 8px;
            transition: transform 0.3s;
        }
        
        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(220, 53, 69, 0.3);
        }
        
        .btn-delete:hover i {
            transform: scale(1.2);
        }
        
        .btn-delete:active {
            transform: translateY(1px);
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 25px;
            background: var(--light-color);
            color: var(--dark-color);
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-size: 1rem;
            font-weight: 500;
            margin-top: 20px;
            border: 1px solid #e9ecef;
        }
        
        .btn-back:hover {
            background: #e9ecef;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
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
            .delete-container {
                padding: 20px;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .test-detail {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<div class="delete-container animate__animated animate__fadeIn">
    <h1><i class="fas fa-trash-alt"></i> Vymazať test</h1>
    
    <div class="test-detail">
        <p><strong><i class="fas fa-book"></i> Názov testu:</strong> <?= htmlspecialchars($test['test_name']) ?></p>
        <p><strong><i class="fas fa-question-circle"></i> Otázka:</strong> <?= htmlspecialchars($test['question']) ?></p>
        
        <p><strong><i class="fas fa-list-ul"></i> Možnosti:</strong></p>
        <ul class="options-list">
            <?php foreach (json_decode($test['options']) as $index => $option): ?>
                <li><?= htmlspecialchars($option) ?></li>
            <?php endforeach; ?>
        </ul>
        
        <p><strong><i class="fas fa-check-circle"></i> Správna odpoveď:</strong> 
            <span class="correct-answer"><?= htmlspecialchars($test['answer']) ?></span>
        </p>
    </div>
    
    <form action="" method="POST" id="deleteForm">
        <button type="submit" name="delete_test" class="btn-delete" id="deleteBtn">
            <i class="fas fa-trash"></i> Vymazať test
        </button>
    </form>
    
    <a href="courses.php" class="btn-back animate__animated animate__fadeInLeft">
        <i class="fas fa-arrow-left"></i> Späť na kurzy
    </a>
</div>

<script>
    // Добавляем эффект ripple для кнопки удаления
    document.getElementById('deleteBtn').addEventListener('click', function(e) {
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

    // Подтверждение удаления
    document.getElementById('deleteForm').addEventListener('submit', function(e) {
        if (!confirm('Naozaj chcete vymazať tento test? Táto akcia je nevratná!')) {
            e.preventDefault();
        }
    });

    // Анимация при фокусе на полях формы
    document.querySelectorAll('button').forEach(button => {
        button.addEventListener('focus', function() {
            this.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => {
                this.classList.remove('animate__animated', 'animate__pulse');
            }, 1000);
        });
    });
</script>

</body>
</html>