<?php
session_start();
require_once 'db.php';

// Проверка роли
if ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'supervisor') {
    header("Location: test.php");
    exit();
}

// Загрузка всех курсов и тестов
$courses = [];
$query = "SELECT c.id AS course_id, c.name AS course_name, c.description, t.id AS test_id, t.test_name AS test_title 
          FROM courses c
          LEFT JOIN course_tests ct ON c.id = ct.course_id
          LEFT JOIN tests t ON ct.test_id = t.id
          ORDER BY c.id, t.id";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $course_id = $row['course_id'];
        if (!isset($courses[$course_id])) {
            $courses[$course_id] = [
                'name' => $row['course_name'],
                'description' => $row['description'],
                'tests' => []
            ];
        }
        if ($row['test_id']) {
            $courses[$course_id]['tests'][] = [
                'id' => $row['test_id'],
                'title' => $row['test_title']
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurzy a testy</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- AOS (Animate On Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --text-color: #5a5c69;
            --shadow-color: rgba(58, 59, 69, 0.15);
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: var(--text-color);
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
            overflow: hidden;
            transition: all 0.5s ease;
        }
        
        .glass-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.2);
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 30%, rgba(255,255,255,0.1) 0%, transparent 50%);
        }
        
        .course-title {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0;
            position: relative;
            display: inline-block;
        }
        
        .course-title::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 3px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }
        
        .course-title:hover::after {
            width: 100%;
        }
        
        .test-item {
            border-left: 3px solid var(--primary-color);
            padding: 1rem 1.5rem;
            margin-bottom: 0.75rem;
            background-color: rgba(248, 249, 252, 0.7);
            border-radius: 8px;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
        }
        
        .test-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(78, 115, 223, 0.1), transparent);
            transition: all 0.6s ease;
        }
        
        .test-item:hover {
            transform: translateX(10px);
            box-shadow: 0 4px 20px 0 rgba(58, 59, 69, 0.1);
        }
        
        .test-item:hover::before {
            left: 100%;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            border: none;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--primary-color) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }
        
        .btn-gradient:hover::before {
            opacity: 1;
        }
        
        .btn-pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(78, 115, 223, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(78, 115, 223, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(78, 115, 223, 0);
            }
        }
        
        .floating-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            z-index: 1000;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .floating-btn:hover {
            transform: scale(1.1) rotate(90deg);
        }
        
        .collapse-icon {
            transition: transform 0.3s ease;
        }
        
        .collapsed .collapse-icon {
            transform: rotate(-90deg);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 15px;
            backdrop-filter: blur(5px);
        }
        
        .ripple {
            position: relative;
            overflow: hidden;
        }
        
        .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.7);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple {
            to {
                transform: scale(2.5);
                opacity: 0;
            }
        }
        
        @media (max-width: 768px) {
            .page-header {
                padding: 2rem 0;
            }
            
            .course-title {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <header class="page-header animate__animated animate__fadeIn">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0" data-aos="fade-right">
                    <i class="fas fa-graduation-cap me-2"></i>
                    Kurzy a testy
                </h1>
                <a href="test.php" class="btn btn-light ripple" data-aos="fade-left">
                    <i class="fas fa-arrow-left me-1"></i> Späť
                </a>
            </div>
        </div>
    </header>

    <div class="container animate__animated animate__fadeInUp">
        <?php if (empty($courses)): ?>
            <div class="glass-card empty-state" data-aos="zoom-in">
                <div class="card-body">
                    <i class="fas fa-book-open fa-4x mb-4" style="color: #d1d3e2;"></i>
                    <h3 class="mb-3">Žiadne kurzy</h3>
                    <p class="mb-4">Momentálne nemáte žiadne vytvorené kurzy.</p>
                    <a href="create_course.php" class="btn btn-gradient btn-pulse ripple">
                        <i class="fas fa-plus me-1"></i> Vytvoriť nový kurz
                    </a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($courses as $course_id => $course): ?>
                <div class="glass-card mb-4" data-aos="fade-up">
                    <div class="card-header d-flex justify-content-between align-items-center collapsed ripple" 
                         data-bs-toggle="collapse" 
                         href="#course-<?= $course_id ?>"
                         style="cursor: pointer;">
                        <h2 class="course-title mb-0">
                            <i class="fas fa-book me-2"></i>
                            <?= htmlspecialchars($course['name']) ?>
                        </h2>
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </div>
                    <div id="course-<?= $course_id ?>" class="collapse">
                        <div class="card-body">
                            <p class="text-muted mb-4"><?= htmlspecialchars($course['description']) ?></p>
                            
                            <div class="d-flex flex-wrap mb-3 gap-2">
                                <a href="edit_course.php?id=<?= $course_id ?>" class="btn btn-outline-primary ripple">
                                    <i class="fas fa-edit me-1"></i> Upraviť kurz
                                </a>
                                <a href="add_test_to_course.php?id=<?= $course_id ?>" class="btn btn-outline-primary ripple">
                                    <i class="fas fa-plus-circle me-1"></i> Pridať test
                                </a>
                            </div>
                            
                            <?php if (!empty($course['tests'])): ?>
                                <h5 class="mb-3" data-aos="fade-right">
                                    <i class="fas fa-tasks me-2"></i>
                                    Testy v kurze
                                </h5>
                                <div class="list-group">
                                    <?php foreach ($course['tests'] as $test): ?>
                                        <div class="test-item" data-aos="fade-left">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($test['title']) ?></h6>
                                                </div>
                                                <div class="btn-group">
                                                    <a href="edit_test.php?id=<?= $test['id'] ?>" class="btn btn-sm btn-outline-primary ripple">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="delete_test.php?id=<?= $test['id'] ?>" class="btn btn-sm btn-outline-danger ripple" 
                                                       onclick="return confirm('Naozaj chcete vymazať tento test?');">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info glass-card" data-aos="fade-up">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Tento kurz nemá žiadne testy.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Floating action button -->
    <a href="create_course.php" class="floating-btn btn-gradient btn-pulse animate__animated animate__bounceIn ripple">
        <i class="fas fa-plus"></i>
    </a>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- AOS (Animate On Scroll) -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Ripple effect
        $(document).on('click', '.ripple', function(e) {
            let $this = $(this);
            let $offset = $this.offset();
            let $circle = $('<span class="ripple-effect"></span>');
            
            let x = e.pageX - $offset.left;
            let y = e.pageY - $offset.top;
            
            $circle.css({
                top: y + 'px',
                left: x + 'px'
            });
            
            $this.append($circle);
            
            setTimeout(function() {
                $circle.remove();
            }, 600);
        });
        
        // Add hover effects dynamically
        $(document).ready(function() {
            $('.glass-card').hover(
                function() {
                    $(this).addClass('animate__animated animate__pulse');
                },
                function() {
                    $(this).removeClass('animate__animated animate__pulse');
                }
            );
            
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>