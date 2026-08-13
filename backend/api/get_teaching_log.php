<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

// Teaching log — everything that happened in training, by date:
//   kind='lesson'  -> whole-lesson completion (effect + note from lesson-level mark)
//   kind='steps'   -> detail steps taught that day (student, lesson, step names) — lesson not fully complete
//   kind='repair'  -> practical_history (source='practical') or real_world_repairs (source='real');
//                     identical repairs (same title + note + date) are grouped into ONE row
//                     with all trainee names combined.
// Filters: ?from=YYYY-MM-DD&to=YYYY-MM-DD&student_id=&effect=effective|partial|not_effective

try {
    $lineCount = "(CHAR_LENGTH(ci.details) - CHAR_LENGTH(REPLACE(REPLACE(ci.details, '\\r', ''), '\\n', '')) + 1)";
    $stepsDone = "(SELECT COUNT(DISTINCT sp2.detail_idx) FROM student_progress sp2
                   WHERE sp2.student_id = p.student_id AND sp2.item_id = p.item_id
                     AND sp2.detail_idx IS NOT NULL AND sp2.status = 'Completed')";

    // ---- per-part filter fragments (same $params array, bound once) ----
    $f = ['from' => '', 'to' => '', 'sid' => '', 'eff' => ''];
    if (!empty($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'])) {
        $f['from'] = " AND DATE(%s) >= :from";
    }
    if (!empty($_GET['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'])) {
        $f['to'] = " AND DATE(%s) <= :to";
    }
    if (!empty($_GET['student_id']) && intval($_GET['student_id']) > 0) {
        $f['sid'] = " AND %s = :sid";
    }
    if (!empty($_GET['effect']) && in_array($_GET['effect'], ['effective', 'partial', 'not_effective'], true)) {
        $f['eff'] = " AND %s = :eff";
    }

    $params = [];
    if ($f['from'] !== '') $params['from'] = $_GET['from'];
    if ($f['to'] !== '') $params['to'] = $_GET['to'];
    if ($f['sid'] !== '') $params['sid'] = intval($_GET['student_id']);
    if ($f['eff'] !== '') $params['eff'] = $_GET['effect'];

    // Parts that carry no effect simply return nothing when an effect filter is active.
    $noEff = $f['eff'] !== '' ? " AND 1=0" : "";

    $part1 = "
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
          " . sprintf($f['from'], 'l.completion_date') . "
          " . sprintf($f['to'], 'l.completion_date') . "
          " . sprintf($f['sid'], 'p.student_id') . "
          " . sprintf($f['eff'], 'l.effect') . "
        GROUP BY p.student_id, p.item_id";

    $part2 = "
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
          " . sprintf($f['from'], 'p.completion_date') . "
          " . sprintf($f['to'], 'p.completion_date') . "
          " . sprintf($f['sid'], 'p.student_id') . $noEff . "
        GROUP BY p.student_id, p.item_id, DATE(p.completion_date)";

    $part3 = "
        SELECT CONCAT('P', MIN(h.id)) AS id, h.repair_date AS log_date,
               MIN(h.student_id) AS student_id, NULL AS item_id, 'repair' AS kind,
               NULL AS effect, h.note, NULL AS created_at,
               GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') AS student_name,
               h.title AS item_title, NULL AS item_type,
               NULL AS step_names, 'practical' AS source
        FROM practical_history h
        LEFT JOIN students s ON s.id = h.student_id
        WHERE 1=1
          " . sprintf($f['from'], 'h.repair_date') . "
          " . sprintf($f['to'], 'h.repair_date') . "
          " . sprintf($f['sid'], 'h.student_id') . $noEff . "
        GROUP BY h.repair_date, h.title, h.note";

    $part4 = "
        SELECT CONCAT('R', MIN(h.id)) AS id, DATE(h.created_at) AS log_date,
               MIN(h.student_id) AS student_id, NULL AS item_id, 'repair' AS kind,
               NULL AS effect, h.comment AS note, NULL AS created_at,
               GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') AS student_name,
               h.repair_title AS item_title, NULL AS item_type,
               NULL AS step_names, 'real' AS source
        FROM real_world_repairs h
        JOIN students s ON s.id = h.student_id
        WHERE 1=1
          " . sprintf($f['from'], 'h.created_at') . "
          " . sprintf($f['to'], 'h.created_at') . "
          " . sprintf($f['sid'], 'h.student_id') . $noEff . "
        GROUP BY DATE(h.created_at), h.repair_title, h.comment";

    $sql = "SELECT t.* FROM ( $part1 UNION ALL $part2 UNION ALL $part3 UNION ALL $part4 ) t
            ORDER BY t.log_date DESC, t.student_name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'data' => []]);
}
