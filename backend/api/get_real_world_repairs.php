<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

$student_id = $_GET['student_id'] ?? '';

try {
    if (!empty($student_id)) {
        $stmt = $conn->prepare("
            SELECT r.*, s.name AS student_name, s.phone
            FROM real_world_repairs r
            JOIN students s ON r.student_id = s.id
            WHERE r.student_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$student_id]);
    } else {
        $stmt = $conn->query("
            SELECT r.*, s.name AS student_name, s.phone
            FROM real_world_repairs r
            JOIN students s ON r.student_id = s.id
            ORDER BY r.created_at DESC
        ");
    }

    echo json_encode(["status" => "success", "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
