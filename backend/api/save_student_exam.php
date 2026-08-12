<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

$student_id = $_POST['student_id'] ?? '';
$exam_name = trim($_POST['exam_name'] ?? 'Final Exam');
$score = is_numeric($_POST['score'] ?? null) ? (float)$_POST['score'] : 0;
$max_score = is_numeric($_POST['max_score'] ?? null) ? (float)$_POST['max_score'] : 100;
$exam_date = $_POST['exam_date'] ?? null;
$note = trim($_POST['note'] ?? '');

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

    if($max_score <= 0) $max_score = 100;
    if($score < 0) $score = 0;
    if($score > $max_score) $score = $max_score;
    $exam_date = $exam_date ?: null;
    $exam_name = $exam_name ?: 'Final Exam';

    $stmt = $conn->prepare("
        INSERT INTO student_exams (student_id, exam_name, score, max_score, exam_date, note)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            exam_name = VALUES(exam_name),
            score = VALUES(score),
            max_score = VALUES(max_score),
            exam_date = VALUES(exam_date),
            note = VALUES(note)
    ");
    $stmt->execute([$student_id, $exam_name, $score, $max_score, $exam_date, $note]);

    echo json_encode(['status' => 'success']);
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
