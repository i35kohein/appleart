<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;


header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'POST only']);
    exit;
}

$action = $_POST['action'] ?? 'add';

try {
    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['status' => 'error', 'message' => 'Missing id']); exit; }
        $stmt = $conn->prepare("DELETE FROM practical_history WHERE id = :id");
        $stmt->execute(['id' => $id]);
        echo json_encode(['status' => 'success']);
        exit;
    }

    // add — free-form: lesson and student are OPTIONAL; Header (title) + Paragraph (note).
    $student_id = intval($_POST['student_id'] ?? 0);
    $item_id = intval($_POST['item_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $repair_date = trim($_POST['repair_date'] ?? '');
    if ($title === '' && $note === '') { echo json_encode(['status' => 'error', 'message' => 'Write something first']); exit; }
    if ($repair_date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $repair_date)) { echo json_encode(['status' => 'error', 'message' => 'Invalid date (YYYY-MM-DD)']); exit; }
    if ($repair_date === '') $repair_date = date('Y-m-d');
    $title = mb_substr($title, 0, 255);
    $note = mb_substr($note, 0, 255);

    $stmt = $conn->prepare("INSERT INTO practical_history (student_id, item_id, repair_date, title, note) VALUES (:sid, :iid, :date, :title, :note)");
    $stmt->execute([
        'sid' => $student_id > 0 ? $student_id : null,
        'iid' => $item_id > 0 ? $item_id : null,
        'date' => $repair_date,
        'title' => $title,
        'note' => $note,
    ]);
    echo json_encode(['status' => 'success', 'id' => $conn->lastInsertId()]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
