<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

$student_id = $_GET['student_id'] ?? '';

if (!empty($student_id)) {
    try {
        $stmt = $conn->prepare("SELECT item_id, detail_idx, completion_date, trainer_name FROM student_progress WHERE student_id = :sid AND status = 'Completed'");
        $stmt->execute(['sid' => $student_id]);
        
        // Fetch associative array to get all data, not just IDs
        $progress = $stmt->fetchAll(PDO::FETCH_ASSOC); 

        echo json_encode(["status" => "success", "data" => $progress]);
    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing student ID."]);
}
?>