<?php
// Shared brute-force guard helpers for student auth endpoints.

function client_ip(): string {
    // Behind a reverse proxy (Caddy), use the real client IP from X-Forwarded-For.
    $fwd = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($fwd !== '') {
        $first = trim(explode(',', $fwd)[0]);
        if (filter_var($first, FILTER_VALIDATE_IP)) return $first;
    }
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

/** Returns remaining lock seconds (0 = not locked). */
function auth_locked(PDO $conn, string $email, string $ip, string $action, int $max = 5, int $windowSec = 900): int {
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM auth_attempts
        WHERE action = :a AND success = 0
          AND created_at > (NOW() - INTERVAL $windowSec SECOND)
          AND (email = :e OR ip = :i)
    ");
    $stmt->execute(['a' => $action, 'e' => $email, 'i' => $ip]);
    $fails = intval($stmt->fetchColumn());
    if ($fails < $max) return 0;

    // Time until the oldest failure in the window ages out.
    $old = $conn->prepare("
        SELECT MIN(created_at) FROM auth_attempts
        WHERE action = :a AND success = 0
          AND created_at > (NOW() - INTERVAL $windowSec SECOND)
          AND (email = :e OR ip = :i)
    ");
    $old->execute(['a' => $action, 'e' => $email, 'i' => $ip]);
    $oldest = $old->fetchColumn();
    if (!$oldest) return 0;
    $remain = $windowSec - (time() - strtotime($oldest));
    return max(0, $remain);
}

function auth_fail(PDO $conn, string $email, string $ip, string $action): void {
    $stmt = $conn->prepare("INSERT INTO auth_attempts (email, ip, action, success) VALUES (:e, :i, :a, 0)");
    $stmt->execute(['e' => $email, 'i' => $ip, 'a' => $action]);
    // Opportunistic cleanup: drop attempts older than 7 days.
    if (mt_rand(1, 50) === 1) {
        $conn->exec("DELETE FROM auth_attempts WHERE created_at < (NOW() - INTERVAL 7 DAY)");
    }
}

function auth_success(PDO $conn, string $email, string $ip, string $action): void {
    $stmt = $conn->prepare("INSERT INTO auth_attempts (email, ip, action, success) VALUES (:e, :i, :a, 1)");
    $stmt->execute(['e' => $email, 'i' => $ip, 'a' => $action]);
    // Old failures for this email no longer matter after a successful login.
    $clean = $conn->prepare("DELETE FROM auth_attempts WHERE action = :a AND email = :e AND success = 0");
    $clean->execute(['a' => $action, 'e' => $email]);
}

function lock_message(int $sec): string {
    $min = (int) ceil($sec / 60);
    return "Too many attempts — ခဏစောင့်ပြီး ပြန်စမ်းပါ (" . $min . " min lock)";
}
