<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

try {
    $query = "
        SELECT
            s.id AS student_id,
            s.name,
            s.phone,
            s.email,
            s.photo_path,
            s.is_active,
            s.rollcall_group,
            COALESCE(p.total_amount, 0) AS total_amount,
            COALESCE(p.first_amount, 0) AS first_amount,
            p.first_paid_at,
            COALESCE(p.second_amount, 0) AS second_amount,
            p.second_paid_at,
            p.reminder_date,
            p.note,
            p.updated_at
        FROM students s
        LEFT JOIN student_payments p ON p.student_id = s.id
        ORDER BY s.id DESC
    ";
    $rows = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $rows]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
