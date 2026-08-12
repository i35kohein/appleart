<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;


header('Content-Type: application/json');

$defaults = [
    'ai_api_key' => '',
    'ai_model' => 'gpt-4o-mini',
    'ai_base_url' => 'https://api.openai.com/v1',
    'show_today_schedule' => false,
    'show_rollcall_lessons' => false,
    'rollcall_schedule' => [
        'days' => [1, 2, 3, 4, 5],
        'start_time' => '10:00',
        'end_time' => '15:00',
        'enabled' => true
    ],
    'rollcall_schedules' => [
        'Weekday' => [
            'days' => [1, 2, 3, 4, 5],
            'start_time' => '10:00',
            'end_time' => '15:00',
            'enabled' => true
        ],
        'Weekend' => [
            'days' => [6, 0],
            'start_time' => '10:00',
            'end_time' => '15:00',
            'enabled' => true
        ]
    ]
];

try {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM app_settings");
    $settings = $defaults;
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $decoded = json_decode($row['setting_value'], true);
        $settings[$row['setting_key']] = $decoded === null ? $row['setting_value'] : $decoded;
    }
    // Backward compat: schedules saved before "enabled" existed default to enabled.
    if (isset($settings['rollcall_schedules']) && is_array($settings['rollcall_schedules'])) {
        foreach (['Weekday', 'Weekend'] as $g) {
            if (isset($settings['rollcall_schedules'][$g]) && is_array($settings['rollcall_schedules'][$g]) && !array_key_exists('enabled', $settings['rollcall_schedules'][$g])) {
                $settings['rollcall_schedules'][$g]['enabled'] = true;
            }
        }
    }
    if (!isset($settings['rollcall_schedules']) && isset($settings['rollcall_schedule'])) {
        $settings['rollcall_schedules'] = $defaults['rollcall_schedules'];
        $settings['rollcall_schedules']['Weekday'] = $settings['rollcall_schedule'];
    }
    // Only admin/master_admin may read the AI API key.
$role = $_SESSION['user_role'] ?? '';
if ($role !== 'admin' && $role !== 'master_admin') {
    unset($settings['ai_api_key']);
}
echo json_encode(['status' => 'success', 'data' => $settings]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'data' => $defaults]);
}
?>
