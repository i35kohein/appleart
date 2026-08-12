<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;


header('Content-Type: application/json');

$student_id = intval($_GET['student_id'] ?? 0);
if ($student_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Missing student_id', 'data' => null]);
    exit;
}

try {
    // 1) Lesson totals + per-category/per-type progress.
    $lessons = $conn->query("SELECT id, category, type FROM curriculum_items")->fetchAll(PDO::FETCH_ASSOC);
    $total = count($lessons);
    $byCat = [];
    $byType = ['Course' => ['total' => 0, 'done' => 0], 'Practical' => ['total' => 0, 'done' => 0]];
    foreach ($lessons as $l) {
        $cat = $l['category'];
        if (!isset($byCat[$cat])) $byCat[$cat] = ['total' => 0, 'done' => 0];
        $byCat[$cat]['total']++;
        $t = $l['type'] === 'Practical' ? 'Practical' : 'Course';
        $byType[$t]['total']++;
    }

    $doneRows = $conn->prepare("
        SELECT DISTINCT item_id FROM student_progress
        WHERE student_id = :sid AND status = 'Completed' AND detail_idx IS NULL
    ");
    $doneRows->execute(['sid' => $student_id]);
    $done = [];
    foreach ($doneRows->fetchAll(PDO::FETCH_COLUMN) as $id) $done[$id] = true;

    $doneCount = 0;
    foreach ($lessons as $l) {
        if (isset($done[$l['id']])) {
            $doneCount++;
            $byCat[$l['category']]['done']++;
            $t = $l['type'] === 'Practical' ? 'Practical' : 'Course';
            $byType[$t]['done']++;
        }
    }

    // In-progress count (any step rows or lesson-level In Progress, no lesson Completed).
    $progRows = $conn->prepare("
        SELECT DISTINCT item_id FROM student_progress
        WHERE student_id = :sid AND item_id IS NOT NULL
          AND NOT (status = 'Completed' AND detail_idx IS NULL)
    ");
    $progRows->execute(['sid' => $student_id]);
    $inProgress = $progRows->rowCount();

    // 2) Attendance.
    $att = $conn->prepare("SELECT status, COUNT(*) c FROM rollcall_logs WHERE student_id = :sid GROUP BY status");
    $att->execute(['sid' => $student_id]);
    $attendance = ['total' => 0, 'byStatus' => []];
    foreach ($att->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $attendance['byStatus'][$r['status']] = intval($r['c']);
        $attendance['total'] += intval($r['c']);
    }
    $attMonthly = $conn->prepare("
        SELECT DATE_FORMAT(created_at, '%Y-%m') ym, status, COUNT(*) c
        FROM rollcall_logs WHERE student_id = :sid
        GROUP BY ym, status ORDER BY ym DESC LIMIT 12
    ");
    $attMonthly->execute(['sid' => $student_id]);
    $attendance['monthly'] = $attMonthly->fetchAll(PDO::FETCH_ASSOC);

    // 3) Exam (latest row — table is one row per student).
    $exam = $conn->prepare("SELECT exam_name, score, max_score, exam_date FROM student_exams WHERE student_id = :sid ORDER BY exam_date DESC LIMIT 1");
    $exam->execute(['sid' => $student_id]);
    $examRow = $exam->fetch(PDO::FETCH_ASSOC) ?: null;

    // 4) Learning speed: lesson completions per ISO week — always last 8 weeks (zeros filled).
    $weekly = $conn->prepare("
        SELECT YEARWEEK(completion_date, 3) yw, COUNT(*) c
        FROM student_progress
        WHERE student_id = :sid AND status = 'Completed' AND detail_idx IS NULL
        GROUP BY yw
    ");
    $weekly->execute(['sid' => $student_id]);
    $weeklyMap = [];
    foreach ($weekly->fetchAll(PDO::FETCH_ASSOC) as $r) $weeklyMap[$r['yw']] = intval($r['c']);
    $weeklyRows = [];
    for ($i = 7; $i >= 0; $i--) {
        $startTs = strtotime("-$i week monday this week");
        $yw = intval(date('o', $startTs) . str_pad(date('W', $startTs), 2, '0', STR_PAD_LEFT));
        $weeklyRows[] = [
            'yw' => $yw,
            'c' => $weeklyMap[$yw] ?? 0,
            'start' => date('Y-m-d', $startTs),
            'end' => date('Y-m-d', strtotime('+6 days', $startTs)),
        ];
    }

    // 5) Repairs: count + grouped by title.
    $repairs = $conn->prepare("
        SELECT 'practical' src, title, COUNT(*) c FROM practical_history WHERE student_id = :sid GROUP BY title
        UNION ALL
        SELECT 'real', repair_title, COUNT(*) c FROM real_world_repairs WHERE student_id = :sid GROUP BY repair_title
    ");
    $repairs->execute(['sid' => $student_id]);
    $repairRows = $repairs->fetchAll(PDO::FETCH_ASSOC);
    $repairCount = 0;
    $repairByTitle = [];
    foreach ($repairRows as $r) {
        $n = intval($r['c']);
        $repairCount += $n;
        if ($r['title'] === '') continue;
        $repairByTitle[$r['title']] = ($repairByTitle[$r['title']] ?? 0) + $n;
    }
    arsort($repairByTitle);

    // 6) Timeline: recently completed lessons.
    $timeline = $conn->prepare("
        SELECT ci.title, ci.category, ci.type, p.completion_date
        FROM student_progress p
        JOIN curriculum_items ci ON ci.id = p.item_id
        WHERE p.student_id = :sid AND p.status = 'Completed' AND p.detail_idx IS NULL
        ORDER BY p.completion_date DESC LIMIT 15
    ");
    $timeline->execute(['sid' => $student_id]);
    $timelineRows = $timeline->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total' => $total,
            'done' => $doneCount,
            'inProgress' => $inProgress,
            'byCategory' => $byCat,
            'byType' => $byType,
            'attendance' => $attendance,
            'exam' => $examRow,
            'weekly' => $weeklyRows,
            'repairCount' => $repairCount,
            'repairByTitle' => $repairByTitle,
            'timeline' => $timelineRows,
        ],
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'data' => null]);
}
?>
