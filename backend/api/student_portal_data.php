<?php
require_once '../config.php';

header('Content-Type: application/json');

if (empty($_SESSION['student_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in', 'data' => null]);
    exit;
}
$sid = intval($_SESSION['student_id']);

try {
    $student = $conn->prepare("SELECT id, name, email, phone, photo_path, rollcall_group FROM students WHERE id = :id LIMIT 1");
    $student->execute(['id' => $sid]);
    $stu = $student->fetch(PDO::FETCH_ASSOC);
    if (!$stu) {
        echo json_encode(['status' => 'error', 'message' => 'Account not found', 'data' => null]);
        exit;
    }

    // Curriculum + materials (same as admin view).
    $cur = $conn->query("SELECT id, type, category, title, tags, practice, details, sort_order FROM curriculum_items ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $mats = $conn->query("SELECT id, item_id, file_name, file_path, file_type, file_size FROM curriculum_materials ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $byItem = [];
    foreach ($mats as $m) $byItem[$m['item_id']][] = $m;
    foreach ($cur as &$c) $c['materials'] = $byItem[$c['id']] ?? [];
    unset($c);

    // Own progress only.
    $prog = $conn->prepare("SELECT id, item_id, detail_idx, status, completion_date FROM student_progress WHERE student_id = :sid ORDER BY id ASC");
    $prog->execute(['sid' => $sid]);
    $progress = $prog->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => ['student' => $stu, 'curriculum' => $cur, 'progress' => $progress]]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'data' => null]);
}
?>
