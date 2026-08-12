<?php
require_once '../config.php';
require_once __DIR__ . '/auth_guard.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
    exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$ip = client_ip();

if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Email and password required']);
    exit;
}

try {
    // Brute-force guard: 5 failed admin logins (email or IP) → 15 min lock.
    $locked = auth_locked($conn, $email, $ip, 'adminlogin');
    if ($locked > 0) {
        http_response_code(429);
        echo json_encode(['status' => 'error', 'message' => lock_message($locked)]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, name, password_hash, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Enforce master_admin if email matches
        $role = ($email === '99puupuu@gmail.com') ? 'master_admin' : $user['role'];

        auth_success($conn, $email, $ip, 'adminlogin');
        session_regenerate_id(true); // prevent session fixation
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $role;
        $_SESSION['user_email'] = $email;

        echo json_encode(['status' => 'success', 'message' => 'Logged in']);
    } else {
        auth_fail($conn, $email, $ip, 'adminlogin');
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>
