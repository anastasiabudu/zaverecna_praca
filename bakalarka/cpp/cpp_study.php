<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Študujeme C++</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Source+Code+Pro:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #759cd8;
            --secondary-color: #9cb9d1;
            --dark-color: #2d3748;
            --light-color: #eef0f6;
            --accent-color: #4a6fa5;
            --success-color: #48bb78;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            color: var(--dark-color);
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #cfdaec 100%);
            min-height: 100vh;
        }

        h1 {
            margin-top: 50px;
            font-weight: 700;
            color: var(--dark-color);
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
            width: 80px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 2px;
        }

        .container {
            margin: 30px auto;
            padding: 30px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 700px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--primary-color);
        }

        p {
            margin-bottom: 20px;
            text-align: justify;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
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
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            min-width: 180px;
        }

        .start-btn:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(0, 0, 0, 0.15);
        }

        .start-btn i {
            margin-right: 8px;
        }

        /* Code block styling */
        .code-block {
            background-color: #2d2d2d;
            color: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Source Code Pro', monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
            margin: 25px 0;
            text-align: left;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-left: 4px solid var(--success-color);
        }

        .code-block::before {
            content: 'C++';
            position: absolute;
            top: 0;
            right: 0;
            padding: 5px 10px;
            background-color: rgba(255, 255, 255, 0.1);
            color: #aaa;
            font-size: 12px;
            border-bottom-left-radius: 8px;
        }

        .code-block pre {
            margin: 0;
            line-height: 1.5;
        }

        .code-keyword {
            color: #569cd6;
        }

        .code-string {
            color: #ce9178;
        }

        .code-function {
            color: #dcdcaa;
        }

        .code-comment {
            color: #6a9955;
        }

        .code-include {
            color: #9cdcfe;
        }

        /* Features section */
        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin: 30px 0;
            gap: 15px;
        }

        .feature {
            flex: 1 1 200px;
            padding: 15px;
            background-color: rgba(117, 156, 216, 0.1);
            border-radius: 8px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .feature:hover {
            transform: scale(1.05);
        }

        .feature i {
            font-size: 24px;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .feature h3 {
            margin: 0 0 10px;
            color: var(--accent-color);
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container {
            animation: fadeIn 0.6s ease-out forwards;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                margin: 20px 15px;
                padding: 20px;
            }
            
            .button-container {
                flex-direction: column;
                align-items: center;
            }
            
            .start-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1><i class="fas fa-code"></i> Študujeme C++</h1>

    <p>C++ je výkonný programovací jazyk, ktorý bol vyvinutý v 80. rokoch minulého storočia ako rozšírenie jazyka C. Je to objektovo orientovaný jazyk, ktorý podporuje aj procedurálne programovanie. C++ je široko používaný na vývoj softvéru, systémového programovania, herného vývoja a aplikácií, ktoré vyžadujú vysoký výkon.</p>

    <div class="features">
        <div class="feature">
            <i class="fas fa-bolt"></i>
            <h3>Vysoký výkon</h3>
            <p>Priama práca s hardvérom a pamäťou</p>
        </div>
        <div class="feature">
            <i class="fas fa-cogs"></i>
            <h3>Multi-paradigmatický</h3>
            <p>Objektové, generické a funkcionálne programovanie</p>
        </div>
        <div class="feature">
            <i class="fas fa-gamepad"></i>
            <h3>Herný priemysel</h3>
            <p>Štandard pre výkonné hry a enginy</p>
        </div>
    </div>

    <p>Tu je jednoduchý príklad kódu v C++:</p>
    <div class="code-block">
    <pre>
<span class="code-include">#include</span> &lt;iostream&gt;
<span class="code-keyword">using namespace</span> std;

<span class="code-keyword">int</span> <span class="code-function">main</span>() {
    cout &lt;&lt; <span class="code-string">"Ahoj, svet!"</span> &lt;&lt; endl;
    <span class="code-keyword">return</span> 0;
}
    </pre>
    </div>

    <p>Po prečítaní týchto informácií ste pripravení na test, ktorý preverí vaše znalosti a porozumenie základom C++.</p>

    <div class="button-container">
        <a href="cpp_test.php" class="start-btn"><i class="fas fa-play"></i> Začnite test</a>
        <a href="../test.php" class="start-btn"><i class="fas fa-language"></i> Vybrať iný jazyk</a>
    </div>

</div>

</body>
</html>