<?php
session_start();

// Правильные ответы (можно изменить)
$correct_answers = [
    'q1' => 2, // JavaScript
    'q2' => 3, // Java
    'q3' => 2, // Java
    'q4' => 4  // C++
];

// Подсчитаем количество правильных ответов
$score = 0;

foreach ($correct_answers as $question => $correct_answer) {
    if (isset($_POST[$question]) && $_POST[$question] == $correct_answer) {
        $score++;
    }
}

// Выводим результат
echo "<h1>Ваш результат</h1>";
echo "<p>Вы ответили правильно на $score из 4 вопросов.</p>";

// Дополнительные действия, например, сохранение результата в базе данных
?>
