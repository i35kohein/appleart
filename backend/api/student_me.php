<?php
require_once '../config.php';

header('Content-Type: application/json');

if (empty($_SESSION['student_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in', 'student' => null]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, name, email, phone, photo_path, rollcall_group, is_active FROM students WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => intval($_SESSION['student_id'])]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student || intval($student['is_active']) !== 1) {
        echo json_encode(['status' => 'error', 'message' => 'Account not found or inactive', 'student' => null]);
        exit;
    }
    echo json_encode(['status' => 'success', 'student' => $student]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'student' => null]);
}
?>
