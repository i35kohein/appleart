<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

$student_id = $_POST['student_id'] ?? '';
$status = $_POST['status'] ?? '';
$date = trim($_POST['date'] ?? ''); // optional YYYY-MM-DD; empty = today

// Validate date format if provided
if ($date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid date format (use YYYY-MM-DD)']);
    exit;
}

if(!empty($student_id) && !empty($status)) {
    // Match existing record for this student on the target date
    $stmt = $conn->prepare("SELECT id FROM rollcall_logs WHERE student_id = ? AND DATE(created_at) = ?");
    $stmt->execute([$student_id, $date !== '' ? $date : date('Y-m-d')]);

    if($stmt->rowCount() > 0) {
        // Update that day's record
        $conn->prepare("UPDATE rollcall_logs SET status = ? WHERE student_id = ? AND DATE(created_at) = ?")->execute([$status, $student_id, $date !== '' ? $date : date('Y-m-d')]);
    } else {
        // Create new record pinned to that day
        $conn->prepare("INSERT INTO rollcall_logs (student_id, status, created_at) VALUES (?, ?, ?)")->execute([$student_id, $status, $date !== '' ? $date . ' 00:00:00' : date('Y-m-d H:i:s')]);
    }
    echo json_encode(['status' => 'success']);
} else { echo json_encode(['status' => 'error']); }
