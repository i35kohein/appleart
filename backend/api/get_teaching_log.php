<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

// Teaching log — derived automatically from COURSE progress data (student_progress).
// A trainee counts as "completed" for a lesson when:
//   A) whole-lesson mark (detail_idx IS NULL, status=Completed) — for lessons WITHOUT
//      detail steps, always; for lessons WITH steps, only if ALL steps are done; or
//   B) all detail steps done (derived completion — Mark done not pressed yet).
// Shows: date, trainee, lesson, effect. Filters: from/to/student_id/effect.

try {
    $lineCount = "(CHAR_LENGTH(ci.details) - CHAR_LENGTH(REPLACE(REPLACE(ci.details, '\\r', ''), '\\n', '')) + 1)";
    $stepsDone = "(SELECT COUNT(DISTINCT sp2.detail_idx) FROM student_progress sp2
                   WHERE sp2.student_id = p.student_id AND sp2.item_id = p.item_id
                     AND sp2.detail_idx IS NOT NULL AND sp2.status = 'Completed')";

    $sql = "SELECT t.* FROM (
        SELECT
            COALESCE(l.id, 0) AS id,
            COALESCE(DATE(l.completion_date), DATE(MAX(p.completion_date))) AS log_date,
            p.student_id, p.item_id,
            l.effect, l.instructor_note AS note,
            DATE_FORMAT(COALESCE(l.completion_date, MAX(p.completion_date)), '%Y-%m-%d %H:%i') AS created_at,
            s.name AS student_name, ci.title AS item_title, ci.type AS item_type
        FROM student_progress p
        JOIN students s ON s.id = p.student_id
        JOIN curriculum_items ci ON ci.id = p.item_id
        LEFT JOIN (
            SELECT id, student_id, item_id, completion_date, effect, instructor_note
            FROM student_progress
            WHERE detail_idx IS NULL AND status = 'Completed'
        ) l ON l.student_id = p.student_id AND l.item_id = p.item_id
        WHERE p.status = 'Completed'
          AND (
            -- A: whole-lesson mark, valid when no steps or ALL steps done
            (p.detail_idx IS NULL AND (ci.details IS NULL OR TRIM(ci.details) = '' OR $stepsDone >= $lineCount))
            OR
            -- B: all steps done but no whole-lesson mark yet (derived completion)
            (p.detail_idx IS NOT NULL AND l.id IS NULL AND ci.details IS NOT NULL AND TRIM(ci.details) <> '' AND $stepsDone >= $lineCount)
          )
        GROUP BY p.student_id, p.item_id
    ) t
    WHERE 1=1";

    $params = [];
    if (!empty($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'])) {
        $sql .= " AND t.log_date >= :from"; $params['from'] = $_GET['from'];
    }
    if (!empty($_GET['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'])) {
        $sql .= " AND t.log_date <= :to"; $params['to'] = $_GET['to'];
    }
    if (!empty($_GET['student_id']) && intval($_GET['student_id']) > 0) {
        $sql .= " AND t.student_id = :sid"; $params['sid'] = intval($_GET['student_id']);
    }
    if (!empty($_GET['effect']) && in_array($_GET['effect'], ['effective', 'partial', 'not_effective'], true)) {
        $sql .= " AND t.effect = :eff"; $params['eff'] = $_GET['effect'];
    }

    $sql .= " ORDER BY t.log_date DESC, t.student_name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'data' => []]);
}
