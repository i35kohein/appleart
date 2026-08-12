<?php
require_once '../config.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/totp.php';
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

    $stmt = $conn->prepare("SELECT id, name, password_hash, role, totp_secret FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Enforce master_admin if email matches
        $role = ($email === '99puupuu@gmail.com') ? 'master_admin' : $user['role'];

        // ---- 2FA: if TOTP is enabled, require the authenticator code ----
        if (!empty($user['totp_secret'])) {
            $code = trim($_POST['totp_code'] ?? '');
            if ($code === '') {
                $_SESSION['_pending_admin'] = intval($user['id']);
                echo json_encode(['status' => '2fa_required', 'message' => 'Enter your authenticator code']);
                exit;
            }
            $locked = auth_locked($conn, $email, $ip, 'totp');
            if ($locked > 0) {
                http_response_code(429);
                echo json_encode(['status' => 'error', 'message' => lock_message($locked)]);
                exit;
            }
            if (!totp_verify($user['totp_secret'], $code)) {
                auth_fail($conn, $email, $ip, 'totp');
                echo json_encode(['status' => 'error', 'message' => 'Invalid 2FA code']);
                exit;
            }
            auth_success($conn, $email, $ip, 'totp');
            unset($_SESSION['_pending_admin']);
        }

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
