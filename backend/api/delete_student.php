<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

$id = $_POST['id'] ?? '';

if(!empty($id)) {
    try {
        $conn->beginTransaction();
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
        
        // 1. Delete all related progress records (prevents foreign key crashes)
        $conn->prepare("DELETE FROM student_progress WHERE student_id=?")->execute([$id]);
        $conn->prepare("DELETE FROM progress_history WHERE student_id=?")->execute([$id]);
        $conn->prepare("DELETE FROM rollcall_logs WHERE student_id=?")->execute([$id]); 
        $conn->prepare("DELETE FROM student_exams WHERE student_id=?")->execute([$id]); 
        
        // 2. Delete the actual student
        $conn->prepare("DELETE FROM students WHERE id=?")->execute([$id]);
        
        $conn->commit();
        echo json_encode(['status' => 'success']);
    } catch(PDOException $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing ID']);
}
?>
