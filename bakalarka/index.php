<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jazykové učenie | Duolingo-Style</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: rgb(55, 127, 227);
            --secondary-color: #1cb0f6;
            --accent-color: #ffc800;
            --dark-color: #2e2e2e;
            --light-color: #f8f9fa;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Nunito', sans-serif;
        }

        body {
            background: linear-gradient(135deg, rgb(9, 47, 61) 0%, rgb(10, 6, 52) 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow-x: hidden;
        }

        /* Звездный фон */
        .stars-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        /* Логотип */
        .logo {
            width: 100px;
            height: 100px;
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: white;
            border-radius: 50%;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 100;
            transition: var(--transition);
        }

        .logo img {
            width: 80%;
            height: 80%;
            object-fit: contain;
        }

        /* Основной контент */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }

        /* Шапка с текстом */
        .welcome-section {
            text-align: center;
            margin-bottom: 40px;
            width: 100%;
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            line-height: 1.3;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .welcome-subtitle {
            font-size: 1.5rem;
            opacity: 0.9;
            line-height: 1.5;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Карточки действий */
        .actions-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        .action-card {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            color: var(--dark-color);
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        /* Модальное окно */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            display: none;
            width: 90%;
            max-width: 400px;
            z-index: 1000;
            color: var(--dark-color);
        }

        .modal-btn {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition);
        }

        .register-btn {
            background-color: var(--primary-color);
            color: white;
        }

        .login-btn {
            background-color: var(--secondary-color);
            color: white;
        }

        .close-btn {
            background-color: transparent;
            color: var(--dark-color);
            border: 2px solid var(--dark-color);
            margin-top: 20px;
        }

        /* Языковой селектор */
        .language-selector {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: white;
            padding: 10px 15px;
            border-radius: 50px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: var(--transition);
            z-index: 100;
            color: var(--dark-color);
        }

        /* Адаптивные стили */
        @media (max-width: 768px) {
            .welcome-title {
                font-size: 2rem;
            }
            .welcome-subtitle {
                font-size: 1.2rem;
            }
            .actions-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .welcome-title {
                font-size: 1.7rem;
            }
            .welcome-subtitle {
                font-size: 1rem;
            }
            .logo {
                width: 70px;
                height: 70px;
                top: 15px;
                right: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Звездный фон -->
    <div id="stars-container" class="stars-container"></div>

    <!-- Логотип -->
    <div class="logo animate__animated animate__bounceInDown">
        <img src="images/log1.jpg" alt="Logo">
    </div>

    <!-- Языковой селектор -->
    <div class="language-selector animate__animated animate__fadeInRight">
        <img src="https://flagcdn.com/w20/sk.png" alt="Slovak">
        <span>Slovenčina</span>
    </div>

    <!-- Основной контент -->
    <div class="main-content">
        <section class="welcome-section animate__animated animate__fadeIn">
            <h1 class="welcome-title">Vítajte v jazykovom učebnom systéme</h1>
            <p class="welcome-subtitle">Začnite svoju cestu k plynulosti ešte dnes!</p>
        </section>

        <div class="actions-container">
            <div class="action-card animate__animated animate__fadeInUp" onclick="goToTests()">
                <div class="action-icon">📝</div>
                <div>Prejsť testy</div>
            </div>
            
            <div class="action-card animate__animated animate__fadeInUp animate__delay-1s" onclick="openModal()">
                <div class="action-icon">🔑</div>
                <div>Prihlásenie / Registrácia</div>
            </div>
        </div>
    </div>

    <!-- Модальное окно -->
    <div id="overlay" class="overlay"></div>
    <div id="modal" class="modal">
        <h2>Vitajte!</h2>
        <p>Vyberte si možnosť:</p>
        <button class="modal-btn register-btn" onclick="location.href='register.php'">Registrácia</button>
        <button class="modal-btn login-btn" onclick="location.href='login.php'">Prihlásenie</button>
        <button class="modal-btn close-btn" onclick="closeModal()">Zatvoriť</button>
    </div>

    <!-- Конфетти -->
    <div id="confetti-container"></div>

    <script>
    // Функция создания звезд
    function createStars() {
        const container = document.getElementById('stars-container');
        if (!container) return;
        
        const starsCount = 50;
        container.innerHTML = '';
        
        for (let i = 0; i < starsCount; i++) {
            const star = document.createElement('div');
            star.className = 'star';
            
            const size = Math.random() * 3 + 1;
            const posX = Math.random() * 100;
            const delay = Math.random() * 5;
            const duration = Math.random() * 5 + 5;
            
            star.style.width = `${size}px`;
            star.style.height = `${size}px`;
            star.style.left = `${posX}vw`;
            star.style.animationDelay = `${delay}s`;
            star.style.animationDuration = `${duration}s`;
            
            if (Math.random() > 0.7) {
                star.style.animationName = 'falling, twinkle';
                star.style.animationDuration = `${duration}s, ${Math.random() * 3 + 2}s`;
            }
            
            container.appendChild(star);
        }
    }

    // Анимация мерцания
    const style = document.createElement('style');
    style.textContent = `
        @keyframes twinkle {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }
        .star {
            position: absolute;
            background-color: white;
            border-radius: 50%;
            animation: falling linear infinite, twinkle ease-in-out infinite;
            opacity: 0.8;
        }
        @keyframes falling {
            0% { transform: translateY(-100px) translateX(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(100vh) translateX(100px); opacity: 0; }
        }
    `;
    document.head.appendChild(style);

    // Инициализация
    document.addEventListener('DOMContentLoaded', () => {
        createStars();
        
        document.getElementById('overlay')?.addEventListener('click', closeModal);
    });

    function openModal() {
        const overlay = document.getElementById('overlay');
        const modal = document.getElementById('modal');
        if (overlay && modal) {
            overlay.style.display = "block";
            modal.style.display = "block";
            createConfetti();
        }
    }

    function closeModal() {
        const overlay = document.getElementById('overlay');
        const modal = document.getElementById('modal');
        if (overlay && modal) {
            overlay.style.display = "none";
            modal.style.display = "none";
        }
    }

    function createConfetti() {
        const container = document.getElementById('confetti-container');
        if (!container) return;
        
        container.innerHTML = '';
        
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.top = -10 + 'px';
            confetti.style.backgroundColor = getRandomColor();
            confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
            container.appendChild(confetti);
            animateConfetti(confetti);
        }
    }

    function animateConfetti(element) {
        const duration = Math.random() * 3 + 2;
        const animation = element.animate([
            { top: '-10px', opacity: 0 },
            { top: '10%', opacity: 1 },
            { top: '100vh', opacity: 0 }
        ], {
            duration: duration * 1000,
            easing: 'cubic-bezier(0.1, 0.8, 0.3, 1)'
        });
        animation.onfinish = () => element.remove();
    }

    function getRandomColor() {
        const colors = ['#58a700', '#1cb0f6', '#ffc800', '#ff6b6b', '#9c5fff'];
        return colors[Math.floor(Math.random() * colors.length)];
    }

    function goToTests() {
        document.body.classList.add('animate__animated', 'animate__fadeOut');
        setTimeout(() => {
            window.location.href = 'test.php';
        }, 500);
    }
    
    </script>
</body>
</html>