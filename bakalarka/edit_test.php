<?php
session_start();
require_once 'db.php';

// Проверка роли
if ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'supervisor') {
    header("Location: test.php");
    exit();
}

// Получаем ID теста из запроса
$test_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($test_id <= 0) {
    header("Location: test.php");
    exit();
}

// Загрузка данных теста
$test = [];
$query = "SELECT * FROM tests WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $test_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $test = $result->fetch_assoc();
} else {
    header("Location: test.php");
    exit();
}

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $test_name = $_POST['test_name'];
    $question = $_POST['question'];
    $options = $_POST['options'];  // Варианты ответов в формате JSON
    $answer = $_POST['answer'];    // Правильный ответ

    // Обновляем данные теста в базе данных
    $update_query = "UPDATE tests SET test_name = ?, question = ?, options = ?, answer = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssssi", $test_name, $question, $options, $answer, $test_id);

    if ($update_stmt->execute()) {
        $_SESSION['message'] = "Test bol úspešne aktualizovaný.";
        header("Location: edit_test.php?id=" . $test_id);
        exit();
    } else {
        $_SESSION['error'] = "Chyba pri aktualizácii testu.";
    }
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upraviť test</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --text-color: #5a5c69;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: var(--text-color);
            min-height: 100vh;
            padding: 20px;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
            overflow: hidden;
            transition: all 0.5s ease;
            padding: 30px;
        }
        
        .glass-card:hover {
            box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.2);
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 15px;
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
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e3e6f0;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-gradient:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.4);
        }
        
        .btn-gradient::after {
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
        
        .btn-gradient:hover::after {
            opacity: 1;
        }
        
        .btn-outline-secondary {
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.2);
        }
        
        .alert {
            border-radius: 8px;
            padding: 15px 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        
        .alert-success {
            background-color: rgba(28, 200, 138, 0.1);
            border-color: rgba(28, 200, 138, 0.2);
            color: #0f5132;
        }
        
        .alert-danger {
            background-color: rgba(231, 74, 59, 0.1);
            border-color: rgba(231, 74, 59, 0.2);
            color: #842029;
        }
        
        .json-editor {
            font-family: 'Courier New', Courier, monospace;
            min-height: 150px;
        }
        
        .option-preview {
            background-color: var(--secondary-color);
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            border-left: 3px solid var(--primary-color);
        }
        
        .option-preview h6 {
            font-weight: 600;
            color: var(--primary-color);
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
            .glass-card {
                padding: 20px;
            }
            
            .page-header {
                padding: 1.5rem 0;
            }
        }
    </style>
</head>
<body>
    <div class="container animate__animated animate__fadeIn">
        <div class="page-header text-center">
            <h1 class="animate__animated animate__fadeInDown">
                <i class="fas fa-edit me-2"></i>
                Upraviť test
            </h1>
        </div>
        
        <div class="glass-card mb-4 animate__animated animate__fadeInUp">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success animate__animated animate__bounceIn">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $_SESSION['message'] ?>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger animate__animated animate__shakeX">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $_SESSION['error'] ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <div class="mb-4">
                    <label for="test_name" class="form-label">Názov testu</label>
                    <input type="text" class="form-control" id="test_name" name="test_name" 
                           value="<?= htmlspecialchars($test['test_name']) ?>" required>
                    <div class="invalid-feedback">
                        Prosím, zadajte názov testu.
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="question" class="form-label">Otázka</label>
                    <textarea class="form-control" id="question" name="question" rows="4" required><?= htmlspecialchars($test['question']) ?></textarea>
                    <div class="invalid-feedback">
                        Prosím, zadajte otázku.
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="options" class="form-label">Možnosti odpovede (JSON)</label>
                    <textarea class="form-control json-editor" id="options" name="options" rows="6" required><?= htmlspecialchars($test['options']) ?></textarea>
                    <div class="invalid-feedback">
                        Prosím, zadajte možnosti odpovede v JSON formáte.
                    </div>
                    <div class="option-preview mt-3 animate__animated animate__fadeIn">
                        <h6><i class="fas fa-eye me-2"></i>Náhľad možností:</h6>
                        <div id="options-preview"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="answer" class="form-label">Správna odpoveď</label>
                    <input type="text" class="form-control" id="answer" name="answer" 
                           value="<?= htmlspecialchars($test['answer']) ?>" required>
                    <div class="invalid-feedback">
                        Prosím, zadajte správnu odpoveď.
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-5">
                    <a href="test.php" class="btn btn-outline-secondary ripple">
                        <i class="fas fa-arrow-left me-1"></i> Späť na testy
                    </a>
                    <button type="submit" class="btn btn-gradient ripple">
                        <i class="fas fa-save me-1"></i> Uložiť zmeny
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        
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
        
        // JSON options preview
        function updateOptionsPreview() {
            try {
                const options = JSON.parse($('#options').val());
                let html = '<ul class="list-unstyled">';
                
                if (Array.isArray(options)) {
                    options.forEach(option => {
                        html += `<li><i class="fas fa-circle-notch fa-xs me-2"></i>${option}</li>`;
                    });
                } else if (typeof options === 'object' && options !== null) {
                    for (const key in options) {
                        html += `<li><strong>${key}:</strong> ${options[key]}</li>`;
                    }
                }
                
                html += '</ul>';
                $('#options-preview').html(html);
            } catch (e) {
                $('#options-preview').html('<div class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Neplatný JSON formát</div>');
            }
        }
        
        // Initial preview and update on change
        $(document).ready(function() {
            updateOptionsPreview();
            $('#options').on('input', updateOptionsPreview);
        });
    </script>
</body>
</html>