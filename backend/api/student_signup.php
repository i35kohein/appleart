<?php
error_reporting(E_ERROR | E_PARSE); // never let notices pollute JSON
require_once '../config.php';
require_once __DIR__ . '/auth_guard.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'POST only']);
    exit;
}

$name = trim(strip_tags($_POST['name'] ?? ''));
$email = strtolower(trim($_POST['email'] ?? ''));
$phone = trim(strip_tags($_POST['phone'] ?? ''));
$password = $_POST['password'] ?? '';
$ip = client_ip();

if ($name === '' || $email === '' || $password === '') {
    echo json_encode(['status' => 'error', 'message' => 'Name, email and password are required']);
    exit;
}
if (mb_strlen($name) > 100 || mb_strlen($phone) > 30) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input length']);
    exit;
}
if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters with letters and numbers']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit;
}

try {
    // Brute-force guard on signup: 5 failed signups per IP → 60 min lock.
    $locked = auth_locked($conn, '', $ip, 'signup', 5, 3600);
    if ($locked > 0) {
        http_response_code(429);
        echo json_encode(['status' => 'error', 'message' => lock_message($locked)]);
        exit;
    }

    $exists = $conn->prepare("SELECT id FROM students WHERE LOWER(email) = :e LIMIT 1");
    $exists->execute(['e' => $email]);
    if ($exists->fetch()) {
        auth_fail($conn, $email, $ip, 'signup');
        echo json_encode(['status' => 'error', 'message' => 'Email already registered — login instead']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    // Self-registered learners = online role (e-learning); hidden from the academy platform.
    $stmt = $conn->prepare("INSERT INTO students (name, phone, email, is_active, role, rollcall_group, enrollment_date, password) VALUES (:n, :p, :e, 1, 'online', 'Weekday', CURDATE(), :h)");
    $stmt->execute(['n' => $name, 'p' => $phone, 'e' => $email, 'h' => $hash]);
    $sid = intval($conn->lastInsertId());

    auth_success($conn, $email, $ip, 'signup');
    session_regenerate_id(true);
    $_SESSION['student_id'] = $sid;
    echo json_encode(['status' => 'success', 'student' => ['id' => $sid, 'name' => $name, 'email' => $email, 'phone' => $phone]]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
