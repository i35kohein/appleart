<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;


header('Content-Type: application/json');

// Optional filters: ?item_id=, ?student_id=
$item_id = $_GET['item_id'] ?? '';
$student_id = $_GET['student_id'] ?? '';

$cond = [];
$params = [];
if ($item_id !== '') { $cond[] = "h.item_id = :iid"; $params['iid'] = intval($item_id); }
if ($student_id !== '') { $cond[] = "h.student_id = :sid"; $params['sid'] = intval($student_id); }
$where = $cond ? " WHERE " . implode(" AND ", $cond) : "";

$cond2 = [];
$params2 = [];
if ($student_id !== '') { $cond2[] = "h.student_id = :sid2"; $params2['sid2'] = intval($student_id); }
$where2 = $cond2 ? " WHERE " . implode(" AND ", $cond2) : "";

// Merge NEW practical-history records + OLD real-world repairs into one feed.
// Records are free-form: item/student may be NULL (LEFT JOIN).
$sql = "
SELECT h.id, h.student_id, s.name AS student_name, h.item_id, COALESCE(ci.title, h.title) AS item_title,
       h.repair_date, h.note, h.created_at, NULL AS trainer_name, 'record' AS source
FROM practical_history h
LEFT JOIN students s ON s.id = h.student_id
LEFT JOIN curriculum_items ci ON ci.id = h.item_id
$where
UNION ALL
SELECT h.id, h.student_id, s.name AS student_name, NULL AS item_id, h.repair_title AS item_title,
       DATE(h.created_at) AS repair_date, h.comment AS note, h.created_at, h.trainer_name, 'repair' AS source
FROM real_world_repairs h
JOIN students s ON s.id = h.student_id
$where2
ORDER BY created_at DESC
";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params + $params2);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'data' => []]);
}
?>
