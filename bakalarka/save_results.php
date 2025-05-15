<?php
session_start();
header('Content-Type: application/json');

require_once 'db.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['student_id'], $data['test_name'], $data['score'], $data['total_questions'], $data['results'])) {
        throw new Exception("Missing required fields.");
    }

    $student_id = intval($data['student_id']);
    $test_name = $data['test_name'];
    $score = intval($data['score']);
    $total_questions = intval($data['total_questions']);
    $results = $data['results'];

    // Сохраняем результаты в сессии
    $_SESSION['results'] = $results;

    // Вставляем данные в таблицу test_results (исправленный запрос)
    $stmt = $conn->prepare('
        INSERT INTO test_results (student_id, test_name, score, total_questions)
        VALUES (?, ?, ?, ?)
    ');
    
    // Привязываем параметры (в порядке их появления в запросе)
    $stmt->bind_param("isii", $student_id, $test_name, $score, $total_questions);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    echo json_encode(['status' => 'success', 'message' => 'Results saved successfully']);
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage(),
        'sql_error' => isset($conn) ? $conn->error : null
    ]);
}

$conn->close();
?>