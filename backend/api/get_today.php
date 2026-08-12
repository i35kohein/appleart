<?php
/**
 * get_today.php — Today's Lessons API (Big Screen)
 * Returns what each selected active student must learn TODAY
 * (Course + Practical), plus their progress — basic -> advance.
 * Params: student_ids (optional CSV; default = all active students)
 */
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

$today = date('Y-m-d');
$wd = (int)date('w'); // 0=Sun
$studentFilter = $_GET['student_ids'] ?? '';

try {
    // --- Curriculum (basic -> advance) ---
    $cur = $conn->query("SELECT id, type, category, title, sort_order FROM curriculum_items ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $courseItems = [];
    $practicalItems = [];
    foreach ($cur as $c) {
        if ($c['type'] === 'Practical') $practicalItems[] = $c;
        else $courseItems[] = $c;
    }

    // --- Active students (optional filter) ---
    $stuSql = "SELECT id, name, phone, rollcall_group, enrollment_date FROM students WHERE is_active = 1";
    if ($studentFilter !== '') {
        $ids = array_filter(array_map('intval', explode(',', $studentFilter)));
        if (count($ids)) $stuSql .= " AND id IN (" . implode(',', $ids) . ")";
    }
    $stuSql .= " ORDER BY id ASC";
    $students = $conn->query($stuSql)->fetchAll(PDO::FETCH_ASSOC);

    // --- Training-day schedule ---
    $settings = [];
    $st = $conn->query("SELECT setting_key, setting_value FROM app_settings")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($st as $s) $settings[$s['setting_key']] = json_decode($s['setting_value'], true);
    $schedules = $settings['rollcall_schedules'] ?? ['Weekday' => ['days' => [2,3,4,5], 'start_time' => '10:00', 'end_time' => '15:00'], 'Weekend' => ['days' => [6,0], 'start_time' => '10:00', 'end_time' => '15:00']];

    // --- Completed progress per student ---
    $progress = [];
    $pr = $conn->query("SELECT student_id, item_id, completion_date FROM student_progress WHERE status = 'Completed'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($pr as $p) $progress[$p['student_id']][$p['item_id']] = $p['completion_date'];

    // --- Scheduled lessons today ---
    $tsByDate = [];
    $ts = $conn->query("SELECT id, schedule_date, start_time, end_time, teacher_name, student_group, lesson_type, topic, room_name, status FROM teacher_schedule WHERE schedule_date = '$today' ORDER BY start_time ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($ts as $t) $tsByDate[$t['schedule_date']][] = $t;

    // --- Per-student today's plan ---
    $result = [];
    foreach ($students as $stu) {
        $g = $stu['rollcall_group'] === 'Weekend' ? 'Weekend' : 'Weekday';
        $groupDays = isset($schedules[$g]['days']) ? $schedules[$g]['days'] : [2,3,4,5];
        $isTraining = in_array($wd, $groupDays);
        $doneSet = $progress[$stu['id']] ?? [];

        $upcomingCourse = [];
        $upcomingPractical = [];
        foreach ($courseItems as $it) { if (!isset($doneSet[$it['id']])) $upcomingCourse[] = $it; }
        foreach ($practicalItems as $it) { if (!isset($doneSet[$it['id']])) $upcomingPractical[] = $it; }

        $course = null; $practical = null;
        if ($isTraining) {
            if (isset($upcomingCourse[0])) {
                $it = $upcomingCourse[0];
                $course = ['id' => $it['id'], 'title' => $it['title'], 'category' => $it['category']];
            }
            if (isset($upcomingPractical[0])) {
                $it = $upcomingPractical[0];
                $practical = ['id' => $it['id'], 'title' => $it['title'], 'category' => $it['category']];
            }
        }

        $result[] = [
            'student_id'   => (int)$stu['id'],
            'student_name' => $stu['name'],
            'phone'        => $stu['phone'],
            'group'        => $g,
            'is_training'  => $isTraining,
            'course'       => $course,
            'practical'    => $practical,
            'course_done'  => count($courseItems) - count($upcomingCourse),
            'course_total' => count($courseItems),
            'practical_done' => count($practicalItems) - count($upcomingPractical),
            'practical_total' => count($practicalItems),
            'scheduled'    => $tsByDate[$today] ?? [],
        ];
    }

    echo json_encode([
        'status'    => 'success',
        'date'      => $today,
        'weekday'   => date('l'),
        'schedules' => $schedules,
        'students'  => $result,
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
