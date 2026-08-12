<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

$student_id = $_GET['student_id'] ?? '';

if(!empty($student_id)) {
    $stmt = $conn->prepare("SELECT * FROM rollcall_logs WHERE student_id = ? ORDER BY created_at DESC");
    $stmt->execute([$student_id]);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} else {
    echo json_encode(['status' => 'error']);
}
?>