<?php
session_start();

if ($_SESSION['role'] !== 'supervisor') {
    header("Location: test.php");
    exit();
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];

    $query = "UPDATE students SET role = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $role, $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = 'Rola bola úspešne zmenená.';
    header("Location: manage_users.php");
    exit();
}

$query = "SELECT id, name, email, role FROM students";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spravovať používateľov</title>
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
        
        .management-container {
            background: white;
            width: 100%;
            max-width: 1000px;
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
        
        .management-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }
        
        h1 {
            color: var(--dark-color);
            margin-bottom: 30px;
            font-size: 2rem;
            position: relative;
            display: inline-block;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 80px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 3px;
        }
        
        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .success-message {
            background: rgba(75, 181, 67, 0.2);
            color: var(--success-color);
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.8s;
        }
        
        .users-table th {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 500;
        }
        
        .users-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .users-table tr:last-child td {
            border-bottom: none;
        }
        
        .users-table tr:hover {
            background: rgba(67, 97, 238, 0.05);
        }
        
        .users-table tr {
            transition: all 0.3s ease;
        }
        
        .users-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .user-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .user-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        .role-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        select {
            padding: 8px 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }
        
        select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            outline: none;
            background-color: white;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .btn i {
            margin-right: 6px;
        }
        
        .btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 25px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-size: 1rem;
            font-weight: 500;
            margin-top: 30px;
        }
        
        .btn-back:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
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
            .management-container {
                padding: 20px;
            }
            
            .users-table {
                display: block;
                overflow-x: auto;
            }
            
            .role-form {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="management-container animate__animated animate__fadeIn">
    <h1><i class="fas fa-users-cog"></i> Spravovať používateľov</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message success-message animate__animated animate__fadeIn">
            <i class="fas fa-check-circle"></i> <?php
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>

    <table class="users-table">
        <thead>
            <tr>
                <th><i class="fas fa-user"></i> Meno</th>
                <th><i class="fas fa-envelope"></i> Email</th>
                <th><i class="fas fa-user-tag"></i> Rola</th>
                <th><i class="fas fa-cog"></i> Akcia</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="animate__animated animate__fadeIn">
                    <td>
                        <a href="student_performance.php?id=<?= $row['id'] ?>" class="user-link">
                            <i class="fas fa-user-graduate"></i> <?= htmlspecialchars($row['name']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <span class="role-badge" style="
                            background: <?= 
                                $row['role'] === 'supervisor' ? 'rgba(67, 97, 238, 0.2)' : 
                                ($row['role'] === 'teacher' ? 'rgba(75, 181, 67, 0.2)' : 'rgba(108, 117, 125, 0.2)') 
                            ?>;
                            color: <?= 
                                $row['role'] === 'supervisor' ? 'var(--primary-color)' : 
                                ($row['role'] === 'teacher' ? 'var(--success-color)' : 'var(--dark-color)') 
                            ?>;
                            padding: 5px 10px;
                            border-radius: 50px;
                            font-weight: 500;
                        ">
                            <?= htmlspecialchars($row['role']) ?>
                        </span>
                    </td>
                    <td>
                        <form action="" method="POST" class="role-form">
                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                            <select name="role" class="role-select">
                                <option value="student" <?= $row['role'] === 'student' ? 'selected' : '' ?>>Študent</option>
                                <option value="teacher" <?= $row['role'] === 'teacher' ? 'selected' : '' ?>>Učiteľ</option>
                                <option value="supervisor" <?= $row['role'] === 'supervisor' ? 'selected' : '' ?>>Supervízor</option>
                            </select>
                            <button type="submit" class="btn" id="updateRoleBtn">
                                <i class="fas fa-sync-alt"></i> Aktualizovať
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="test.php" class="btn-back animate__animated animate__fadeInLeft" id="backButton">
        <i class="fas fa-arrow-left"></i> Späť na testy
    </a>
</div>

<script>
    // Добавляем эффект ripple для кнопок
    document.querySelectorAll('.btn').forEach(button => {
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

    // Анимация строк таблицы с задержкой
    document.querySelectorAll('.users-table tr').forEach((row, index) => {
        row.style.animationDelay = `${index * 0.1}s`;
    });
</script>

</body>
</html>