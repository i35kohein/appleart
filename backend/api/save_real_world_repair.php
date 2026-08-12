<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
    exit;
}

$student_ids = $_POST['student_ids'] ?? '';
$repair_title = trim($_POST['repair_title'] ?? '');
$comment = trim($_POST['comment'] ?? '');
$trainer_name = $_POST['trainer_name'] ?? 'Instructor';

$ids = json_decode($student_ids, true);
if (!is_array($ids)) {
    $ids = array_filter(array_map('trim', explode(',', $student_ids)));
}

if (empty($ids) || empty($repair_title) || empty($comment)) {
    echo json_encode(["status" => "error", "message" => "Student, title, and comment are required."]);
    exit;
}

try {
    $stmt = $conn->prepare("
        INSERT INTO real_world_repairs (student_id, repair_title, comment, trainer_name)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($ids as $student_id) {
        if (!empty($student_id)) {
            $stmt->execute([$student_id, $repair_title, $comment, $trainer_name]);
        }
    }

    echo json_encode(["status" => "success"]);
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
