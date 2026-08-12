<?php
/**
 * get_calendar.php — Big Training Calendar API
 * Fetches curriculum (Course + Practical), students, progress and generates
 * a daily basic->advance teaching plan for each student for a given month.
 */
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

$month = $_GET['month'] ?? date('Y-m');
$studentFilter = $_GET['student_id'] ?? '';

if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid month format (use YYYY-MM)']);
    exit;
}

$year  = (int)substr($month, 0, 4);
$monthNum = (int)substr($month, 5, 2);
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);

try {
    // --- Curriculum (basic -> advance by sort_order) ---
    $cur = $conn->query("SELECT id, type, category, title, sort_order FROM curriculum_items ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $courseItems = [];
    $practicalItems = [];
    foreach ($cur as $c) {
        if ($c['type'] === 'Practical') $practicalItems[] = $c;
        else $courseItems[] = $c;
    }

    // --- Students ---
    $stuSql = "SELECT id, name, rollcall_group, enrollment_date, is_active FROM students WHERE is_active = 1";
    if ($studentFilter !== '') {
        $stuSql .= " AND id = " . (int)$studentFilter;
    }
    $stuSql .= " ORDER BY id ASC";
    $students = $conn->query($stuSql)->fetchAll(PDO::FETCH_ASSOC);

    // --- Rollcall / training-day schedule ---
    $settings = [];
    $st = $conn->query("SELECT setting_key, setting_value FROM app_settings")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($st as $s) $settings[$s['setting_key']] = json_decode($s['setting_value'], true);
    $schedules = $settings['rollcall_schedules'] ?? ['Weekday' => ['days' => [2,3,4,5], 'start_time' => '10:00', 'end_time' => '15:00'], 'Weekend' => ['days' => [6,0], 'start_time' => '10:00', 'end_time' => '15:00']];
    $single = $settings['rollcall_schedule'] ?? null;
    if ($single && !$schedules) {
        $schedules = ['Weekday' => $single, 'Weekend' => $single];
    }

    // --- Completed progress per student ---
    $progress = [];
    $pr = $conn->query("SELECT student_id, item_id, completion_date FROM student_progress WHERE status = 'Completed'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($pr as $p) {
        $progress[$p['student_id']][$p['item_id']] = $p['completion_date'];
    }

    // --- Existing teacher_schedule rows for this month ---
    $tsSql = "SELECT id, schedule_date, start_time, end_time, teacher_name, student_group, lesson_type, topic, room_name, status
              FROM teacher_schedule WHERE schedule_date BETWEEN '$month-01' AND '$month-$daysInMonth' ORDER BY schedule_date ASC, start_time ASC";
    $teacherSchedule = $conn->query($tsSql)->fetchAll(PDO::FETCH_ASSOC);
    $tsByDate = [];
    foreach ($teacherSchedule as $ts) {
        $tsByDate[$ts['schedule_date']][] = $ts;
    }

    // --- Generate daily plan: basic -> advance, skipping completed ---
    $days = [];
    $stats = [];
    foreach ($students as $stu) {
        $g = $stu['rollcall_group'] === 'Weekend' ? 'Weekend' : 'Weekday';
        $groupDays = isset($schedules[$g]['days']) ? $schedules[$g]['days'] : [2,3,4,5];
        $doneSet = $progress[$stu['id']] ?? [];

        // Upcoming queue = uncompleted items, basic -> advance order
        $upcomingCourse = [];
        $upcomingPractical = [];
        foreach ($courseItems as $it) { if (!isset($doneSet[$it['id']])) $upcomingCourse[] = $it; }
        foreach ($practicalItems as $it) { if (!isset($doneSet[$it['id']])) $upcomingPractical[] = $it; }

        $doneCourse = count($courseItems) - count($upcomingCourse);
        $donePractical = count($practicalItems) - count($upcomingPractical);

        $dayIdx = 0; // training-day counter for this student (within month)
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = sprintf('%04d-%02d-%02d', $year, $monthNum, $d);
            $wd = (int)date('w', strtotime($date)); // 0=Sun
            $isTraining = in_array($wd, $groupDays);

            $course = null; $practical = null;
            if ($isTraining) {
                if (isset($upcomingCourse[$dayIdx])) {
                    $it = $upcomingCourse[$dayIdx];
                    $course = ['id' => $it['id'], 'title' => $it['title'], 'category' => $it['category'], 'done' => false];
                }
                if (isset($upcomingPractical[$dayIdx])) {
                    $it = $upcomingPractical[$dayIdx];
                    $practical = ['id' => $it['id'], 'title' => $it['title'], 'category' => $it['category'], 'done' => false];
                }
                $dayIdx++;
            }

            $days[$date][] = [
                'student_id'   => (int)$stu['id'],
                'student_name' => $stu['name'],
                'group'        => $g,
                'is_training'  => $isTraining,
                'course'       => $course,
                'practical'    => $practical,
                'scheduled'    => $tsByDate[$date] ?? [],
            ];
        }

        $stats[] = [
            'student_id'       => (int)$stu['id'],
            'student_name'     => $stu['name'],
            'group'            => $g,
            'course_done'      => $doneCourse,
            'course_total'     => count($courseItems),
            'practical_done'   => $donePractical,
            'practical_total'  => count($practicalItems),
        ];
    }

    echo json_encode([
        'status'         => 'success',
        'month'          => $month,
        'year'           => $year,
        'month_num'      => $monthNum,
        'days_in_month'  => $daysInMonth,
        'schedules'      => $schedules,
        'students'       => $students,
        'course_total'   => count($courseItems),
        'practical_total'=> count($practicalItems),
        'days'           => $days,
        'stats'          => $stats,
        'teacher_schedule' => $teacherSchedule,
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
