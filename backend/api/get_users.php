<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'master_admin'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

try {
    $stmt = $conn->query("SELECT id, name, email, role FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "data" => $users]);
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
