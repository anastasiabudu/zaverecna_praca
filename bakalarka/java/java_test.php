<?php
session_start();

// Проверяем, авторизован ли пользователь
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$is_guest = !isset($_SESSION['user_id']);

// Подключаемся к базе данных
include "../db.php";

// Загружаем тесты из базы данных
$test_name = "Java";
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
    <title>Java Quiz</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Source+Code+Pro:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a6fa5;
            --secondary-color: #759cd8;
            --accent-color: #ff7e33;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --text-color: #495057;
            --border-radius: 8px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f7fa;
            color: var(--text-color);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e2e8f0 100%);
        }

        .quiz-container {
            background-color: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        .quiz-container:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .quiz-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--primary-color);
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
            background-color: rgba(117, 156, 216, 0.2);
            padding: 10px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 25px;
            text-align: center;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        .question {
            margin: 20px 0;
            font-size: 20px;
            font-weight: 500;
            color: var(--dark-color);
            text-align: center;
            line-height: 1.5;
        }

        .options {
            margin: 25px 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .option {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background-color: white;
            color: var(--primary-color);
            border: 2px solid var(--secondary-color);
            border-radius: var(--border-radius);
            transition: var(--transition);
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            text-align: left;
        }

        .option:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }

        .option.selected {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
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

        .option i {
            margin-right: 10px;
            font-size: 18px;
        }

        .pagination {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 25px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            min-width: 120px;
        }

        .button:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(0, 0, 0, 0.15);
        }

        .button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .button i {
            margin-right: 8px;
        }

        .result {
            margin-top: 30px;
            padding: 20px;
            background-color: rgba(40, 167, 69, 0.1);
            border-radius: var(--border-radius);
            text-align: center;
            font-size: 18px;
            color: var(--dark-color);
            display: none;
        }

        .button-container {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        /* Progress bar */
        .progress-bar-container {
            width: 100%;
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            margin-bottom: 25px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background-color: var(--primary-color);
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .quiz-container {
            animation: fadeIn 0.6s ease-out forwards;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .quiz-container {
                padding: 20px;
            }
            
            .options {
                grid-template-columns: 1fr;
            }
            
            .pagination {
                flex-direction: column;
                gap: 10px;
            }
            
            .button {
                width: 100%;
            }
        }

        /* Code snippets in questions */
        .code-snippet {
            font-family: 'Source Code Pro', monospace;
            background-color: #2d2d2d;
            color: #f5f5f5;
            padding: 10px 15px;
            border-radius: 6px;
            margin: 10px 0;
            font-size: 15px;
            overflow-x: auto;
        }
    </style>
</head>
<body>

<div class="quiz-container">
    <h1><i class="fas fa-java"></i> Java Quiz</h1>
    
    <div class="progress-bar-container">
        <div class="progress-bar" id="progressBar" style="width: 0%"></div>
    </div>
    
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
    
    <div class="result" id="result"></div>
    <div class="button-container" id="buttonContainer"></div>
</div>

<script>
    const userId = <?php echo json_encode($user_id); ?>;
    const isGuest = <?php echo json_encode($is_guest); ?>;
    const testName = "Java";
    console.log("User ID:", userId, "Is Guest:", isGuest, "Test Name:", testName);

    const questions = <?php echo json_encode($questions); ?>;
    let currentQuestionIndex = 0;
    let score = 0;
    let userAnswers = new Array(questions.length).fill(null);
    let quizCompleted = false;

    function displayQuestion() {
        const quizDiv = document.getElementById("quiz");
        quizDiv.innerHTML = "";

        // Update progress
        document.getElementById("progress").innerText = `Question ${currentQuestionIndex + 1} of ${questions.length}`;
        document.getElementById("progressBar").style.width = `${((currentQuestionIndex + 1) / questions.length) * 100}%`;

        const questionElement = document.createElement("div");
        questionElement.classList.add("question");
        
        // Format question text (replace code markers with styled code)
        let questionText = questions[currentQuestionIndex].question;
        questionText = questionText.replace(/`(.*?)`/g, '<span class="code-snippet">$1</span>');
        questionElement.innerHTML = questionText;
        
        quizDiv.appendChild(questionElement);

        const optionsContainer = document.createElement("div");
        optionsContainer.classList.add("options");

        questions[currentQuestionIndex].options.forEach(option => {
            const optionElement = document.createElement("div");
            optionElement.classList.add("option");
            
            if (quizCompleted) {
                // After quiz completion, show correct/incorrect answers
                if (option === questions[currentQuestionIndex].answer) {
                    optionElement.classList.add("correct");
                    optionElement.innerHTML = `<i class="fas fa-check-circle"></i> ${option}`;
                } else if (userAnswers[currentQuestionIndex] === option && option !== questions[currentQuestionIndex].answer) {
                    optionElement.classList.add("incorrect");
                    optionElement.innerHTML = `<i class="fas fa-times-circle"></i> ${option}`;
                } else {
                    optionElement.innerHTML = option;
                }
            } else {
                // During quiz, show selected state
                if (userAnswers[currentQuestionIndex] === option) {
                    optionElement.classList.add("selected");
                    optionElement.innerHTML = `<i class="fas fa-dot-circle"></i> ${option}`;
                } else {
                    optionElement.innerHTML = `<i class="far fa-circle"></i> ${option}`;
                }
                optionElement.onclick = () => selectAnswer(option);
            }
            
            optionsContainer.appendChild(optionElement);
        });

        quizDiv.appendChild(optionsContainer);

        // Update pagination buttons
        document.getElementById("prevButton").disabled = currentQuestionIndex === 0;

        const nextButton = document.getElementById("nextButton");
        if (currentQuestionIndex === questions.length - 1) {
            nextButton.innerHTML = quizCompleted ? "Review Results" : `<i class="fas fa-flag-checkered"></i> Finish`;
            nextButton.onclick = quizCompleted ? reviewResults : finishQuiz;
        } else {
            nextButton.innerHTML = `Next <i class="fas fa-arrow-right"></i>`;
            nextButton.onclick = nextQuestion;
        }
        
        // Disable next button if no answer selected (only during quiz)
        if (!quizCompleted) {
            nextButton.disabled = userAnswers[currentQuestionIndex] === null;
        } else {
            nextButton.disabled = false;
        }
    }

    function selectAnswer(selected) {
        if (quizCompleted) return;
        
        userAnswers[currentQuestionIndex] = selected;
        displayQuestion();
        
        // Enable next button after selection
        document.getElementById("nextButton").disabled = false;
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
        
        const results = {
            score: score,
            totalQuestions: questions.length,
            percentage: Math.round((score / questions.length) * 100),
            questions: questions.map((question, index) => ({
                question: question.question,
                userAnswer: userAnswers[index],
                correctAnswer: question.answer,
                isCorrect: userAnswers[index] === question.answer
            }))
        };

        // Show result summary
        const resultDiv = document.getElementById("result");
        resultDiv.style.display = "block";
        resultDiv.innerHTML = `
            <h3><i class="fas fa-trophy"></i> Quiz Completed!</h3>
            <p>Your score: <strong>${score}/${questions.length}</strong> (${results.percentage}%)</p>
            ${results.percentage >= 70 ? 
                '<p style="color: var(--success-color);"><i class="fas fa-check-circle"></i> Congratulations! You passed!</p>' : 
                '<p style="color: var(--danger-color);"><i class="fas fa-exclamation-circle"></i> Keep practicing to improve your score!</p>'}
        `;

        // Update buttons
        const buttonContainer = document.getElementById("buttonContainer");
        buttonContainer.innerHTML = `
            <button class="button" onclick="restartQuiz()" style="background-color: var(--accent-color);">
                <i class="fas fa-redo"></i> Try Again
            </button>
            <button class="button" onclick="reviewResults()" style="background-color: var(--secondary-color);">
                <i class="fas fa-list-ol"></i> Review Answers
            </button>
            <a href="../test.php" class="button" style="background-color: var(--dark-color); text-decoration: none;">
                <i class="fas fa-language"></i> Other Tests
            </a>
        `;

        // Hide pagination if showing results
        document.getElementById("pagination").style.display = "none";

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
        user_answers: userAnswers,
        correct_answers: questions.map(q => q.answer)
    })
})
.then(response => {
    if (!response.ok) {
        throw new Error('Network response was not ok');
    }
    return response.json();
})
.then(data => {
    console.log("Results saved successfully:", data);
})
.catch(error => {
    console.error("Error saving results:", error);
    alert("Error saving results. Please check console for details.");
});
    }

    function reviewResults() {
        currentQuestionIndex = 0;
        displayQuestion();
        document.getElementById("pagination").style.display = "flex";
        document.getElementById("result").style.display = "none";
    }

    function restartQuiz() {
        currentQuestionIndex = 0;
        score = 0;
        quizCompleted = false;
        userAnswers = new Array(questions.length).fill(null);
        document.getElementById("result").style.display = "none";
        document.getElementById("buttonContainer").innerHTML = "";
        document.getElementById("pagination").style.display = "flex";
        document.getElementById("progressBar").style.width = "0%";
        displayQuestion();
    }

    // Initialize quiz
    displayQuestion();
</script>

</body>
</html>