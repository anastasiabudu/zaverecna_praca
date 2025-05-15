<?php
session_start();

// Проверяем, авторизован ли пользователь
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$is_guest = !isset($_SESSION['user_id']);

// Подключаемся к базе данных
include "db.php";

// Загружаем тесты из базы данных
$test_name = "Python";
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
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Python Quiz</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #759cd8;
            --secondary-color: #9cb9d1;
            --dark-color: #4b6e9b;
            --dark-blue:rgb(20, 35, 57);
            --light-color: #eef0f6;
            --accent-color: #ffcc00;
            --correct-color: #28a745;
            --wrong-color: #dc3545;
            --border-radius: 8px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f5f5 0%, #e0e9f5 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .quiz-container {
            background-color: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 700px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        h1 {
            color: var(--dark-color);
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
            position: relative;
            padding-bottom: 10px;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--accent-color);
            border-radius: 3px;
        }

        .progress {
            text-align: center;
            margin-bottom: 25px;
            font-size: 16px;
            color: var(--dark-color);
            font-weight: 500;
            background: rgba(117, 156, 216, 0.2);
            padding: 8px;
            border-radius: 50px;
        }

        .question {
            margin: 20px 0;
            font-size: 20px;
            color: var(--dark-color);
            text-align: center;
            font-weight: 600;
            line-height: 1.5;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .options {
            margin: 20px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .option {
            display: flex;
            align-items: center;
            margin: 8px 0;
            padding: 15px 20px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            width: 90%;
            max-width: 500px;
            text-align: left;
            font-size: 16px;
            cursor: pointer;
            border: none;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .option:hover {
            background-color: var(--dark-color);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .option.selected {
            background-color: var(--dark-blue);
            transform: scale(1.02);
            box-shadow: 0 8px 16px rgba(44, 78, 128, 0.3); /* Тень в тон цвету */
        }
        
        .option.selected::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 15px;
            color: white;
            animation: fadeIn 0.5s;
        }
        
        .option.correct {
            background-color: var(--correct-color);
        }
        
        .option.wrong {
            background-color: var(--wrong-color);
        }

        .result {
            margin-top: 25px;
            font-size: 22px;
            color: var(--dark-color);
            text-align: center;
            font-weight: 600;
            min-height: 30px;
        }

        .button-container {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 25px;
            background-color: var(--primary-color);
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
            background-color: var(--dark-color);
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        .button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .pagination {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            width: 100%;
        }
        
        .result-panel {
            display: none;
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            background-color: rgba(117, 156, 216, 0.1);
            border-radius: var(--border-radius);
        }
        
        .result-actions {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap:wrap;
        }
        
        .result-actions .button {
            min-width: 150px;
            flex: 1 1 auto; /* Добавьте это свойство */
            box-sizing: border-box; /* Добавьте это свойство */
        }
        
        .try-again {
            background-color: var(--accent-color);
            color: #333;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @media (max-width: 768px) {
            .quiz-container {
                padding: 20px;
            }
            
            .options {
                width: 100%;
            }
            
            .option {
                width: 100%;
                padding: 12px 15px;
            }
            
            .pagination, .result-actions {
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

<div class="quiz-container animate__animated animate__fadeIn">
    <h1><i class="fab fa-python"></i> Python Quiz</h1>
    <div class="progress" id="progress">Question 1 of <?= count($questions) ?></div>
    <div id="quiz"></div>
    <div class="pagination" id="pagination">
        <button class="button" id="prevButton" onclick="prevQuestion()" disabled>
            <i class="fas fa-arrow-left"></i> Back
        </button>
        <button class="button" id="nextButton" onclick="nextQuestion()">
            Next <i class="fas fa-arrow-right"></i>
        </button>
    </div>
    
    <!-- Панель результатов (изначально скрыта) -->
    <div class="result-panel" id="resultPanel">
        <div class="result" id="result"></div>
        <div class="result-actions">
            <button class="button try-again" onclick="restartQuiz()">
                <i class="fas fa-redo"></i> Try Again
            </button>
            <button class="button" onclick="reviewAnswers()">
                <i class="fas fa-list-ol"></i> Review Answers
            </button>
            <a href="test.php" class="button">
                <i class="fas fa-language"></i> Other Tests
            </a>
        </div>
    </div>
</div>

<script>
const userId = <?php echo json_encode($user_id); ?>;
const isGuest = <?php echo json_encode($is_guest); ?>;
const testName = "Python";
    
const questions = <?php echo json_encode($questions); ?>;
let currentQuestionIndex = 0;
let score = 0;
let userAnswers = new Array(questions.length).fill(null);
let quizCompleted = false;

function displayQuestion() {
    const quizDiv = document.getElementById("quiz");
    quizDiv.innerHTML = "";
    
    // Обновляем прогресс
    document.getElementById("progress").innerText = `Question ${currentQuestionIndex + 1} of ${questions.length}`;

    const questionElement = document.createElement("div");
    questionElement.classList.add("question");
    questionElement.innerHTML = questions[currentQuestionIndex].question;
    quizDiv.appendChild(questionElement);

    const optionsContainer = document.createElement("div");
    optionsContainer.classList.add("options");

    questions[currentQuestionIndex].options.forEach(option => {
        const optionElement = document.createElement("button");
        optionElement.classList.add("option");
        
        if (quizCompleted) {
            // В режиме просмотра ответов
            if (option === questions[currentQuestionIndex].answer) {
                optionElement.classList.add("correct");
            } else if (userAnswers[currentQuestionIndex] === option) {
                optionElement.classList.add("wrong");
            }
            optionElement.innerHTML = option;
        } else {
            // В режиме тестирования
            if (userAnswers[currentQuestionIndex] === option) {
                optionElement.classList.add("selected");
            }
            optionElement.innerHTML = option;
            optionElement.onclick = () => selectAnswer(option);
        }
        
        optionsContainer.appendChild(optionElement);
    });

    quizDiv.appendChild(optionsContainer);

    // Обновляем кнопки пагинации
    document.getElementById("prevButton").disabled = currentQuestionIndex === 0;

    const nextButton = document.getElementById("nextButton");
    if (currentQuestionIndex === questions.length - 1) {
        if (quizCompleted) {
            nextButton.innerHTML = '<i class="fas fa-arrow-left"></i> Back to Results';
            nextButton.onclick = showResults;
        } else {
            nextButton.innerHTML = '<i class="fas fa-check"></i> Finish';
            nextButton.onclick = finishQuiz;
        }
    } else {
        nextButton.innerHTML = 'Next <i class="fas fa-arrow-right"></i>';
        nextButton.onclick = nextQuestion;
    }
    
    if (!quizCompleted) {
         nextButton.disabled = false;
    }
}

function selectAnswer(selected) {
    const options = document.querySelectorAll('.option');
    options.forEach(option => {
        option.classList.remove('selected', 'animate__animated', 'animate__pulse');
    });
    
    userAnswers[currentQuestionIndex] = selected;
    
    const selectedOption = Array.from(options).find(option => option.innerText === selected);
    if (selectedOption) {
        selectedOption.classList.add('selected', 'animate__animated', 'animate__pulse');
    }
    
}

function prevQuestion() {
    if (currentQuestionIndex > 0) {
        currentQuestionIndex--;
        displayQuestion();
    }
}

function nextQuestion() {
    if (currentQuestionIndex < questions.length - 1) {
        currentQuestionIndex++;
        displayQuestion();
    }
}

function finishQuiz() {
    score = userAnswers.filter((answer, index) => answer === questions[index].answer).length;
    quizCompleted = true;
    
    // Показываем панель результатов
    document.getElementById("resultPanel").style.display = "block";
    document.getElementById("result").innerHTML = `
        <h3>Quiz Completed!</h3>
        <p>Your score: <strong>${score}/${questions.length}</strong></p>
    `;
    
    // Скрываем пагинацию
    document.getElementById("pagination").style.display = "none";
    
    // Сохраняем результаты (если пользователь авторизован)
    if (!isGuest) {
        saveResults();
    }
}

function reviewAnswers() {
    currentQuestionIndex = 0;
    quizCompleted = true;
    displayQuestion();
    document.getElementById("resultPanel").style.display = "none";
    document.getElementById("pagination").style.display = "flex";
}

function showResults() {
    currentQuestionIndex = questions.length - 1;
    quizCompleted = true;
    document.getElementById("resultPanel").style.display = "block";
    document.getElementById("pagination").style.display = "none";
    displayQuestion();
}

function restartQuiz() {
    currentQuestionIndex = 0;
    score = 0;
    quizCompleted = false;
    userAnswers = new Array(questions.length).fill(null);
    document.getElementById("resultPanel").style.display = "none";
    document.getElementById("pagination").style.display = "flex";
    displayQuestion();
}

function saveResults() {
    const results = {
        score: score,
        totalQuestions: questions.length,
        questions: questions.map((q, i) => ({
            question: q.question,
            userAnswer: userAnswers[i],
            correctAnswer: q.answer
        }))
    };

    fetch('../save_results.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            student_id: userId,
            test_name: testName,
            score: score,
            total_questions: questions.length,
            results: results
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log("Results saved:", data);
    })
    .catch(error => {
        console.error("Error saving results:", error);
    });
}

// Инициализация теста
displayQuestion();
</script>
</body>
</html>