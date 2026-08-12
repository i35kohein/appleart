<?php
error_reporting(E_ERROR | E_PARSE); // never let notices pollute JSON
require_once '../config.php';
require_once __DIR__ . '/auth_guard.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'POST only']);
    exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$ip = client_ip();

if ($email === '' || $password === '') {
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
    exit;
}

try {
    // Brute-force guard: 5 failed attempts (email or IP) → 15 min lock.
    $locked = auth_locked($conn, $email, $ip, 'login');
    if ($locked > 0) {
        http_response_code(429);
        echo json_encode(['status' => 'error', 'message' => lock_message($locked)]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, name, email, phone, password FROM students WHERE LOWER(email) = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student || $student['password'] === null || !password_verify($password, $student['password'])) {
        auth_fail($conn, $email, $ip, 'login');
        // Wrong email or password — same message for both.
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
        exit;
    }

    auth_success($conn, $email, $ip, 'login');
    session_regenerate_id(true); // prevent session fixation
    $_SESSION['student_id'] = intval($student['id']);
    // Remember me → keep the session cookie for 30 days (else browser-session).
    if (($_POST['remember'] ?? '') === '1') {
        setcookie(session_name(), session_id(), time() + 30 * 86400, '/', '', false, true);
    }
    unset($student['password']);
    echo json_encode(['status' => 'success', 'student' => $student]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
