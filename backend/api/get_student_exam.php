<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

$student_id = $_GET['student_id'] ?? '';

try {
    $conn->exec("
        CREATE TABLE IF NOT EXISTS student_exams (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            exam_name VARCHAR(120) DEFAULT 'Final Exam',
            score DECIMAL(6,2) DEFAULT 0,
            max_score DECIMAL(6,2) DEFAULT 100,
            exam_date DATE NULL,
            note TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_student_exam (student_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    if(empty($student_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing student_id']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM student_exams WHERE student_id = ? LIMIT 1");
    $stmt->execute([$student_id]);
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $exam ?: null]);
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
