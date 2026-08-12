<?php
error_reporting(E_ERROR | E_PARSE); // never let notices pollute JSON
require_once '../config.php';
require_once __DIR__ . '/auth_guard.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'POST only']);
    exit;
}
if (empty($_SESSION['student_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}
$sid = intval($_SESSION['student_id']);
$ip = client_ip();

$current = $_POST['current_password'] ?? '';
$newPass = $_POST['new_password'] ?? '';

if ($current === '' || $newPass === '') {
    echo json_encode(['status' => 'error', 'message' => 'Current and new password are required']);
    exit;
}
if (strlen($newPass) < 8 || !preg_match('/[A-Za-z]/', $newPass) || !preg_match('/\d/', $newPass)) {
    echo json_encode(['status' => 'error', 'message' => 'New password must be at least 8 characters with letters and numbers']);
    exit;
}

try {
    // Brute-force guard on password changes too (5 fails → 15 min).
    $email = '';
    $stmt = $conn->prepare("SELECT email, password FROM students WHERE id = :id");
    $stmt->execute(['id' => $sid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Account not found']);
        exit;
    }
    $email = strtolower($row['email'] ?? '');

    $locked = auth_locked($conn, $email, $ip, 'pwchange');
    if ($locked > 0) {
        http_response_code(429);
        echo json_encode(['status' => 'error', 'message' => lock_message($locked)]);
        exit;
    }

    if ($row['password'] === null || !password_verify($current, $row['password'])) {
        auth_fail($conn, $email, $ip, 'pwchange');
        echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
        exit;
    }

    $hash = password_hash($newPass, PASSWORD_DEFAULT);
    $upd = $conn->prepare("UPDATE students SET password = :h WHERE id = :id");
    $upd->execute(['h' => $hash, 'id' => $sid]);
    auth_success($conn, $email, $ip, 'pwchange');
    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
