<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

$student_id = (int)($_POST['student_id'] ?? 0);
$total_amount = (float)($_POST['total_amount'] ?? 0);
$first_amount = (float)($_POST['first_amount'] ?? 0);
$second_amount = (float)($_POST['second_amount'] ?? 0);
$first_paid_at = trim($_POST['first_paid_at'] ?? '') ?: null;
$second_paid_at = trim($_POST['second_paid_at'] ?? '') ?: null;
$reminder_date = trim($_POST['reminder_date'] ?? '') ?: null;
$note = trim($_POST['note'] ?? '');

if ($student_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Missing student']);
    exit;
}

foreach ([$first_paid_at, $second_paid_at, $reminder_date] as $date) {
    if ($date !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid date format']);
        exit;
    }
}

try {
    $stmt = $conn->prepare("
        INSERT INTO student_payments
            (student_id, total_amount, first_amount, first_paid_at, second_amount, second_paid_at, reminder_date, note)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            total_amount = VALUES(total_amount),
            first_amount = VALUES(first_amount),
            first_paid_at = VALUES(first_paid_at),
            second_amount = VALUES(second_amount),
            second_paid_at = VALUES(second_paid_at),
            reminder_date = VALUES(reminder_date),
            note = VALUES(note)
    ");
    $stmt->execute([$student_id, $total_amount, $first_amount, $first_paid_at, $second_amount, $second_paid_at, $reminder_date, $note]);
    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
