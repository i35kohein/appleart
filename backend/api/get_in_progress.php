<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;


header('Content-Type: application/json');

// Optional student_id filter; omit for all students.
$student_id = $_GET['student_id'] ?? '';

// "In Progress" is DERIVED for detail lessons (has step rows, no lesson-level Completed)
// OR stored directly as a lesson-level 'In Progress' row (lessons without details).
$sql = "SELECT sp.student_id, s.name AS student_name, sp.item_id, ci.title AS item_title, sp.status, sp.completion_date,
               COUNT(*) AS steps_done
        FROM student_progress sp
        JOIN students s ON s.id = sp.student_id
        JOIN curriculum_items ci ON ci.id = sp.item_id
        WHERE (
            (sp.status = 'In Progress' AND sp.detail_idx IS NULL)
            OR
            (sp.status = 'Completed' AND sp.detail_idx IS NOT NULL
                AND NOT EXISTS (
                    SELECT 1 FROM student_progress l
                    WHERE l.student_id = sp.student_id AND l.item_id = sp.item_id
                      AND l.detail_idx IS NULL AND l.status = 'Completed'
                ))
        )";
$params = [];
if ($student_id !== '') {
    $sql .= " AND sp.student_id = :sid";
    $params['sid'] = intval($student_id);
}
$sql .= " GROUP BY sp.student_id, sp.item_id ORDER BY sp.completion_date DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'data' => []]);
}
?>
