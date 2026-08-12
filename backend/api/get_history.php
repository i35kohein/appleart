<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

$student_id = $_GET['student_id'] ?? '';

if(!empty($student_id)) {
    // Joins the history table with the curriculum table to get the actual Module Names
    $stmt = $conn->prepare("
        SELECT ph.*, ci.title, ci.type 
        FROM progress_history ph 
        JOIN curriculum_items ci ON ph.item_id = ci.id 
        WHERE ph.student_id = ? 
        ORDER BY ph.created_at DESC
    ");
    $stmt->execute([$student_id]);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} else {
    echo json_encode(['status' => 'error']);
}
?>