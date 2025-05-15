<?php
session_start();
require_once 'db.php';

if ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'supervisor') {
    header("Location: test.php");
    exit();
}

$course_id = $_GET['id'];

$course = [];
$query = "SELECT id, name, description FROM courses WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $course = $result->fetch_assoc();
} else {
    $_SESSION['error'] = "Kurz nebol nájdený.";
    header("Location: courses.php");
    exit();
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_course'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);

        if (empty($name)) {
            $_SESSION['error'] = "Názov kurzu je povinný.";
        } else {
            $query = "UPDATE courses SET name = ?, description = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $name, $description, $course_id);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Kurz bol úspešne upravený.";
                header("Location: courses.php");
                exit();
            } else {
                $_SESSION['error'] = "Chyba pri úprave kurzu: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif (isset($_POST['delete_course'])) {
        $query = "DELETE FROM course_tests WHERE course_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $stmt->close();

        $query = "DELETE FROM courses WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $course_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Kurz bol úspešne vymazaný.";
            header("Location: courses.php");
            exit();
        } else {
            $_SESSION['error'] = "Chyba pri vymazávaní kurzu: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upraviť kurz</title>
    <!-- Подключаем библиотеки анимаций -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --success-color: #4bb543;
            --error-color: #dc3545;
            --warning-color: #ffc107;
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
        
        .edit-container {
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
        
        .edit-container::before {
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
        
        .error-message {
            background: rgba(220, 53, 69, 0.2);
            color: var(--error-color);
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
        
        input[type="text"], textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            box-sizing: border-box;
            background-color: #f8f9fa;
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        input[type="text"]:focus, textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            outline: none;
            background-color: white;
        }
        
        .buttons-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 25px;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-size: 1rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            flex: 1;
            min-width: 200px;
        }
        
        .btn i {
            margin-right: 8px;
            transition: transform 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn:hover i {
            transform: scale(1.2);
        }
        
        .btn:active {
            transform: translateY(1px);
        }
        
        .btn-save {
            background: var(--primary-color);
        }
        
        .btn-save:hover {
            background: var(--secondary-color);
        }
        
        .btn-delete {
            background: var(--error-color);
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 25px;
            background: var(--light-color);
            color: var(--dark-color);
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-size: 1rem;
            font-weight: 500;
            margin-top: 20px;
            border: 1px solid #e9ecef;
        }
        
        .btn-back:hover {
            background: #e9ecef;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
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
            .edit-container {
                padding: 20px;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .buttons-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="edit-container animate__animated animate__fadeIn">
    <h1><i class="fas fa-edit"></i> Upraviť kurz: <?= htmlspecialchars($course['name']) ?></h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error-message animate__animated animate__shakeX">
            <i class="fas fa-exclamation-circle"></i> <?php
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" id="editForm">
        <div class="form-group">
            <label for="name"><i class="fas fa-book"></i> Názov kurzu:</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($course['name']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description"><i class="fas fa-align-left"></i> Popis kurzu:</label>
            <textarea name="description" id="description"><?= htmlspecialchars($course['description']) ?></textarea>
        </div>
        
        <div class="buttons-group">
            <button type="submit" name="edit_course" class="btn btn-save" id="saveBtn">
                <i class="fas fa-save"></i> Uložiť zmeny
            </button>
            
            <button type="submit" name="delete_course" class="btn btn-delete" id="deleteBtn">
                <i class="fas fa-trash-alt"></i> Vymazať kurz
            </button>
        </div>
    </form>
    
    <a href="courses.php" class="btn-back animate__animated animate__fadeInLeft">
        <i class="fas fa-arrow-left"></i> Späť na kurzy
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

    // Подтверждение удаления
    document.getElementById('deleteBtn').addEventListener('click', function(e) {
        if (!confirm('Naozaj chcete vymazať tento kurz? Táto akcia je nevratná a vymaže všetky priradené testy!')) {
            e.preventDefault();
        }
    });

    // Анимация при фокусе на полях формы
    document.querySelectorAll('input, textarea').forEach(input => {
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