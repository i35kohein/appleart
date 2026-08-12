<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

// Attendance for a month (or all if no month) — joined with student names for calendar view
$month = $_GET['month'] ?? '';

try {
    if ($month !== '') {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid month format (use YYYY-MM)']);
            exit;
        }
        $stmt = $conn->prepare("
            SELECT r.id, r.student_id, r.status, r.created_at, s.name, s.photo_path, s.is_active
            FROM rollcall_logs r
            JOIN students s ON r.student_id = s.id
            WHERE DATE_FORMAT(r.created_at, '%Y-%m') = :month
            ORDER BY r.created_at ASC
        ");
        $stmt->execute(['month' => $month]);
    } else {
        $stmt = $conn->query("
            SELECT r.id, r.student_id, r.status, r.created_at, s.name, s.photo_path, s.is_active
            FROM rollcall_logs r
            JOIN students s ON r.student_id = s.id
            ORDER BY r.created_at DESC
        ");
    }

    echo json_encode(["status" => "success", "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
