<?php
session_start();
require_once __DIR__ . '/../db.php';

// Разрешаем доступ без авторизации только к первому модулю
$allow_guest_access = false;
$module_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 1);

if (!isset($_SESSION['user_id'])) {
    // Проверяем, запрашивается ли первый модуль
    $stmt = $conn->prepare("SELECT topic_order FROM course_topics WHERE id = ?");
    $stmt->bind_param("i", $module_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$result || $result['topic_order'] != 1) {
        header("Location: ../login.php");
        exit;
    }
    
    $allow_guest_access = true;
    $user_id = 0;
    $user_role = 'guest';
} else {
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['role'] ?? 'student';
}

// Получаем данные модуля
$query = "
    SELECT ct.*, c.name as course_name, 
           (SELECT topic_order FROM course_topics WHERE id = ?) as current_order
";
if (!$allow_guest_access) {
    $query .= ", up.completed, up.completion_date";
}

$query .= "
    FROM course_topics ct
    JOIN courses c ON ct.course_id = c.id
";

if (!$allow_guest_access) {
    $query .= " LEFT JOIN user_progress up ON ct.id = up.topic_id AND up.user_id = ?";
}

$query .= " WHERE ct.id = ?";

$stmt = $conn->prepare($query);

if ($allow_guest_access) {
    $stmt->bind_param("ii", $module_id, $module_id);
} else {
    $stmt->bind_param("iii", $module_id, $user_id, $module_id);
}

$stmt->execute();
$module = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$module) {
    header("Location: ../course_list.php?error=module_not_found");
    exit;
}

// Проверяем доступ к модулю (только для зарегистрированных)
if (!$allow_guest_access) {
    $is_unlocked = true;
    if ($module['current_order'] > 1) {
        $stmt = $conn->prepare("
            SELECT ct.id, up.completed 
            FROM course_topics ct
            LEFT JOIN user_progress up ON ct.id = up.topic_id AND up.user_id = ?
            WHERE ct.course_id = ? AND ct.topic_order = ?
        ");
        $prev_order = $module['current_order'] - 1;
        $stmt->bind_param("iii", $user_id, $module['course_id'], $prev_order);
        $stmt->execute();
        $prev_completed = $stmt->get_result()->fetch_assoc();
        
        $is_unlocked = ($prev_completed && $prev_completed['completed']) || $user_role === 'teacher';
    }

    if (!$is_unlocked) {
        header("Location: theory.php?id=".($module['current_order'] - 1)."&error=complete_previous_first");
        exit;
    }
}

// Получаем следующий модуль (если пользователь авторизован)
$next_module = null;
if (!$allow_guest_access) {
    $stmt = $conn->prepare("
        SELECT id, title FROM course_topics 
        WHERE course_id = ? AND topic_order = ?
    ");
    $next_order = $module['current_order'] + 1;
    $stmt->bind_param("ii", $module['course_id'], $next_order);
    $stmt->execute();
    $next_module = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Обновляем дату завершения если нужно (только для зарегистрированных)
if (!$allow_guest_access && $module['completed'] && empty($module['completion_date'])) {
    $stmt = $conn->prepare("
        UPDATE user_progress 
        SET completion_date = CURRENT_TIMESTAMP 
        WHERE user_id = ? AND topic_id = ?
    ");
    $stmt->bind_param("ii", $user_id, $module_id);
    $stmt->execute();
    $module['completion_date'] = date('Y-m-d H:i:s');
    $stmt->close();
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
    <title><?= htmlspecialchars($module['title']) ?> - <?= htmlspecialchars($module['course_name']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/theory.css">
</head>
<body>
    <div class="header">
        <h1><?= htmlspecialchars($module['title']) ?></h1>
        <div class="module-meta">
            <?= htmlspecialchars($module['course_name']) ?> • Module <?= $module['current_order'] ?>
            <?php if (!$allow_guest_access && $module['completed']): ?>
                <span class="progress-badge">Completed</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="theory-container">
        <?= $module['theory_content'] ?>
        
        <div class="module-actions">
            <?php if ($allow_guest_access): ?>
                <div class="guest-warning">
                    <p>You are viewing this module as a guest. <a href="../register.php">Register</a> or <a href="../login.php">login</a> to save your progress and access all features.</p>
                    <a href="module_tests.php?module_id=<?= $module_id ?>&guest=1" class="btn">
                        Try Test (demo)
                    </a>
                </div>
            <?php elseif (!$module['completed']): ?>
                <a href="module_tests.php?module_id=<?= $module_id ?>" class="btn">
                    Take Module Test
                </a>
                <p class="note">You must score at least <?= $module['required_test_score'] ?? 70 ?>% to complete this module</p>
            <?php else: ?>
                <p class="note">You've completed this module on <?= date('F j, Y', strtotime($module['completion_date'])) ?></p>
            <?php endif; ?>
        </div>
        
        <div class="module-navigation">
            <?php if ($module['current_order'] > 1): ?>
                <a href="theory.php?id=<?= $module_id - 1 ?>" class="btn btn-secondary">
                    ← Previous Module
                </a>
            <?php else: ?>
            <a href="<?= $back_to_course_url ?>?id=<?= $module['course_id'] ?>" class="btn btn-secondary">
                ← Back to Course
            </a>
            <?php endif; ?>
            
            <?php if (!$allow_guest_access && $next_module): ?>
                <a href="theory.php?id=<?= $next_module['id'] ?>" class="btn btn-success <?= !$module['completed'] ? 'disabled' : '' ?>">
                    Next Module →
                </a>
            <?php elseif (!$allow_guest_access): ?>
                <a href="course_page.php?id=<?= $module['course_id'] ?>" class="btn btn-success">
                    Finish Course →
                </a>
            <?php else: ?>
                <a href="../register.php" class="btn btn-success">
                    Register to Continue →
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>