<?php
session_start();

// Подключаем файл с подключением к базе данных
require_once 'db.php';

// Получаем идентификатор пользователя из сессии
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';

if ($user_id) {
    // Запрос для получения имени пользователя из базы данных
    $sql = "SELECT name FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close();

    if (!$username) {
        $username = 'Guest';
    }
} else {
    $username = 'Guest';
}

$conn = null;
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Výber programovacieho jazyka</title>
    
    <!-- Подключаем библиотеки для анимаций -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Подключаем Font Awesome для иконок -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4a6bff;
            --secondary-color: #ff7ae9;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --success-color: #28a745;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .container {
            text-align: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            color: var(--dark-color);
            font-size: 2.8em;
            margin-bottom: 40px;
            position: relative;
            display: inline-block;
            padding-bottom: 10px;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--secondary-color);
            border-radius: 2px;
        }

        .languages {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 40px;
        }

        .language-card {
            background-color: #fff;
            border-radius: 12px;
            padding: 25px;
            width: 280px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none;
        }

        .language-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .language-card img {
            max-width: 100%;
            border-radius: 8px;
            height: 160px;
            object-fit: contain;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .language-card h2 {
            color: var(--dark-color);
            font-size: 1.6em;
            margin: 15px 0;
            transition: color 0.3s ease;
        }

        .language-card p {
            color: #666;
            font-size: 1em;
            margin-bottom: 20px;
        }

        .language-card button {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-size: 1.1em;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(74, 107, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .language-card button::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        .language-card button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(74, 107, 255, 0.4);
        }

        .language-card button:hover::after {
            animation: ripple 1s ease-out;
        }

        .language-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .language-card:hover img {
            transform: scale(1.05);
        }

        .language-card:hover h2 {
            color: var(--primary-color);
        }

        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 1;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }

        .user-info {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 12px 20px;
            border-radius: 50px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .user-info a {
            color: var(--dark-color);
            text-decoration: none;
            margin: 0 10px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .user-info a:hover {
            color: var(--primary-color);
            text-decoration: none;
        }

        .verified {
            color: var(--success-color);
            cursor: pointer;
            margin-left: 5px;
            font-size: 0.9em;
        }

        .admin-panel {
            margin: 40px 0;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .admin-panel a {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(45deg, var(--primary-color), #6c5ce7);
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(74, 107, 255, 0.3);
        }

        .admin-panel a i {
            margin-right: 8px;
        }

        .admin-panel a:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(74, 107, 255, 0.4);
            background: linear-gradient(45deg, #6c5ce7, var(--primary-color));
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .languages {
                flex-direction: column;
                align-items: center;
            }
            
            .language-card {
                width: 80%;
            }
            
            h1 {
                font-size: 2em;
            }
            
            .user-info {
                position: static;
                margin-bottom: 20px;
                width: fit-content;
                margin-left: auto;
                margin-right: auto;
            }
        }
        
    </style>
</head>
<body>

<div class="user-info animate__animated animate__fadeInDown">
    <?php if ($user_id): ?>
        <a href="profile.php" class="animate__animated animate__pulse animate__infinite"><i class="fas fa-user"></i> <?php echo htmlspecialchars($username); ?></a>
        <span class="verified" onclick="window.location.href='profile.php'"><i class="fas fa-check-circle"></i></span>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    <?php else: ?>
        <a href="register.php" class="animate__animated animate__pulse"><i class="fas fa-user-plus"></i> Register</a>
        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
    <?php endif; ?>
</div>

<div class="container">
    <h1 class="animate__animated animate__fadeIn">I want to study...</h1>

    <!-- Панель для учителей и супервизоров -->
    <?php if ($role === 'teacher' || $role === 'supervisor'): ?>
        <div class="admin-panel" data-aos="fade-up" data-aos-delay="200">
            <a href="courses.php"><i class="fas fa-plus-circle"></i> Vytvoriť test</a>
            <?php if ($role === 'supervisor'): ?>
                <a href="manage_users.php"><i class="fas fa-users-cog"></i> Spravovať používateľov</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="languages">
        <!-- Cards for languages -->
        <div class="language-card animate__animated animate__fadeInUp" data-aos="zoom-in" data-aos-delay="100">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c3/Python-logo-notext.svg/172px-Python-logo-notext.svg.png" alt="Python">
            <h2>Python</h2>
            <p><i class="fas fa-users"></i> 14,6 milióna študentov</p>
            <button onclick="location.href='python/python_title.php'"><i class="fas fa-book-open"></i> Študovať Python</button>
        </div>
        
        <div class="language-card animate__animated animate__fadeInUp" data-aos="zoom-in" data-aos-delay="200">
            <img src="https://upload.wikimedia.org/wikipedia/en/thumb/3/30/Java_programming_language_logo.svg/800px-Java_programming_language_logo.svg.png" alt="Java">
            <h2>Java</h2>
            <p><i class="fas fa-users"></i> 2,60 milióna študentov</p>
            <button onclick="location.href='java/java_title.php'"><i class="fas fa-laptop-code"></i> Študovať Java</button>
        </div>
        
        <div class="language-card animate__animated animate__fadeInUp" data-aos="zoom-in" data-aos-delay="300">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/18/ISO_C%2B%2B_Logo.svg/180px-ISO_C%2B%2B_Logo.svg.png" alt="C++">
            <h2>C++</h2>
            <p><i class="fas fa-users"></i> 1,55 milióna študentov</p>
            <button onclick="location.href='cpp/cpp_title.php'"><i class="fas fa-code"></i> Študovať C++</button>
        </div>
        
        <div class="language-card animate__animated animate__fadeInUp" data-aos="zoom-in" data-aos-delay="400">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/61/HTML5_logo_and_wordmark.svg/800px-HTML5_logo_and_wordmark.svg.png" alt="HTML">
            <h2>HTML</h2>
            <p><i class="fas fa-users"></i> 1,36 milióna študentov</p>
            <button onclick="location.href='html/html_title.php'"><i class="fas fa-globe"></i> Študovať HTML</button>
        </div>
    </div>
</div>

<!-- Подключаем скрипты для анимаций -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Инициализация AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: false
    });
    
    // Добавляем эффект пульсации для кнопок при наведении
    document.querySelectorAll('.language-card button').forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.classList.add('animate__animated', 'animate__pulse');
        });
        
        button.addEventListener('mouseleave', function() {
            this.classList.remove('animate__animated', 'animate__pulse');
        });
    });
    
    // Плавная прокрутка для всех ссылок
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
</script>

</body>
</html>