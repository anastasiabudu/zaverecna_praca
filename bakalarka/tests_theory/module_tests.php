<?php
session_start();
include "../db.php";

$is_temp_user = !isset($_SESSION['user_id']);
$user_id = $is_temp_user ? 0 : $_SESSION['user_id']; // 0 для гостей

$module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 
            (isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 
            (isset($_GET['id']) ? (int)$_GET['id'] : 1));
// Получаем информацию о модуле
$stmt = $conn->prepare("SELECT * FROM course_topics WHERE id = ?");
$stmt->bind_param("i", $module_id);
$stmt->execute();
$module = $stmt->get_result()->fetch_assoc();


// После получения информации о модуле
$stmt = $conn->prepare("SELECT MAX(topic_order) as last_module FROM course_topics WHERE course_id = ?");
$stmt->bind_param("i", $module['course_id']);
$stmt->execute();
$last_module = $stmt->get_result()->fetch_assoc();

$is_last_module = ($module['topic_order'] == $last_module['last_module']);

// Проверяем существование модуля
if (!$module) {
    header("Location: course_list.php?error=module_not_found");
    exit;
}

// Получаем тесты для текущего модуля
$stmt = $conn->prepare("
    SELECT t.id, t.question, t.options, t.answer 
    FROM tests t
    JOIN course_tests ct ON t.id = ct.test_id
    WHERE ct.course_id = ? 
    AND ct.topic_id = ?
    ORDER BY RAND() 
    LIMIT 5
");
$stmt->bind_param("ii", $module['course_id'], $module_id);
$stmt->execute();
$tests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Проверяем, что тесты найдены
if (empty($tests)) {
    die("No tests found for this module. Please contact administrator.");
}

// Обработка отправки теста
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answers'])) {
    $score = 0;
    $total = count($tests);
    $all_answered = true;
    
    // Проверяем, что все вопросы отвечены
    foreach ($tests as $test) {
        if (!isset($_POST['answers'][$test['id']])) {
            $all_answered = false;
            break;
        }
    }
    
    if (!$all_answered) {
        header("Location: module_tests.php?module_id=$module_id&error=1");
        exit;
    }
    
    // Проверяем ответы
    $results = [];
    foreach ($_POST['answers'] as $test_id => $user_answer) {
        foreach ($tests as $test) {
            if ($test['id'] == $test_id) {
                $is_correct = $user_answer === $test['answer'];
                if ($is_correct) {
                    $score++;
                }
                $results[] = [
                    'question' => $test['question'],
                    'user_answer' => $user_answer,
                    'correct_answer' => $test['answer'],
                    'is_correct' => $is_correct,
                    'options' => json_decode($test['options'], true),
                    'test_id' => $test['id']
                ];
                break;
            }
        }
    }
    
    // Сохраняем результат в сессии
    $_SESSION['test_results'] = [
        'score' => $score,
        'total' => $total,
        'results' => $results,
        'module_id' => $module_id,
        'module_title' => $module['title'],
        'show_results' => true,
        'is_temp_user' => $is_temp_user // Добавляем флаг временного пользователя
    ];
    
    // Сохраняем результат в базу данных только для зарегистрированных пользователей
    if (!$is_temp_user) {
        $passing_score = ceil($total * 0.6);
        $passed = $score >= $passing_score;
        
        $stmt = $conn->prepare("
            INSERT INTO test_results (student_id, test_name, score, total_questions, date_taken)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $test_name = "Module " . $module['topic_order'] . ": " . $module['title'];
        $stmt->bind_param("isii", $user_id, $test_name, $score, $total);
        $stmt->execute();
        
        if ($passed) {
            $stmt = $conn->prepare("
                INSERT INTO user_progress (user_id, course_id, topic_id, completed, completion_date)
                VALUES (?, ?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE completed = 1, completion_date = NOW()
            ");
            $stmt->bind_param("iii", $user_id, $module['course_id'], $module_id);
            $stmt->execute();
        }
    }
    
    // Перенаправляем на эту же страницу без параметров POST
    header("Location: module_tests.php?module_id=$module_id");
    exit;
}

// Проверяем, нужно ли показывать результаты
$show_results = false;
$score = 0;
$total = 0;
$results = [];
$is_temp_user_result = false;

if (isset($_SESSION['test_results']) && $_SESSION['test_results']['module_id'] == $module_id) {
    $show_results = $_SESSION['test_results']['show_results'];
    $score = $_SESSION['test_results']['score'];
    $total = $_SESSION['test_results']['total'];
    $results = $_SESSION['test_results']['results'];
    $is_temp_user_result = $_SESSION['test_results']['is_temp_user'] ?? false;
    
    // Сбрасываем флаг показа результатов после первого отображения
    $_SESSION['test_results']['show_results'] = false;
}
// Определяем страницу курса на основе его ID
$course_pages = [
    1 => '../python/python_title.php', // для курса Python
    2 => '../java/java_title.php',  // для курса Java
    3 => '../html/html_title.php',   // для курса HTML
    4 => '../cpp/cpp_title.php'   // для курса C++
];

// Получаем нужную страницу или используем заглушку, если курс не найден
$back_to_course_url = $course_pages[$module['course_id']] ?? 'course_list.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module Test: <?php echo htmlspecialchars($module['title']); ?></title>
    <link rel="stylesheet" href="../css/module_tests.css">

</head>
<body>
<h1>Test for Module <?php echo $module['topic_order']; ?>: <?php echo htmlspecialchars($module['title']); ?></h1>
    
    <?php if ($show_results): ?>
        <?php if ($is_temp_user_result): ?>
            <div class="demo-warning">
                <p>⚠️ Demo mode: Your results are not saved. Register to save progress.</p>
            </div>
        <?php endif; ?>

        <?php if ($is_last_module && $score >= ceil($total * 0.6)): ?>
        <div id="confetti-container"></div>
        <div class="course-complete">
            <h2>🎉 Congratulations! 🎉</h2>
            <p>You've successfully completed the entire course!</p>
        </div>
    <?php endif; ?>
        
        <div class="test-result <?php echo ($score >= ceil($total * 0.6)) ? 'success' : 'error'; ?>">
            <h2>Test Results</h2>
            <p>You scored <?php echo $score; ?> out of <?php echo $total; ?> (<?php echo round(($score/$total)*100); ?>%)</p>
            <p>
                <?php if ($score >= ceil($total * 0.6)): ?>
                    Congratulations! You passed the test.
                <?php else: ?>
                    You didn't pass. You need at least <?php echo ceil($total * 0.6); ?> correct answers.
                <?php endif; ?>
            </p>
        </div>
        
        <div class="test-container">
            <h3>Detailed Results:</h3>
            <?php foreach ($results as $index => $result): ?>
                <div class="question">
                    <?php echo ($index + 1) . '. ' . htmlspecialchars($result['question']); ?>
                </div>
                <div class="options">
                    <?php foreach ($result['options'] as $option): ?>
                        <div class="option">
                            <input type="radio" 
                                   name="answers[<?php echo $result['test_id']; ?>]" 
                                   id="opt_<?php echo $result['test_id'] . '_' . substr($option, 0, 1); ?>" 
                                   value="<?php echo htmlspecialchars($option); ?>"
                                   <?php echo ($result['user_answer'] === $option) ? 'checked' : ''; ?>
                                   disabled>
                            <label for="opt_<?php echo $result['test_id'] . '_' . substr($option, 0, 1); ?>">
                                <?php echo htmlspecialchars($option); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="answer-feedback <?php echo $result['is_correct'] ? 'correct-answer' : 'incorrect-answer'; ?>">
                    <?php if ($result['is_correct']): ?>
                        <p>✓ Correct answer!</p>
                    <?php else: ?>
                        <p>✗ Your answer: <span class="user-selection"><?php echo htmlspecialchars($result['user_answer']); ?></span></p>
                        <p>Correct answer: <?php echo htmlspecialchars($result['correct_answer']); ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($index < count($results) - 1): ?>
                    <hr>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        
        <div class="course-actions">
    <?php if ($score < ceil($total * 0.6)): ?>
        <a href="theory.php?topic_id=<?php echo $module_id; ?>" class="btn btn-theory">Review Theory</a>
        <a href="module_tests.php?module_id=<?php echo $module_id; ?>" class="btn retake-btn">Retake Test</a>
    <?php endif; ?>
    <a href="<?php echo $back_to_course_url; ?>" class="btn back-btn">Back to Course</a>
    </div>
    <?php else: ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="message error">
                <strong>Please answer all questions</strong> before submitting the test.
            </div>
        <?php endif; ?>
        
        <div class="progress-info">
            This test contains <?php echo count($tests); ?> questions based on the module theory. 
            You need to answer all questions and get at least <?php echo ceil(count($tests) * 0.6); ?> correct answers to pass.
        </div>
        
        <form method="post" id="testForm">
            <div class="test-container">
                <?php foreach ($tests as $index => $test): ?>
                    <div class="question">
                        <?php echo ($index + 1) . '. ' . htmlspecialchars($test['question']); ?>
                    </div>
                    <div class="options">
                        <?php 
                        $options = json_decode($test['options']);
                        foreach ($options as $option): ?>
                            <div class="option">
                                <input type="radio" 
                                       name="answers[<?php echo $test['id']; ?>]" 
                                       id="opt_<?php echo $test['id'] . '_' . substr($option, 0, 1); ?>" 
                                       value="<?php echo htmlspecialchars($option); ?>"
                                       class="test-answer"
                                       required>
                                <label for="opt_<?php echo $test['id'] . '_' . substr($option, 0, 1); ?>">
                                    <?php echo htmlspecialchars($option); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($index < count($tests) - 1): ?>
                        <hr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <div class="button-container">
    <a href="<?php echo $back_to_course_url; ?>" class="top-back-btn">
        <i class="fas fa-arrow-left"></i> Back to Course
    </a>
    <button type="submit" class="submit-btn" id="submitBtn" disabled>
        <i class="fas fa-paper-plane"></i> Submit Test
    </button>
</div>
        </form>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('testForm');
            if (form) {
                const submitBtn = document.getElementById('submitBtn');
                const answerInputs = document.querySelectorAll('.test-answer');
                
                function checkAnswers() {
                    let allAnswered = true;
                    const questionIds = <?php echo json_encode(array_column($tests, 'id')); ?>;
                    
                    questionIds.forEach(id => {
                        const answered = document.querySelector(`input[name="answers[${id}]"]:checked`);
                        if (!answered) {
                            allAnswered = false;
                        }
                    });
                    
                    submitBtn.disabled = !allAnswered;
                }
                
                answerInputs.forEach(input => {
                    input.addEventListener('change', checkAnswers);
                });
                
                checkAnswers();
            }
        });

        <?php if ($show_results && $is_last_module && $score >= ceil($total * 0.6)): ?>
        function createConfetti() {
            const container = document.getElementById('confetti-container');
            const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff'];
            
            for (let i = 0; i < 150; i++) {
                const confetti = document.createElement('div');
                confetti.style.position = 'absolute';
                confetti.style.width = '10px';
                confetti.style.height = '10px';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.top = '-10px';
                confetti.style.borderRadius = '50%';
                
                const animation = confetti.animate([
                    { top: '-10px', transform: 'rotate(0deg)', opacity: 1 },
                    { top: '100vh', transform: 'rotate(360deg)', opacity: 0 }
                ], {
                    duration: Math.random() * 3000 + 2000,
                    easing: 'cubic-bezier(0.1, 0.8, 0.9, 1)',
                    delay: Math.random() * 2000
                });
                
                container.appendChild(confetti);
                animation.onfinish = () => confetti.remove();
            }
        }
        
        // Запускаем конфетти сразу и затем каждые 3 секунды
        createConfetti();
        setInterval(createConfetti, 3000);
    <?php endif; ?>
        
    </script>
</body>
</html>
