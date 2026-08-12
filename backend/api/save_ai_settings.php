<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;


header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'POST only']);
    exit;
}

$fields = ['ai_api_key' => '', 'ai_model' => 'gpt-4o-mini', 'ai_base_url' => 'https://api.openai.com/v1'];
foreach ($fields as $key => $default) {
    if (isset($_POST[$key])) {
        $value = trim($_POST[$key]);
        if ($value === '') $value = $default;
        $stmt = $conn->prepare("
            INSERT INTO app_settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute([$key, $value]);
    }
}
echo json_encode(['status' => 'success']);
?>
