<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
    exit;
}

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'master_admin'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'user';

if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields required']);
    exit;
}

// Master Admin cannot be created via API directly
if ($role === 'master_admin') {
    $role = 'admin';
}

try {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hash, $role]);
    
    echo json_encode(['status' => 'success', 'message' => 'User created']);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
}
?>
