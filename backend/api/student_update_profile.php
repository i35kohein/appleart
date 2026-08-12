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

$name = trim(strip_tags($_POST['name'] ?? ''));
$email = strtolower(trim($_POST['email'] ?? ''));
$phone = trim(strip_tags($_POST['phone'] ?? ''));

if ($name === '' || $email === '') {
    echo json_encode(['status' => 'error', 'message' => 'Name and email are required']);
    exit;
}
if (mb_strlen($name) > 100 || mb_strlen($phone) > 30) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input length']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit;
}

try {
    $dup = $conn->prepare("SELECT id FROM students WHERE LOWER(email) = :e AND id <> :id LIMIT 1");
    $dup->execute(['e' => $email, 'id' => $sid]);
    if ($dup->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Email already used by another account']);
        exit;
    }
    $stmt = $conn->prepare("UPDATE students SET name = :n, email = :e, phone = :p WHERE id = :id");
    $stmt->execute(['n' => $name, 'e' => $email, 'p' => $phone, 'id' => $sid]);
    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
