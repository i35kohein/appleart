<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;


header('Content-Type: application/json');

$defaults = [
    'Weekday' => ['days' => [1, 2, 3, 4, 5], 'start_time' => '10:00', 'end_time' => '15:00', 'enabled' => true],
    'Weekend' => ['days' => [6, 0], 'start_time' => '10:00', 'end_time' => '15:00', 'enabled' => true]
];

$postedSchedules = json_decode($_POST['schedules'] ?? '', true);
$showTodaySchedule = $_POST['show_today_schedule'] ?? null;
$showRollcallLessons = $_POST['show_rollcall_lessons'] ?? null;
if (!is_array($postedSchedules)) {
    $postedSchedules = [
        'Weekday' => [
            'days' => json_decode($_POST['days'] ?? '[]', true),
            'start_time' => $_POST['start_time'] ?? '10:00',
            'end_time' => $_POST['end_time'] ?? '15:00'
        ],
        'Weekend' => $defaults['Weekend']
    ];
}

$schedules = [];
foreach (['Weekday', 'Weekend'] as $group) {
    $schedule = is_array($postedSchedules[$group] ?? null) ? $postedSchedules[$group] : $defaults[$group];
    $days = $schedule['days'] ?? [];
    if (!is_array($days)) $days = [];
    $days = array_values(array_unique(array_map('intval', $days)));
    $days = array_values(array_filter($days, fn($day) => $day >= 0 && $day <= 6));
    $start = $schedule['start_time'] ?? $defaults[$group]['start_time'];
    $end = $schedule['end_time'] ?? $defaults[$group]['end_time'];

    if (!preg_match('/^\d{2}:\d{2}$/', $start) || !preg_match('/^\d{2}:\d{2}$/', $end)) {
        echo json_encode(['status' => 'error', 'message' => "$group invalid time format"]);
        exit;
    }
    if ($start >= $end) {
        echo json_encode(['status' => 'error', 'message' => "$group start time must be before end time"]);
        exit;
    }
    $schedules[$group] = [
        'days' => $days,
        'start_time' => $start,
        'end_time' => $end,
        'enabled' => !isset($schedule['enabled']) ? true : (bool)$schedule['enabled']
    ];
}

$value = json_encode($schedules);

try {
    // Show/hide the "what to teach today" schedule on the Today Screen.
    if ($showTodaySchedule !== null) {
        $rc_stmt = $conn->prepare("
            INSERT INTO app_settings (setting_key, setting_value)
            VALUES ('show_today_schedule', ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $rc_stmt->execute([$showTodaySchedule ? '1' : '0']);
    }
    // Show/hide "Course: / Practical:" lines on Roll Call student cards.
    if ($showRollcallLessons !== null) {
        $rc_stmt = $conn->prepare("
            INSERT INTO app_settings (setting_key, setting_value)
            VALUES ('show_rollcall_lessons', ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $rc_stmt->execute([$showRollcallLessons ? '1' : '0']);
    }
    $stmt = $conn->prepare("
        INSERT INTO app_settings (setting_key, setting_value)
        VALUES ('rollcall_schedules', ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    $stmt->execute([$value]);
    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
