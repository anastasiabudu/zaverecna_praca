<?php
session_start();
include "../db.php";

if ($conn === null) {
    die("Ошибка подключения к базе данных");
}

// Получаем ID курса из параметра (по умолчанию 1 - Python)
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 1;

// Для незалогиненных пользователей
if (!isset($_SESSION['user_id'])) {
    $is_temp_user = true;
    $user_id = 0;
    $_SESSION['user_name'] = 'Guest';
} else {
    $is_temp_user = false;
    $user_id = $_SESSION['user_id'];
    
    // Получаем имя пользователя из БД
    $stmt = $conn->prepare("SELECT name FROM students WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['user_name'] = $result->fetch_assoc()['name'];
    }
    $stmt->close();
}

// Получаем информацию о курсе
$stmt = $conn->prepare("SELECT id, name, description FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    die("Course not found");
}

// Получаем данные о модулях для выбранного курса
$stmt = $conn->prepare("
    SELECT 
        id,
        title,
        content,
        topic_order as module_order,
        theory_content
    FROM course_topics
    WHERE course_id = ?
    ORDER BY topic_order
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$topics = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Для зарегистрированных пользователей получаем прогресс
$user_progress = [];
if (!$is_temp_user) {
    $stmt = $conn->prepare("
        SELECT topic_id, completed, completion_date 
        FROM user_progress 
        WHERE user_id = ? AND course_id = ?
    ");
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $user_progress[$row['topic_id']] = $row;
    }
    $stmt->close();
}

// Формируем данные о прогрессе
$progress = [];
$prev_completed = true;

foreach ($topics as $topic) {
    $topic_id = $topic['id'];
    
    $module_completed = $is_temp_user ? false : ($user_progress[$topic_id]['completed'] ?? false);
    $completion_date = $is_temp_user ? null : ($user_progress[$topic_id]['completion_date'] ?? null);
    
    $is_unlocked = ($topic['module_order'] == 1) || 
                  (!$is_temp_user && $prev_completed);
    
    $progress[$topic_id] = [
        'title' => $topic['title'],
        'content' => $topic['content'],
        'theory_content' => $topic['theory_content'],
        'unlocked' => $is_unlocked,
        'completed' => $module_completed,
        'order' => $topic['module_order'],
        'completion_date' => $completion_date
    ];
    
    $prev_completed = $module_completed;


    // Проверяем, завершены ли все модули
$all_modules_completed = false;
if (!$is_temp_user && count($topics) > 0) {
    $all_modules_completed = true;
    foreach ($topics as $topic) {
        if (empty($user_progress[$topic['id']]['completed'])) {
            $all_modules_completed = false;
            break;
        }
    }
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['name']) ?> Course</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/title.css">
</head>
<body>
    <div class="course-container">
        <!-- Шапка профиля -->
        <div class="profile-header">
            <div class="profile-info">
                <div class="profile-avatar">
                    <?= $is_temp_user ? 'G' : substr($_SESSION['user_name'] ?? 'U', 0, 1) ?>
                </div>
                <div>
                    <h3 style="margin:0"><?= $is_temp_user ? 'Guest' : htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></h3>
                    <small><?= $is_temp_user ? 'View only' : 'Registered user' ?></small>
                </div>
            </div>
            <div class="auth-buttons">
                <?php if ($is_temp_user): ?>
                    <a href="../register.php" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                    <a href="../login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
        


        <div class="course-header">
            <h1><i class="fas fa-code"></i> <?= htmlspecialchars($course['name']) ?> Course</h1>
            <p class="course-description"><?= htmlspecialchars($course['description']) ?></p>
            
            <?php if ($all_modules_completed): ?>
                <div class="course-completion-message">
                    <div class="confetti-container"></div>
                    <h2><i class="fas fa-trophy"></i> Congratulations!</h2>
                    <p>You have successfully completed all modules of this course!</p>
                    <div class="completion-badge">
                        <i class="fas fa-medal"></i> Course Master
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Индикатор прогресса -->
        <div class="progress-indicator">
            <?php foreach ($progress as $topic_id => $topic): ?>
                <div class="progress-step 
                    <?= $topic['completed'] ? 'completed' : '' ?>
                    <?= ($topic['unlocked'] && !$topic['completed']) ? 'active' : '' ?>
                    <?= !$topic['unlocked'] ? 'locked' : '' ?>">
                    <?= $topic['order'] ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="modules-list">
            <?php foreach ($progress as $topic_id => $topic): ?>
                <div class="module <?= $topic['completed'] ? 'completed' : '' ?> 
                    <?= $topic['unlocked'] ? 'unlocked' : 'locked' ?>
                    <?= $topic['order'] === 1 ? 'active' : '' ?>" 
                    data-module-id="<?= $topic_id ?>">
                    
                    <div class="module-header">
                        <h2>
                            <i class="fas fa-<?= $topic['completed'] ? 'check-circle' : 
                                ($topic['unlocked'] ? 'lock-open' : 'lock') ?>"></i>
                            Module <?= $topic['order'] ?>: <?= htmlspecialchars($topic['title']) ?>
                        </h2>
                        <span class="status-badge <?= $topic['completed'] ? 'completed-badge' : 
                            ($topic['unlocked'] ? 'unlocked-badge' : 'locked-badge') ?>">
                            <?= $topic['completed'] ? 'Completed' : 
                                ($topic['unlocked'] ? 'Unlocked' : 'Locked') ?>
                        </span>
                    </div>
                    
                    <div class="module-content">
                        <?php if ($topic['unlocked']): ?>
                            <div class="module-summary">
                                <h3><i class="fas fa-info-circle"></i> Module Overview</h3>
                                <p><?= nl2br(htmlspecialchars($topic['content'])) ?></p>
                                
                                <?php if ($topic['theory_content']): ?>
                                    <a href="../tests_theory/theory.php?topic_id=<?= $topic_id ?>&course_id=<?= $course_id ?>" class="btn btn-theory">
                                        <i class="fas fa-book-open"></i> Study Theory
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($is_temp_user && $topic['order'] == 1): ?>
                                    <a href="../tests_theory/module_tests.php?module_id=<?= $topic_id ?>&course_id=<?= $course_id ?>" class="btn btn-warning demo-mode">
                                        <i class="fas fa-question-circle"></i> Try Test (demo)
                                    </a>
                                    <div class="note">
                                        <p>Progress won't be saved. Register to save results.</p>
                                    </div>
                                <?php elseif (!$is_temp_user): ?>
                                    <a href="../tests_theory/module_tests.php?module_id=<?= $topic_id ?>&course_id=<?= $course_id ?>" class="btn btn-warning">
                                        <i class="fas fa-question-circle"></i> Take Test
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($topic['completed']): ?>
                                <p><strong><i class="fas fa-check-circle"></i> 
                                    Completed on <?= $topic['completion_date'] ?? date('Y-m-d') ?>
                                </strong></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="unlock-message">
                                <p><i class="fas fa-lock"></i> To unlock this module:</p>
                                <ul>
                                    <li><?= $is_temp_user ? 'Register or login to continue' : 'Complete previous module' ?></li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Back to Courses button at the bottom -->
        <div class="back-to-courses">
            <a href="../test.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to All Courses
            </a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Открываем первый модуль по умолчанию
            $('.module.unlocked:first').addClass('active');
            
            // Обработка клика по заголовку модуля
            $('.module-header').click(function() {
                const module = $(this).parent();
                if (module.hasClass('locked')) return;
                
                const moduleContent = module.find('.module-content');
                
                if (module.hasClass('active')) {
                    module.removeClass('active');
                    moduleContent.css('max-height', '0');
                } else {
                    $('.module').removeClass('active');
                    $('.module-content').css('max-height', '0');
                    module.addClass('active');
                    moduleContent.css('max-height', moduleContent[0].scrollHeight + 'px');
                }
            });
            
            $('.btn.demo-mode').click(function(e) {
                if (!confirm('You are in demo mode. Test results won\'t be saved. Continue?')) {
                    e.preventDefault();
                }
            });
        });


    $(document).ready(function() {
        function createConfetti() {
            const container = $('.confetti-container');
            const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff'];
            
            for (let i = 0; i < 100; i++) {
                const confetti = $('<div class="confetti"></div>');
                confetti.css({
                    'position': 'absolute',
                    'width': '10px',
                    'height': '10px',
                    'background-color': colors[Math.floor(Math.random() * colors.length)],
                    'left': Math.random() * 100 + '%',
                    'top': '-10px',
                    'border-radius': '50%',
                    'z-index': '10'
                });
                
                container.append(confetti);
                
                const animationDuration = Math.random() * 3000 + 2000;
                
                confetti.animate({
                    'top': '100%',
                    'opacity': 0,
                    'transform': 'rotate(' + (Math.random() * 360) + 'deg)'
                }, animationDuration, function() {
                    $(this).remove();
                });
            }
        }
        
        // Запускаем конфетти сразу и затем каждые 3 секунды
        createConfetti();
        setInterval(createConfetti, 3000);
    });

    </script>
</body>
</html>