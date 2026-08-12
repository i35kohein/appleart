<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;


header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'POST only']);
    exit;
}

$message = trim($_POST['message'] ?? '');
if ($message === '') {
    echo json_encode(['status' => 'error', 'message' => 'Empty message']);
    exit;
}

// Load AI settings from app_settings.
$settings = [];
foreach ($conn->query("SELECT setting_key, setting_value FROM app_settings") as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$apiKey = $settings['ai_api_key'] ?? '';
$model = $settings['ai_model'] ?? 'gpt-4o-mini';
$baseUrl = rtrim($settings['ai_base_url'] ?? 'https://api.openai.com/v1', '/');

if ($apiKey === '') {
    echo json_encode(['status' => 'error', 'message' => 'AI API key is not set — add it in Admin → AI Assistant & API.']);
    exit;
}

$systemPrompt = "You are the helpful assistant for Apple Art i Device Repair Training academy in Yangon, Myanmar.
You answer questions about phone repair training, iPhone repair, the academy's courses, and general repair help.
Keep answers practical and concise.";

$payload = json_encode([
    'model' => $model,
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $message],
    ],
    'max_tokens' => 800,
]);

$ch = curl_init($baseUrl . '/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(['status' => 'error', 'message' => 'AI request failed: ' . $err]);
    exit;
}
$data = json_decode($resp, true);
if ($code >= 400 || !isset($data['choices'][0]['message']['content'])) {
    echo json_encode(['status' => 'error', 'message' => 'AI error (' . $code . '): ' . substr($resp, 0, 300)]);
    exit;
}
echo json_encode(['status' => 'success', 'reply' => $data['choices'][0]['message']['content']]);
?>
