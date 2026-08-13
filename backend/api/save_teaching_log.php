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
        $conn->prepare("DELETE FROM teaching_log WHERE id = :id")->execute(['id' => $id]);
        echo json_encode(['status' => 'success']);
        exit;
    }

    // ---- add ----
    $logDate = trim($_POST['log_date'] ?? '');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $logDate)) $logDate = date('Y-m-d');

    $studentId = intval($_POST['student_id'] ?? 0);
    $itemId = intval($_POST['item_id'] ?? 0);
    $effect = $_POST['effect'] ?? 'effective';
    if (!in_array($effect, ['effective', 'partial', 'not_effective'], true)) $effect = 'effective';
    $note = mb_substr(trim(strip_tags($_POST['note'] ?? '')), 0, 500);

    if ($studentId <= 0 && $itemId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Pick a trainee and a lesson']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO teaching_log (log_date, student_id, item_id, effect, note) VALUES (:d, :s, :i, :e, :n)");
    $stmt->execute([
        'd' => $logDate,
        's' => $studentId > 0 ? $studentId : null,
        'i' => $itemId > 0 ? $itemId : null,
        'e' => $effect,
        'n' => $note !== '' ? $note : null,
    ]);
    echo json_encode(['status' => 'success', 'id' => $conn->lastInsertId()]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
