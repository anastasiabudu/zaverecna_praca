<?php
session_start();

// Проверяем, авторизован ли пользователь
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$is_guest = !isset($_SESSION['user_id']);

// Подключаемся к базе данных
include "../db.php";

// Загружаем тесты из базы данных
$test_name = "HTML";
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTML Quiz</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --success-color: #4ade80;
            --danger-color: #f87171;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--dark-color);
            line-height: 1.6;
        }

        .quiz-container {
            background-color: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .quiz-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }

        h1 {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
        }

        .progress-container {
            width: 100%;
            background-color: #e0e0e0;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            height: 10px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 10px;
            transition: width 0.4s ease;
        }

        .progress-text {
            font-size: 0.9rem;
            color: var(--dark-color);
            text-align: center;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .question {
            margin: 1.5rem 0;
            font-size: 1.3rem;
            color: var(--primary-color);
            text-align: center;
            font-weight: 600;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 1rem;
        }

        .options {
            margin: 1.5rem 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            width: 100%;
        }

        .option {
            display: block;
            padding: 1rem 1.5rem;
            background-color: white;
            color: var(--dark-color);
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: var(--transition);
            width: 100%;
            text-align: center;
            font-size: 1rem;
            cursor: pointer;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .option:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }

        .option.selected {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .option.correct {
            background-color: var(--success-color);
            color: white;
            border-color: var(--success-color);
        }

        .option.incorrect {
            background-color: var(--danger-color);
            color: white;
            border-color: var(--danger-color);
            opacity: 0.7;
        }

        .result {
            margin-top: 2rem;
            font-size: 1.5rem;
            color: var(--dark-color);
            text-align: center;
            font-weight: 600;
            padding: 1rem;
            border-radius: 8px;
            background-color: rgba(67, 97, 238, 0.1);
        }

        .button-container {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .button {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
            font-size: 1rem;
            cursor: pointer;
            border: none;
            font-weight: 600;
            box-shadow: var(--shadow);
            min-width: 120px;
            text-align: center;
        }

        .button:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .button:active {
            transform: translateY(0);
        }

        .button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .button.secondary {
            background-color: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .button.secondary:hover {
            background-color: #f0f4ff;
        }

        .pagination {
            margin-top: 2rem;
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .score-display {
            font-size: 1.2rem;
            margin-top: 1rem;
            text-align: center;
        }

        .score-value {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        .feedback {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 8px;
            background-color: rgba(248, 113, 113, 0.1);
            color: var(--dark-color);
        }

        .feedback.correct {
            background-color: rgba(74, 222, 128, 0.1);
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .shake {
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .quiz-container {
                padding: 1.5rem;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .question {
                font-size: 1.1rem;
            }
            
            .options {
                grid-template-columns: 1fr;
            }
            
            .button {
                padding: 0.7rem 1.2rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .quiz-container {
                padding: 1rem;
            }
            
            .pagination {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .button {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="quiz-container animate__animated animate__fadeIn">
    <h1>HTML Knowledge Test</h1>
    <div class="progress-container">
        <div class="progress-bar" id="progressBar" style="width: <?= (1/count($questions))*100 ?>%"></div>
    </div>
    <div class="progress-text" id="progressText">Question 1 of <?= count($questions) ?></div>
    <div id="quiz" class="fade-in"></div>
    <div class="pagination" id="pagination">
        <button class="button secondary" id="prevButton" onclick="prevQuestion()" disabled>← Back</button>
        <button class="button" id="nextButton" onclick="nextQuestion()">Next →</button>
    </div>
    <div class="result" id="result"></div>
    <div class="button-container" id="buttonContainer"></div>
</div>

<script>
    const userId = <?php echo json_encode($user_id); ?>;
    const isGuest = <?php echo json_encode($is_guest); ?>;
    const testName = "HTML";
    
    const questions = <?php echo json_encode($questions); ?>;
    
    let currentQuestionIndex = 0;
    let score = 0;
    let userAnswers = new Array(questions.length).fill(null);
    let quizCompleted = false;

    function displayQuestion() {
        const quizDiv = document.getElementById("quiz");
        quizDiv.innerHTML = "";
        quizDiv.className = "fade-in";
        
        // Update progress
        const progressPercent = ((currentQuestionIndex + 1) / questions.length) * 100;
        document.getElementById("progressBar").style.width = `${progressPercent}%`;
        document.getElementById("progressText").innerText = 
            `Question ${currentQuestionIndex + 1} of ${questions.length}`;
        
        // Create question element
        const questionElement = document.createElement("div");
        questionElement.className = "question";
        questionElement.innerHTML = questions[currentQuestionIndex].question;
        quizDiv.appendChild(questionElement);
        
        // Create options
        const optionsContainer = document.createElement("div");
        optionsContainer.className = "options";
        
        questions[currentQuestionIndex].options.forEach(option => {
            const optionElement = document.createElement("div");
            optionElement.className = "option";
            
            if (quizCompleted) {
                if (option === questions[currentQuestionIndex].answer) {
                    optionElement.classList.add("correct");
                } else if (userAnswers[currentQuestionIndex] === option && 
                          userAnswers[currentQuestionIndex] !== questions[currentQuestionIndex].answer) {
                    optionElement.classList.add("incorrect");
                }
            } else if (userAnswers[currentQuestionIndex] === option) {
                optionElement.classList.add("selected");
            }
            
            optionElement.innerText = option;
            optionElement.onclick = () => !quizCompleted && selectAnswer(option);
            optionsContainer.appendChild(optionElement);
        });
        
        quizDiv.appendChild(optionsContainer);
        
        // Update pagination buttons
        document.getElementById("prevButton").disabled = currentQuestionIndex === 0;
        
        const nextButton = document.getElementById("nextButton");
        if (currentQuestionIndex === questions.length - 1) {
            nextButton.innerText = quizCompleted ? "Review Results" : "Finish Quiz";
            nextButton.onclick = quizCompleted ? showResults : finishQuiz;
        } else {
            nextButton.innerText = "Next →";
            nextButton.onclick = nextQuestion;
        }
        
        // Hide/show elements based on quiz state
        document.getElementById("result").style.display = quizCompleted ? "block" : "none";
    }

    function selectAnswer(selected) {
        if (quizCompleted) return;
        
        const options = document.querySelectorAll('.option');
        options.forEach(option => option.classList.remove('selected'));
        
        userAnswers[currentQuestionIndex] = selected;
        
        const selectedOption = Array.from(options).find(option => option.innerText === selected);
        if (selectedOption) {
            selectedOption.classList.add('selected');
            selectedOption.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => {
                selectedOption.classList.remove('animate__animated', 'animate__pulse');
            }, 1000);
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
        quizCompleted = true;
        score = userAnswers.filter((answer, index) => answer === questions[index].answer).length;
        
        const results = {
            score: score,
            totalQuestions: questions.length,
            questions: questions.map((question, index) => ({
                question: question.question,
                userAnswer: userAnswers[index],
                correctAnswer: question.answer
            }))
        };
        
        // Show immediate feedback
        showResults();
        
        // Send results to server
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

    function showResults() {
        const resultDiv = document.getElementById("result");
        resultDiv.innerHTML = `
            <div class="score-display">
                Your score: <span class="score-value">${score}/${questions.length}</span>
                (${Math.round((score / questions.length) * 100)}%)
            </div>
        `;
        
        const buttonContainer = document.getElementById("buttonContainer");
        buttonContainer.innerHTML = `
            <button class="button" onclick="restartQuiz()">Retake Quiz</button>
            <button class="button secondary" onclick="window.location.href='../results.php?score=${score}&total=${questions.length}&test_name=${testName}'">
                View Detailed Results
            </button>
        `;
        
        displayQuestion();
    }

    function restartQuiz() {
        currentQuestionIndex = 0;
        score = 0;
        userAnswers = new Array(questions.length).fill(null);
        quizCompleted = false;
        
        document.getElementById("result").innerHTML = "";
        document.getElementById("buttonContainer").innerHTML = "";
        document.getElementById("pagination").style.display = "flex";
        
        displayQuestion();
    }

    // Initialize quiz
    displayQuestion();
</script>

</body>
</html>