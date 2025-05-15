<?php
session_start();
require_once 'db.php';

// Проверяем, авторизован ли пользователь
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Получаем текущие данные пользователя
$sql = "SELECT name, email, password FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $current_password);
$stmt->fetch();
$stmt->close();

// Обработка изменения имени
if (isset($_POST['update_name'])) {
    $new_name = $_POST['name'];
    $update_sql = "UPDATE students SET name = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_name, $user_id);
    $update_stmt->execute();
    $update_stmt->close();
    $_SESSION['message'] = 'Úspešne ste zmenili svoje meno.';
    header("Location: profile.php");
    exit();
}

// Обработка изменения пароля
if (isset($_POST['update_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['password'];
    $new_password_confirm = $_POST['password_confirm'];

    if (password_verify($old_password, $current_password)) {
        if ($new_password === $new_password_confirm) {
            $new_password_hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $update_sql = "UPDATE students SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $new_password_hashed, $user_id);
            $update_stmt->execute();
            $update_stmt->close();
            $_SESSION['message'] = 'Úspešne ste zmenili svoje heslo.';
        } else {
            $_SESSION['message'] = 'Nové heslá sa nezhodujú.';
        }
    } else {
        $_SESSION['message'] = 'Staré heslo je nesprávne.';
    }
    header("Location: profile.php");
    exit();
}

if ($user_id) {
    $sql = "SELECT login_time FROM user_logins WHERE user_id = ? ORDER BY login_time DESC LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($login_time);

    $logins = [];
    while ($stmt->fetch()) {
        $logins[] = ['login_time' => $login_time];
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil používateľa</title>
    <!-- Подключаем библиотеки анимаций -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --success-color: #4bb543;
            --error-color: #ff3333;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e0e9f5 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        
        .profile-container {
            background: white;
            width: 100%;
            max-width: 800px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 20px 0;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .profile-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }
        
        h2 {
            color: var(--dark-color);
            margin-bottom: 20px;
            font-size: 1.8rem;
            position: relative;
            display: inline-block;
        }
        
        h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--accent-color);
            border-radius: 3px;
        }
        
        .user-info {
            background: rgba(67, 97, 238, 0.05);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid var(--primary-color);
        }
        
        .user-info p {
            margin: 10px 0;
            font-size: 1.1rem;
        }
        
        .user-info strong {
            color: var(--primary-color);
        }
        
        .form-section {
            margin-bottom: 30px;
            animation: fadeIn 0.8s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        form {
            background: var(--light-color);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        
        input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            outline: none;
        }
        
        button[type="submit"] {
            background: var(--primary-color);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }
        
        button[type="submit"]:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .success-message {
            background: rgba(75, 181, 67, 0.2);
            color: var(--success-color);
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
            font-weight: 500;
            animation: fadeIn 0.5s;
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e0e0e0, transparent);
            margin: 30px 0;
        }
        
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        
        .history-table th {
            background: var(--primary-color);
            color: white;
            padding: 12px 15px;
            text-align: left;
        }
        
        .history-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .history-table tr:last-child td {
            border-bottom: none;
        }
        
        .history-table tr:hover {
            background: rgba(67, 97, 238, 0.05);
        }
        
        .no-records {
            text-align: center;
            padding: 20px;
            color: #777;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            flex: 1;
            min-width: 150px;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-back {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-back:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-progress {
            background: var(--success-color);
            color: white;
        }
        
        .btn-progress:hover {
            background: #3fa83a;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(75, 181, 67, 0.3);
        }
        
        .logout-btn {
            background: var(--error-color);
            color: white;
        }
        
        .logout-btn:hover {
            background: #e02b2b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 51, 51, 0.3);
        }
        
        /* Эффект ripple */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.7);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        /* Адаптивность */
        @media (max-width: 768px) {
            .profile-container {
                padding: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="profile-container animate__animated animate__fadeIn">
    <div class="user-info">
        <h2><i class="fas fa-user-circle"></i> Váš profil</h2>
        <p><strong><i class="fas fa-user"></i> Meno:</strong> <?php echo htmlspecialchars($username); ?></p>
        <p><strong><i class="fas fa-envelope"></i> Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="success-message animate__animated animate__fadeIn">
            <i class="fas fa-check-circle"></i> <?php
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>

    <div class="form-section">
        <h2><i class="fas fa-edit"></i> Zmeniť meno</h2>
        <form action="profile.php" method="POST">
            <div class="form-group">
                <label for="name">Nové meno:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <button type="submit" name="update_name" id="updateNameBtn">
                <i class="fas fa-save"></i> Uložiť zmeny mena
            </button>
        </form>
    </div>

    <div class="divider"></div>

    <div class="form-section">
        <h2><i class="fas fa-key"></i> Zmeniť heslo</h2>
        <form action="profile.php" method="POST">
            <div class="form-group">
                <label for="old_password">Staré heslo:</label>
                <input type="password" id="old_password" name="old_password" required>
            </div>
            <div class="form-group">
                <label for="password">Nové heslo:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="password_confirm">Potvrdiť nové heslo:</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            <button type="submit" name="update_password" id="updatePasswordBtn">
                <i class="fas fa-save"></i> Uložiť zmeny hesla
            </button>
        </form>
    </div>

    <div class="divider"></div>

    <div class="history-section">
        <h2><i class="fas fa-history"></i> História prihlásení</h2>
        <table class="history-table">
            <thead>
                <tr>
                    <th><i class="fas fa-calendar-alt"></i> Dátum prihlásenia</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($logins) && !empty($logins)): ?>
                    <?php foreach ($logins as $login): ?>
                        <tr class="animate__animated animate__fadeIn">
                            <td><i class="fas fa-clock"></i> <?= htmlspecialchars($login['login_time']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td class="no-records">Žiadne záznamy</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="action-buttons">
        <a href="test.php" class="btn btn-back animate__animated animate__fadeInLeft">
            <i class="fas fa-arrow-left"></i> Veriť k testom
        </a>
        <a href="progress.php" class="btn btn-progress animate__animated animate__fadeIn">
            <i class="fas fa-chart-line"></i> Pozrieť úspešnosť
        </a>
        <a href="logout.php" class="btn logout-btn animate__animated animate__fadeInRight">
            <i class="fas fa-sign-out-alt"></i> Odhlásiť sa
        </a>
    </div>
</div>

<script>
    // Добавляем эффект ripple для кнопок
    document.querySelectorAll('button').forEach(button => {
        button.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // Анимация при фокусе на полях ввода
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => {
                this.parentElement.classList.remove('animate__animated', 'animate__pulse');
            }, 1000);
        });
    });
</script>

</body>
</html>