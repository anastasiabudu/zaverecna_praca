<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Študujeme HTML</title>
    
    <!-- Подключаем библиотеки -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #e44d26; /* HTML orange */
            --secondary-color: #f16529;
            --dark-color: #333;
            --light-color: #f5f5f5;
            --accent-color: #f0db4f; /* JavaScript yellow as accent */
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-color);
            background: linear-gradient(135deg, #f9f9f9 0%, #e0e0e0 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        h1 {
            margin-top: 50px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            display: inline-block;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 2px;
        }

        h2 {
            margin-top: 20px;
            font-size: 24px;
            color: var(--primary-color);
        }

        .container {
            box-sizing: border-box;
            margin: 30px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            text-align: left;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .btn-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 30px;/
        }

        .start-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            padding: 12px 25px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .start-btn i {
            margin-right: 8px;
            transition: transform 0.3s;
        }
        
        .start-btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        .start-btn:hover i {
            transform: scale(1.2);
        }
        
        .start-btn::after {
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
        
        .start-btn:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }

        pre {
            background: #272822;
            color: #f8f8f2;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
            font-size: 16px;
            line-height: 1.6;
            border: 2px solid var(--primary-color);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            position: relative;
        }
        
        pre:hover {
            transform: scale(1.01);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        
        pre::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 30px;
            background: #3a3a3a;
            border-radius: 8px 8px 0 0;
        }
        
        pre::after {
            content: 'HTML';
            position: absolute;
            top: 5px;
            left: 15px;
            color: var(--accent-color);
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        code {
            color: #f8f8f2;
            font-family: 'Courier New', Courier, monospace;
        }

        p {
            font-size: 16px;
            line-height: 1.8;
            margin-top: 15px;
            color: var(--dark-color);
        }

        strong {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .html-icon {
            color: var(--primary-color);
            margin-right: 10px;
        }
        
        /* Анимации */
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
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        /* Адаптивность */
        @media (max-width: 768px) {
            .container {
                margin: 20px 15px;
                padding: 20px;
            }
            
            .btn-group {
                flex-direction: row; /* Ряд только на десктопах */
                justify-content: center;
            }
            
            .start-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container animate__animated animate__fadeIn" data-aos="fade-up">
    <h1 class="floating"><i class="fab fa-html5 html-icon"></i>Študujeme HTML</h1>

    <p data-aos="fade-right">HTML (HyperText Markup Language) je základný stavebný kameň webu. Každá webová stránka, ktorú navštívite, je vytvorená pomocou HTML. Tento jazyk definuje štruktúru a obsah webovej stránky pomocou systému značiek (tagov), ktoré obklopujú a popisujú obsah.</p>

    <h2 data-aos="fade-left"><i class="fas fa-code"></i> Základná štruktúra HTML dokumentu:</h2>
    <div data-aos="zoom-in">
        <pre><code>
&lt;!DOCTYPE html&gt;
&lt;html lang="sk"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;Moja stránka&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Vitajte na mojej stránke&lt;/h1&gt;
    &lt;p&gt;Toto je môj prvý HTML dokument.&lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;
        </code></pre>
    </div>

    <p data-aos="fade-right">Tento kód predstavuje základnú štruktúru každého HTML dokumentu. Element <strong>&lt;!DOCTYPE html&gt;</strong> definuje, že ide o HTML5 dokument. Element <strong>&lt;html&gt;</strong> je koreňový element celého dokumentu. Vnútri <strong>&lt;head&gt;</strong> sa nachádzajú metadáta a v <strong>&lt;body&gt;</strong> je viditeľný obsah stránky.</p>
    <p data-aos="fade-left">Po prečítaní tohto príkladu ste pripravení na test, ktorý preverí vaše znalosti HTML!</p>

    <div class="btn-group">
        <a href="html_test.php" class="start-btn animate__animated animate__pulse animate__infinite animate__slower">
            <i class="fas fa-play"></i> Začať test
        </a>
        <a href="../test.php" class="start-btn">
            <i class="fas fa-exchange-alt"></i> Vybrať iný jazyk
        </a>
    </div>
</div>

<!-- Подключаем скрипты -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Инициализация AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: false
    });
    
    // Плавная прокрутка для якорей
    $(document).ready(function(){
        $("a").on('click', function(event) {
            if (this.hash !== "") {
                event.preventDefault();
                var hash = this.hash;
                $('html, body').animate({
                    scrollTop: $(hash).offset().top
                }, 800, function(){
                    window.location.hash = hash;
                });
            }
        });
    });
    
    // Анимация при наведении на кнопки
    $('.start-btn').on('mouseenter', function() {
        $(this).addClass('animate__animated animate__pulse');
    }).on('mouseleave', function() {
        $(this).removeClass('animate__animated animate__pulse');
    });
</script>

</body>
</html>