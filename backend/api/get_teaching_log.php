<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

// Teaching log — everything that happened in training, by date:
//   kind='lesson'  -> whole-lesson completion (effect + note from lesson-level mark)
//   kind='steps'   -> detail steps taught that day (student, lesson, step names) — lesson not fully complete
//   kind='repair'  -> practical_history (source='practical') or real_world_repairs (source='real')
// Filters: ?from=YYYY-MM-DD&to=YYYY-MM-DD&student_id=&effect=effective|partial|not_effective

try {
    $lineCount = "(CHAR_LENGTH(ci.details) - CHAR_LENGTH(REPLACE(REPLACE(ci.details, '\\r', ''), '\\n', '')) + 1)";
    $stepsDone = "(SELECT COUNT(DISTINCT sp2.detail_idx) FROM student_progress sp2
                   WHERE sp2.student_id = p.student_id AND sp2.item_id = p.item_id
                     AND sp2.detail_idx IS NOT NULL AND sp2.status = 'Completed')";

    $sql = "SELECT t.* FROM (
        -- 1) whole-lesson completions (valid only when lesson has no steps OR all steps done)
        SELECT CONCAT('L', l.id) AS id,
               DATE(l.completion_date) AS log_date,
               p.student_id, p.item_id, 'lesson' AS kind,
               l.effect, l.instructor_note AS note,
               DATE_FORMAT(l.completion_date, '%Y-%m-%d %H:%i') AS created_at,
               s.name AS student_name, ci.title AS item_title, ci.type AS item_type,
               NULL AS step_names, NULL AS source
        FROM student_progress p
        JOIN students s ON s.id = p.student_id
        JOIN curriculum_items ci ON ci.id = p.item_id
        LEFT JOIN (
            SELECT id, student_id, item_id, completion_date, effect, instructor_note
            FROM student_progress WHERE detail_idx IS NULL AND status = 'Completed'
        ) l ON l.student_id = p.student_id AND l.item_id = p.item_id
        WHERE p.status = 'Completed'
          AND l.id IS NOT NULL
          AND (ci.details IS NULL OR TRIM(ci.details) = '' OR $stepsDone >= $lineCount)
        GROUP BY p.student_id, p.item_id

        UNION ALL

        -- 2) detail steps taught on a given day (lesson not fully completed yet)
        SELECT CONCAT('S', p.student_id, '-', p.item_id, '-', DATE(p.completion_date)) AS id,
               DATE(p.completion_date) AS log_date, p.student_id, p.item_id, 'steps' AS kind,
               NULL AS effect, NULL AS note, NULL AS created_at,
               s.name AS student_name, ci.title AS item_title, ci.type AS item_type,
               GROUP_CONCAT(DISTINCT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(ci.details, '\n', p.detail_idx), '\n', -1)) ORDER BY p.detail_idx SEPARATOR ', ') AS step_names,
               NULL AS source
        FROM student_progress p
        JOIN students s ON s.id = p.student_id
        JOIN curriculum_items ci ON ci.id = p.item_id
        WHERE p.status = 'Completed' AND p.detail_idx IS NOT NULL
          AND ci.details IS NOT NULL AND TRIM(ci.details) <> ''
          AND NOT EXISTS (
              SELECT 1 FROM student_progress lp
              JOIN curriculum_items ci3 ON ci3.id = lp.item_id
              WHERE lp.student_id = p.student_id AND lp.item_id = p.item_id
                AND lp.detail_idx IS NULL AND lp.status = 'Completed'
                AND (ci3.details IS NULL OR TRIM(ci3.details) = '' OR
                     (SELECT COUNT(DISTINCT sp4.detail_idx) FROM student_progress sp4
                      WHERE sp4.student_id = lp.student_id AND sp4.item_id = lp.item_id
                        AND sp4.detail_idx IS NOT NULL AND sp4.status = 'Completed')
                     >= (CHAR_LENGTH(ci3.details) - CHAR_LENGTH(REPLACE(REPLACE(ci3.details, '\\r', ''), '\\n', '')) + 1))
          )
        GROUP BY p.student_id, p.item_id, DATE(p.completion_date)

        UNION ALL

        -- 3) practical repairs (practice sessions)
        SELECT CONCAT('P', h.id) AS id, h.repair_date AS log_date,
               h.student_id, NULL AS item_id, 'repair' AS kind,
               NULL AS effect, h.note, NULL AS created_at,
               s.name AS student_name, h.title AS item_title, NULL AS item_type,
               NULL AS step_names, 'practical' AS source
        FROM practical_history h
        LEFT JOIN students s ON s.id = h.student_id

        UNION ALL

        -- 4) real-world repairs
        SELECT CONCAT('R', h.id) AS id, DATE(h.created_at) AS log_date,
               h.student_id, NULL AS item_id, 'repair' AS kind,
               NULL AS effect, h.comment AS note, NULL AS created_at,
               s.name AS student_name, h.repair_title AS item_title, NULL AS item_type,
               NULL AS step_names, 'real' AS source
        FROM real_world_repairs h
        JOIN students s ON s.id = h.student_id
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
