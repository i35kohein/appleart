<?php
/**
 * save_teacher_schedule.php — Add / update / delete a scheduled lesson
 * on the training calendar. Lets staff go back to any date and add data.
 *
 * POST fields:
 *   action         = 'save' | 'delete'
 *   id             = schedule id (required for update/delete)
 *   schedule_date  = YYYY-MM-DD
 *   start_time     = HH:MM
 *   end_time       = HH:MM (optional)
 *   teacher_name   = string
 *   student_group  = Weekday | Weekend | '' (all)
 *   lesson_type    = Theory | Practical | Live Repair | Exam | Other
 *   topic          = text
 *   room_name      = string (optional)
 *   status         = Planned | Taught | Cancelled
 */
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

$action = $_POST['action'] ?? 'save';
$id = (int)($_POST['id'] ?? 0);

try {
    if ($action === 'delete') {
        if ($id <= 0) { echo json_encode(['status' => 'error', 'message' => 'Missing id']); exit; }
        $conn->prepare("DELETE FROM teacher_schedule WHERE id = ?")->execute([$id]);
        echo json_encode(['status' => 'success']);
        exit;
    }

    $date = trim($_POST['schedule_date'] ?? '');
    $start = trim($_POST['start_time'] ?? '');
    $end = trim($_POST['end_time'] ?? '');
    $teacher = trim($_POST['teacher_name'] ?? '');
    $group = trim($_POST['student_group'] ?? '');
    $lessonType = trim($_POST['lesson_type'] ?? 'Theory');
    $topic = trim($_POST['topic'] ?? '');
    $room = trim($_POST['room_name'] ?? '');
    $status = trim($_POST['status'] ?? 'Planned');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid date (use YYYY-MM-DD)']); exit;
    }
    if (!preg_match('/^\d{2}:\d{2}$/', $start)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid start time (use HH:MM)']); exit;
    }
    if ($end !== '' && !preg_match('/^\d{2}:\d{2}$/', $end)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid end time (use HH:MM)']); exit;
    }
    if ($teacher === '' || $topic === '') {
        echo json_encode(['status' => 'error', 'message' => 'Teacher name and topic are required']); exit;
    }

    $allowedTypes = ['Theory', 'Practical', 'Live Repair', 'Exam', 'Other'];
    if (!in_array($lessonType, $allowedTypes)) $lessonType = 'Other';
    $allowedStatus = ['Planned', 'Taught', 'Cancelled'];
    if (!in_array($status, $allowedStatus)) $status = 'Planned';
    $allowedGroups = ['', 'Weekday', 'Weekend'];
    if (!in_array($group, $allowedGroups)) $group = '';

    if ($id > 0) {
        $stmt = $conn->prepare(
            "UPDATE teacher_schedule SET schedule_date=?, start_time=?, end_time=?, teacher_name=?,
             student_group=?, lesson_type=?, topic=?, room_name=?, status=? WHERE id=?"
        );
        $stmt->execute([$date, $start, $end !== '' ? $end : null, $teacher, $group, $lessonType, $topic, $room !== '' ? $room : null, $status, $id]);
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO teacher_schedule (schedule_date, start_time, end_time, teacher_name, student_group, lesson_type, topic, room_name, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$date, $start, $end !== '' ? $end : null, $teacher, $group, $lessonType, $topic, $room !== '' ? $room : null, $status]);
        $id = (int)$conn->lastInsertId();
    }

    echo json_encode(['status' => 'success', 'id' => $id]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
