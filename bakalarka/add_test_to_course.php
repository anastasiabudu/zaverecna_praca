<?php
session_start();
require_once 'db.php';

if ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'supervisor') {
    header("Location: test.php");
    exit();
}

$courses_query = "SELECT id, name FROM courses";
$courses_result = $conn->query($courses_query);
$courses = [];
while ($row = $courses_result->fetch_assoc()) {
    $courses[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $question = trim($_POST['question']);
    $option1 = trim($_POST['option1']);
    $option2 = trim($_POST['option2']);
    $option3 = trim($_POST['option3']);
    $option4 = trim($_POST['option4']);
    $correct_answer_index = $_POST['correct_answer']; // Получаем индекс правильного ответа (0, 1, 2 или 3)

    if (empty($course_id) || empty($question) || empty($option1) || empty($option2) || empty($option3) || empty($option4) || !isset($correct_answer_index)) {
        $_SESSION['error'] = "Všetky polia sú povinné.";
    } else {
        $course_name_query = "SELECT name FROM courses WHERE id = ?";
        $stmt = $conn->prepare($course_name_query);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $stmt->bind_result($course_name);
        $stmt->fetch();
        $stmt->close();

        // Создаем массив вариантов ответа
        $options_array = [$option1, $option2, $option3, $option4];
        $options = json_encode($options_array);
        
        // Получаем текст правильного ответа по индексу
        $correct_answer_value = $options_array[$correct_answer_index];

        $query = "INSERT INTO tests (test_name, question, options, answer) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $test_name = "$course_name";
        $stmt->bind_param("ssss", $test_name, $question, $options, $correct_answer_value); // Сохраняем текст ответа

        if ($stmt->execute()) {
            $test_id = $stmt->insert_id;

            $query = "INSERT INTO course_tests (course_id, test_id) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $course_id, $test_id);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Test bol úspešne pridaný!";
                header("Location: courses.php");
                exit();
            } else {
                $_SESSION['error'] = "Chyba pri pridávaní testu do kurzu: " . $stmt->error;
            }
        } else {
            $_SESSION['error'] = "Chyba pri vytváraní testu: " . $stmt->error;
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
    <title>Pridať test do kurzu</title>
    <!-- Подключаем библиотеки анимаций -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --success-color: #4bb543;
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
        
        .test-container {
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
        
        .test-container::before {
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
        
        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .success-message {
            background: rgba(75, 181, 67, 0.2);
            color: var(--success-color);
        }
        
        .error-message {
            background: rgba(255, 51, 51, 0.2);
            color: var(--error-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
        }
        
        select, input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            box-sizing: border-box;
            background-color: #f8f9fa;
        }
        
        select:focus, input[type="text"]:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            outline: none;
            background-color: white;
        }
        
        .options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 600px) {
            .options-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 25px;
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
            width: 100%;
            margin-top: 10px;
        }
        
        .btn-submit i {
            margin-right: 8px;
            transition: transform 0.3s;
        }
        
        .btn-submit:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-submit:hover i {
            transform: scale(1.2);
        }
        
        .btn-submit:active {
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
    </style>
</head>
<body>

<div class="test-container animate__animated animate__fadeIn">
    <h1><i class="fas fa-plus-circle"></i> Pridať test do kurzu</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error-message animate__animated animate__shakeX">
            <i class="fas fa-exclamation-circle"></i> <?php
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="course_id"><i class="fas fa-book-open"></i> Vyberte kurz:</label>
            <select name="course_id" id="course_id" required>
                <option value="">Vyberte kurz</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo htmlspecialchars($course['id']); ?>">
                        <?php echo htmlspecialchars($course['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="question"><i class="fas fa-question-circle"></i> Otázka:</label>
            <input type="text" name="question" id="question" required>
        </div>

        <div class="options-grid">
            <div class="form-group">
                <label for="option1"><i class="fas fa-dot-circle"></i> Možnosť 1:</label>
                <input type="text" name="option1" id="option1" required>
            </div>
            <div class="form-group">
                <label for="option2"><i class="fas fa-dot-circle"></i> Možnosť 2:</label>
                <input type="text" name="option2" id="option2" required>
            </div>
            <div class="form-group">
                <label for="option3"><i class="fas fa-dot-circle"></i> Možnosť 3:</label>
                <input type="text" name="option3" id="option3" required>
            </div>
            <div class="form-group">
                <label for="option4"><i class="fas fa-dot-circle"></i> Možnosť 4:</label>
                <input type="text" name="option4" id="option4" required>
            </div>
        </div>

        <div class="form-group">
            <label for="correct_answer"><i class="fas fa-check-circle"></i> Správna odpoveď:</label>
            <select name="correct_answer" id="correct_answer" required>
                <option value="">Vyberte správnu odpoveď</option>
                <option value="0">Možnosť 1</option>
                <option value="1">Možnosť 2</option>
                <option value="2">Možnosť 3</option>
                <option value="3">Možnosť 4</option>
            </select>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
            <i class="fas fa-plus"></i> Pridať test
        </button>
    </form>

    <a href="courses.php" class="btn-back animate__animated animate__fadeInLeft">
        <i class="fas fa-arrow-left"></i> Späť na kurzy
    </a>
</div>

<script>
    // Добавляем эффект ripple для кнопки
    document.getElementById('submitBtn').addEventListener('click', function(e) {
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

    // Анимация при фокусе на полях ввода
    document.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => {
                this.parentElement.classList.remove('animate__animated', 'animate__pulse');
            }, 1000);
        });
    });
</script>

</body>
</html>