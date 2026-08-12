<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/auth_guard.php';

header('Content-Type: application/json');

if (!require_admin()) exit;

$uid = intval($_SESSION['user_id']);
$ip = client_ip();
$current = $_POST['current_password'] ?? '';
$newPass = $_POST['new_password'] ?? '';

if ($current === '' || $newPass === '') {
    echo json_encode(['status' => 'error', 'message' => 'Current and new password are required']);
    exit;
}
if (strlen($newPass) < 10 || !preg_match('/[A-Za-z]/', $newPass) || !preg_match('/\d/', $newPass)) {
    echo json_encode(['status' => 'error', 'message' => 'New password must be at least 10 characters with letters and numbers']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT email, password_hash FROM users WHERE id = :id");
    $stmt->execute(['id' => $uid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { http_response_code(403); echo json_encode(['status' => 'error', 'message' => 'Account not found']); exit; }
    $email = strtolower($row['email'] ?? '');

    $locked = auth_locked($conn, $email, $ip, 'pwchange');
    if ($locked > 0) {
        http_response_code(429);
        echo json_encode(['status' => 'error', 'message' => lock_message($locked)]);
        exit;
    }

    if (!password_verify($current, $row['password_hash'])) {
        auth_fail($conn, $email, $ip, 'pwchange');
        echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
        exit;
    }

    $hash = password_hash($newPass, PASSWORD_DEFAULT);
    $conn->prepare("UPDATE users SET password_hash = :h WHERE id = :id")->execute(['h' => $hash, 'id' => $uid]);
    auth_success($conn, $email, $ip, 'pwchange');
    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
