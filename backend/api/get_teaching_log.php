<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

// Teaching log — every teaching session: date, who, what lesson, effect.
// Filters: ?from=YYYY-MM-DD&to=YYYY-MM-DD&student_id=&effect=effective|partial|not_effective

try {
    $conds = [];
    $params = [];
    if (!empty($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'])) {
        $conds[] = "t.log_date >= :from"; $params['from'] = $_GET['from'];
    }
    if (!empty($_GET['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'])) {
        $conds[] = "t.log_date <= :to"; $params['to'] = $_GET['to'];
    }
    if (!empty($_GET['student_id']) && intval($_GET['student_id']) > 0) {
        $conds[] = "t.student_id = :sid"; $params['sid'] = intval($_GET['student_id']);
    }
    if (!empty($_GET['effect']) && in_array($_GET['effect'], ['effective', 'partial', 'not_effective'], true)) {
        $conds[] = "t.effect = :eff"; $params['eff'] = $_GET['effect'];
    }
    $where = $conds ? " WHERE " . implode(" AND ", $conds) : "";

    $sql = "SELECT t.id, t.log_date, t.student_id, t.item_id, t.effect, t.note, t.created_at,
                   s.name AS student_name, ci.title AS item_title, ci.type AS item_type
            FROM teaching_log t
            LEFT JOIN students s ON s.id = t.student_id
            LEFT JOIN curriculum_items ci ON ci.id = t.item_id
            $where
            ORDER BY t.log_date DESC, t.id DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'data' => []]);
}
