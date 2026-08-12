<?php
error_reporting(E_ERROR | E_PARSE); // never leak notices/warnings

// ---- Security headers (all responses) ----
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob:; media-src 'self' blob:; frame-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'");

// ---- Hardened session cookie ----
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
date_default_timezone_set('Asia/Rangoon'); // Local timezone — "today" for rollcall/calendar/screen

// ============================================================
// DATABASE CREDENTIALS — copy this file to config.php and fill in.
// config.php is gitignored (never commit real credentials).
// ============================================================
$servername = "localhost";
$username = "USERNAME";      // <-- DB username
$password = "PASSWORD";      // <-- DB password
$dbname = "zvryylsz_appleart";

// Create connection using PDO for better security
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
