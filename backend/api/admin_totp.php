<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/totp.php';

header('Content-Type: application/json');

if (!require_admin()) exit;

$uid = intval($_SESSION['user_id']);
$action = $_POST['action'] ?? 'status';

try {
    $stmt = $conn->prepare("SELECT id, email, totp_secret, password_hash FROM users WHERE id = :id");
    $stmt->execute(['id' => $uid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) { http_response_code(403); echo json_encode(['status' => 'error', 'message' => 'Account not found']); exit; }

    if ($action === 'status') {
        echo json_encode(['status' => 'success', 'enabled' => !empty($user['totp_secret'])]);
        exit;
    }

    if ($action === 'enable') {
        if (empty($user['totp_secret'])) {
            $secret = totp_secret_generate();
            $upd = $conn->prepare("UPDATE users SET totp_secret = :s WHERE id = :id");
            $upd->execute(['s' => $secret, 'id' => $uid]);
        } else {
            $secret = $user['totp_secret'];
        }
        echo json_encode([
            'status' => 'success',
            'enabled' => true,
            'secret' => $secret,
            'uri' => totp_uri($secret, $user['email']),
        ]);
        exit;
    }

    if ($action === 'verify_enable') {
        $code = trim($_POST['code'] ?? '');
        if ($user['totp_secret'] && totp_verify($user['totp_secret'], $code)) {
            echo json_encode(['status' => 'success', 'enabled' => true]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid code — check your authenticator app']);
        }
        exit;
    }

    if ($action === 'disable') {
        $pw = $_POST['password'] ?? '';
        if (!password_verify($pw, $user['password_hash'])) {
            echo json_encode(['status' => 'error', 'message' => 'Password is incorrect']);
            exit;
        }
        $upd = $conn->prepare("UPDATE users SET totp_secret = NULL WHERE id = :id");
        $upd->execute(['id' => $uid]);
        echo json_encode(['status' => 'success', 'enabled' => false]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
