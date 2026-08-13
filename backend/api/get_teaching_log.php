<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

// Teaching log — derived automatically from COURSE progress data (student_progress).
// Shows: on which date, which trainee completed which lesson, and the marked effect.
// Filters: ?from=YYYY-MM-DD&to=YYYY-MM-DD&student_id=&effect=effective|partial|not_effective

try {
    $conds = [];
    $params = [];
    if (!empty($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'])) {
        $conds[] = "DATE(p.completion_date) >= :from"; $params['from'] = $_GET['from'];
    }
    if (!empty($_GET['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'])) {
        $conds[] = "DATE(p.completion_date) <= :to"; $params['to'] = $_GET['to'];
    }
    if (!empty($_GET['student_id']) && intval($_GET['student_id']) > 0) {
        $conds[] = "p.student_id = :sid"; $params['sid'] = intval($_GET['student_id']);
    }
    if (!empty($_GET['effect']) && in_array($_GET['effect'], ['effective', 'partial', 'not_effective'], true)) {
        $conds[] = "p.effect = :eff"; $params['eff'] = $_GET['effect'];
    }
    // Lesson-level completions only (detail step rows carry detail_idx, not shown here).
    $conds[] = "p.detail_idx IS NULL";
    $conds[] = "p.status = 'Completed'";
    $where = " WHERE " . implode(" AND ", $conds);

    $sql = "SELECT p.id, DATE(p.completion_date) AS log_date, p.student_id, p.item_id, p.effect, p.instructor_note AS note,
                   DATE_FORMAT(p.completion_date, '%Y-%m-%d %H:%i') AS created_at,
                   s.name AS student_name, ci.title AS item_title, ci.type AS item_type
            FROM student_progress p
            LEFT JOIN students s ON s.id = p.student_id
            LEFT JOIN curriculum_items ci ON ci.id = p.item_id
            $where
            ORDER BY p.completion_date DESC, p.id DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'data' => []]);
}
